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
use SimpleXMLElement;

final class EuropeanCentralBankProvider implements ExchangeRateProviderInterface
{

    const LIVE_FEED_URL = 'https://www.ecb.europa.eu/stats/eurofxref/eurofxref-daily.xml';
    const NINETYDAYS_FEED_URL = 'https://www.ecb.europa.eu/stats/eurofxref/eurofxref-hist-90d.xml';
    const FULL_FEED_URL = 'https://www.ecb.europa.eu/stats/eurofxref/eurofxref-hist.xml';

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
     * EuropeanCentralBankProvider constructor.
     * @param HttpClient|null                   $client
     * @param RequestFactory|null               $requestFactory
     * @param ExchangeRateFactoryInterface|null $exchangeRateFactory
     * @param CacheInterface|null               $cache
     * @throws \Http\Discovery\Exception\NotFoundException
     */
    public function __construct(
        HttpClient $client = null,
        RequestFactory $requestFactory = null,
        ExchangeRateFactoryInterface $exchangeRateFactory = null,
        CacheInterface $cache = null
    ) {
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
            $date = new DateTimeImmutable('now', new DateTimeZone('Europe/Paris'));
        }

        if ($date instanceof DateTime) {
            $date = DateTimeImmutable::createFromMutable($date)->setTimezone(new DateTimeZone('Europe/Paris'));
        }

        $dateString = $date->format('Y-m-d');

        if (!in_array('EUR', [$sourceCurrency->getCode(), $targetCurrency->getCode()])) {
            throw new ExchangeRateNotFoundException($sourceCurrency, $targetCurrency, "ECB only provide EUR-based currency conversions.");
        }

        // Same currencies
        if ($sourceCurrency->getCode() === $targetCurrency->getCode()) {
            return $this->exchangeRateFactory->create($sourceCurrency, $targetCurrency, 1);
        }

        // Invert currencies
        if ('EUR' === $targetCurrency->getCode()) { // ECB only provide EUR -> *
            $revertExchangeRate = $this->getExchangeRate($targetCurrency, $sourceCurrency, $date);
            return $this->exchangeRateFactory->create($targetCurrency, $sourceCurrency, 1 / $revertExchangeRate->getRatio());
        }

        $url = $this->pickUrl($date);
        $response = $this->client->sendRequest($this->requestFactory->createRequest('GET', $url));
        $xml = new SimpleXMLElement($response->getBody());

        $rates = [];
        foreach ($xml->Cube->Cube as $cube) {
            foreach ($cube->Cube as $rate) {
                $currency = (string) $rate['currency'];
                $ratio = (float) (string) $rate['rate'];
                $rates[(string) $cube['time']][$currency] = $ratio;
            }
        }

        if (isset($rates[$dateString][$targetCurrency->getCode()])) {
            return $this->exchangeRateFactory->create($sourceCurrency, $targetCurrency, $rates[$dateString][$targetCurrency->getCode()]);
        }

        throw new ExchangeRateNotFoundException($sourceCurrency, $targetCurrency);
    }


    /**
     * @param DateTimeInterface|null $date
     * @return string
     */
    private function pickUrl(DateTimeInterface $date): string
    {
        if ($date instanceof DateTime) {
            $date = DateTimeImmutable::createFromMutable($date);
        }

        $today = new DateTimeImmutable('today midnight', $date->getTimezone());


        switch (true) {
            case $date->format('Y-m-d') === $today->format('Y-m-d'):
                return static::LIVE_FEED_URL;

            case $date >= new DateTimeImmutable('-90 days', $date->getTimezone()):
                return static::NINETYDAYS_FEED_URL;

            default:
                return static::FULL_FEED_URL;
        }
    }
}
