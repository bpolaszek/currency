<?php

namespace BenTools\Currency\Provider;

use BenTools\Currency\Model\CurrencyInterface;
use BenTools\Currency\Model\ExchangeRateFactoryInterface;
use BenTools\Currency\Model\ExchangeRateInterface;
use BenTools\Currency\Model\ExchangeRateNotFoundException;
use BenTools\Currency\Model\NativeExchangeRateFactory;
use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;
use Http\Client\HttpClient;
use Http\Discovery\HttpClientDiscovery;
use Http\Discovery\MessageFactoryDiscovery;
use Http\Message\RequestFactory;
use Psr\SimpleCache\CacheInterface;

final class OpenExchangeRatesProvider implements ExchangeRateProviderInterface
{
    /**
     * @var string
     */
    private $appId;

    /**
     * @var HttpClient|null
     */
    private $client;

    /**
     * @var RequestFactory|null
     */
    private $requestFactory;

    /**
     * @var ExchangeRateFactoryInterface|null
     */
    private $exchangeRateFactory;

    /**
     * OpenExchangeRatesProvider constructor.
     * @param string                            $appId
     * @param HttpClient|null                   $client
     * @param RequestFactory|null               $requestFactory
     * @param ExchangeRateFactoryInterface|null $exchangeRateFactory
     * @param CacheInterface|null               $cache
     * @throws \Http\Discovery\Exception\NotFoundException
     */
    public function __construct(
        string $appId,
        HttpClient $client = null,
        RequestFactory $requestFactory = null,
        ExchangeRateFactoryInterface $exchangeRateFactory = null,
        CacheInterface $cache = null
    ) {
        $this->appId = $appId;
        $this->client = $client ?? HttpClientDiscovery::find();
        $this->requestFactory = $requestFactory ?? MessageFactoryDiscovery::find();
        $this->exchangeRateFactory = $exchangeRateFactory ?? new NativeExchangeRateFactory();
    }

    /**
     * @inheritDoc
     */
    public function getExchangeRate(CurrencyInterface $sourceCurrency, CurrencyInterface $targetCurrency, DateTimeInterface $date = null): ExchangeRateInterface
    {
        if (null === $date) {
            $date = new DateTimeImmutable('now', new DateTimeZone('UTC'));
        }

        if ($date instanceof DateTime) {
            $date = DateTimeImmutable::createFromMutable($date)->setTimezone(new DateTimeZone('UTC'));
        }

        if (!in_array('USD', [$sourceCurrency->getCode(), $targetCurrency->getCode()])) {
            throw new ExchangeRateNotFoundException($sourceCurrency, $targetCurrency, "OpenExchangeRates Free plan only provide USD-based currency conversions.");
        }

        // Same currencies
        if ($sourceCurrency->getCode() === $targetCurrency->getCode()) {
            return $this->exchangeRateFactory->create($sourceCurrency, $targetCurrency, 1);
        }

        // Invert currencies
        if ('USD' === $targetCurrency->getCode()) { // OpenExchangeRates only provide USD -> *
            $revertExchangeRate = $this->getExchangeRate($targetCurrency, $sourceCurrency, $date);
            return $this->exchangeRateFactory->create($targetCurrency, $sourceCurrency, 1 / $revertExchangeRate->getRatio());
        }

        $url = sprintf('https://openexchangerates.org/api/historical/%s.json?app_id=%s', $date->format('Y-m-d'), $this->appId);
        $response = $this->client->sendRequest($this->requestFactory->createRequest('GET', $url));
        $json = json_decode((string) $response->getBody(), true);
        if (isset($json['rates'][$targetCurrency->getCode()])) {
            return $this->exchangeRateFactory->create($sourceCurrency, $targetCurrency, $json['rates'][$targetCurrency->getCode()]);
        }

        throw new ExchangeRateNotFoundException($sourceCurrency, $targetCurrency);
    }
}
