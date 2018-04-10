<?php

namespace BenTools\Currency\Tests\Provider;

use BenTools\Currency\Cache\ArrayCache;
use BenTools\Currency\Model\Currency;
use BenTools\Currency\Model\CurrencyInterface;
use BenTools\Currency\Model\ExchangeRate;
use BenTools\Currency\Model\ExchangeRateInterface;
use BenTools\Currency\Provider\ExchangeRateProviderInterface;
use BenTools\Currency\Provider\PSR16CacheProvider;
use DateTimeInterface;
use PHPUnit\Framework\TestCase;

class PSR16CacheProviderTest extends TestCase
{

    public function testGetExchangeRate()
    {

        $liveProvider = new class implements ExchangeRateProviderInterface
        {
            private $counter = 0;

            public function getExchangeRate(CurrencyInterface $sourceCurrency, CurrencyInterface $targetCurrency, DateTimeInterface $date = null): ExchangeRateInterface
            {
                $this->counter++;
                return new ExchangeRate($sourceCurrency, $targetCurrency, 0.812);
            }

            /**
             * @return int
             */
            public function getCounter(): int
            {
                return $this->counter;
            }

        };

        $cache = new ArrayCache();
        $cachedProvider = new PSR16CacheProvider($liveProvider, $cache);

        $EUR = new Currency('EUR');
        $USD = new Currency('USD');

        $exchangeRate = $cachedProvider->getExchangeRate($USD, $EUR);
        $this->assertEquals(0.812, $exchangeRate->getRatio());
        $this->assertEquals(1, $liveProvider->getCounter());

        $exchangeRate = $cachedProvider->getExchangeRate($USD, $EUR);
        $this->assertEquals(0.812, $exchangeRate->getRatio());
        $this->assertEquals(1, $liveProvider->getCounter());

    }

}
