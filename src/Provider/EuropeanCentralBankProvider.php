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
     * @var int
     */
    private $ttl;

    /**
     * @var ExchangeRateFactoryInterface|null
     */
    private $exchangeRateFactory;

    /**
     * @var array
     */
    private $rates = [];

    /**
     * @var
     */
    private $lastFetchTime;

    /**
     * EuropeanCentralBankLiveRateProvider constructor.
     * @param int                               $ttl
     * @param HttpClient|null                   $client
     * @param RequestFactory|null               $requestFactory
     * @param ExchangeRateFactoryInterface|null $exchangeRateFactory
     * @throws \Http\Discovery\Exception\NotFoundException
     */
    public function __construct(
        int $ttl = 0,
        HttpClient $client = null,
        RequestFactory $requestFactory = null,
        ExchangeRateFactoryInterface $exchangeRateFactory = null
    ) {
        $this->ttl = $ttl;
        $this->client = $client ?? HttpClientDiscovery::find();
        $this->requestFactory = $requestFactory ?? MessageFactoryDiscovery::find();
        $this->exchangeRateFactory = $exchangeRateFactory ?? new NativeExchangeRateFactory();
    }

    /**
     * @inheritDoc
     */
    public function getExchangeRate(CurrencyInterface $sourceCurrency, CurrencyInterface $targetCurrency, DateTimeInterface $date = null): ExchangeRateInterface
    {
        $date = $date ?? new DateTimeImmutable();

        if ($this->shouldFetchRates($date)) {
            $this->fetchRates($date);
        }

        $dateString = $date->format('Y-m-d');

        if ('EUR' === $sourceCurrency->getCode()) {
            if (!isset($this->rates[$dateString][$targetCurrency->getCode()])) {
                throw new ExchangeRateNotFoundException($sourceCurrency, $targetCurrency);
            }
            return $this->exchangeRateFactory->create($sourceCurrency, $targetCurrency, $this->rates[$dateString][$targetCurrency->getCode()]);
        }

        if ('EUR' === $targetCurrency->getCode()) {
            if (!isset($this->rates[$dateString][$sourceCurrency->getCode()])) {
                throw new ExchangeRateNotFoundException($sourceCurrency, $targetCurrency);
            }
            return $this->exchangeRateFactory->create($sourceCurrency, $targetCurrency, $this->rates[$dateString][$sourceCurrency->getCode()])->invertCurrencies();
        }

        throw new ExchangeRateNotFoundException($sourceCurrency, $targetCurrency);
    }

    /**
     * @param DateTimeInterface $date
     * @throws \Exception
     * @throws \Http\Client\Exception
     */
    private function fetchRates(DateTimeInterface $date): void
    {
        $url = $this->pickUrl($date);
        $response = $this->client->sendRequest($this->requestFactory->createRequest('GET', $url));
        $xml = new SimpleXMLElement($response->getBody());

        if (time() > ($this->lastFetchTime + $this->ttl)) {
            $this->rates = [];
        }

        foreach ($xml->Cube->Cube as $cube) {
            foreach ($cube->Cube as $rate) {
                $this->rates[(string) $cube['time']][(string) $rate['currency']] = (float) (string) $rate['rate'];
            }
        }

        $this->lastFetchTime = time();
    }

    /**
     * @return bool
     */
    private function shouldFetchRates(DateTimeInterface $date): bool
    {

        if ($date < new DateTimeImmutable('1999-01-04', new DateTimeZone('Europe/Paris'))) {
            return false;
        }

        return [] === $this->rates
            || !isset($this->rates[$date->format('Y-m-d')])
            || null === $this->lastFetchTime
            || time() > ($this->lastFetchTime + $this->ttl);
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
