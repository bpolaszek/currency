<?php

namespace BenTools\Currency\Tests\Provider;

use BenTools\Currency\Model\Currency;
use BenTools\Currency\Model\CurrencyInterface;
use BenTools\Currency\Model\ExchangeRate;
use BenTools\Currency\Model\ExchangeRateInterface;
use BenTools\Currency\Model\ExchangeRateNotFoundException;
use BenTools\Currency\Provider\ChainedExchangeRateProvider;
use BenTools\Currency\Provider\ExchangeRateProviderInterface;
use BenTools\Currency\Tests\ProviderMockTrait;
use PHPUnit\Framework\TestCase;
use DateTimeInterface;

class ChainedExchangeRateProviderTest extends TestCase
{
    use ProviderMockTrait;

    public function testGetFirstExchangeRate()
    {
        $provider = new ChainedExchangeRateProvider([
            $this->generateFakeProvider(1.2),
            $this->generateFakeProvider(1.3),
        ]);

        $this->assertEquals(1.2, $provider->getExchangeRate(new Currency('EUR'), new Currency('USD'))->getRatio());
    }

    public function testFallback()
    {
        $GBPtoEURProvider = new class implements ExchangeRateProviderInterface
        {
            public function getExchangeRate(CurrencyInterface $sourceCurrency, CurrencyInterface $targetCurrency, DateTimeInterface $date = null): ExchangeRateInterface
            {
                if ('GBP' === $sourceCurrency->getCode() && 'EUR' === $targetCurrency->getCode()) {
                    return new ExchangeRate($sourceCurrency, $targetCurrency, 1.15);
                }

                throw new ExchangeRateNotFoundException($sourceCurrency, $targetCurrency);
            }

        };

        $USDtoEURProvider = new class implements ExchangeRateProviderInterface
        {
            public function getExchangeRate(CurrencyInterface $sourceCurrency, CurrencyInterface $targetCurrency, DateTimeInterface $date = null): ExchangeRateInterface
            {
                if ('USD' === $sourceCurrency->getCode() && 'EUR' === $targetCurrency->getCode()) {
                    return new ExchangeRate($sourceCurrency, $targetCurrency, 0.81);
                }

                throw new ExchangeRateNotFoundException($sourceCurrency, $targetCurrency);
            }

        };

        $provider = new ChainedExchangeRateProvider([$GBPtoEURProvider, $USDtoEURProvider]);
        $this->assertEquals(0.81, $provider->getExchangeRate(new Currency('USD'), new Currency('EUR'))->getRatio());

    }
}
