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

final class FixerIOProvider implements ExchangeRateProviderInterface
{
    /**
     * @var string
     */
    private $accessKey;

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
     * @param string                            $accessKey
     * @param HttpClient|null                   $client
     * @param RequestFactory|null               $requestFactory
     * @param ExchangeRateFactoryInterface|null $exchangeRateFactory
     * @throws \Http\Discovery\Exception\NotFoundException
     */
    public function __construct(
        string $accessKey,
        HttpClient $client = null,
        RequestFactory $requestFactory = null,
        ExchangeRateFactoryInterface $exchangeRateFactory = null
    ) {
        $this->accessKey = $accessKey;
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
            $date = new DateTimeImmutable('now', new DateTimeZone('GMT'));
        }

        if ($date instanceof DateTime) {
            $date = DateTimeImmutable::createFromMutable($date)->setTimezone(new DateTimeZone('GMT'));
        }

        if (!in_array('EUR', [$sourceCurrency->getCode(), $targetCurrency->getCode()])) {
            throw new ExchangeRateNotFoundException($sourceCurrency, $targetCurrency, "FixerIO Free plan only provide USD-based currency conversions.");
        }

        // Same currencies
        if ($sourceCurrency->getCode() === $targetCurrency->getCode()) {
            return $this->exchangeRateFactory->create($sourceCurrency, $targetCurrency, 1);
        }

        // Invert currencies
        if ('EUR' === $targetCurrency->getCode()) { // FixerIO free plan only provide EUR -> *
            $revertExchangeRate = $this->getExchangeRate($targetCurrency, $sourceCurrency, $date);
            return $this->exchangeRateFactory->create($sourceCurrency, $targetCurrency, 1 / $revertExchangeRate->getRatio());
        }

        $url = sprintf('http://data.fixer.io/api/%s?access_key=%s', $date->format('Y-m-d'), $this->accessKey);
        $response = $this->client->sendRequest($this->requestFactory->createRequest('GET', $url));
        $json = json_decode((string) $response->getBody(), true);
        if (isset($json['rates'][$targetCurrency->getCode()])) {
            return $this->exchangeRateFactory->create($sourceCurrency, $targetCurrency, $json['rates'][$targetCurrency->getCode()]);
        }

        throw new ExchangeRateNotFoundException($sourceCurrency, $targetCurrency);
    }
}
