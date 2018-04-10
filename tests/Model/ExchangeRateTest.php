<?php

namespace BenTools\Currency\Tests\Model;

use BenTools\Currency\Model\Currency;
use BenTools\Currency\Model\ExchangeRate;
use PHPUnit\Framework\TestCase;

class ExchangeRateTest extends TestCase
{

    public function testConstruct()
    {
        $sourceCurrency = new Currency('EUR');
        $targetCurrency = new Currency('USD');
        $ratio = 1.12;
        $exchangeRate = new ExchangeRate(
            $sourceCurrency,
            $targetCurrency,
            $ratio
        );

        $this->assertSame($sourceCurrency, $exchangeRate->getSourceCurrency());
        $this->assertSame($targetCurrency, $exchangeRate->getTargetCurrency());
        $this->assertSame($ratio, $exchangeRate->getRatio());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testRatioZero()
    {
        $sourceCurrency = new Currency('EUR');
        $targetCurrency = new Currency('USD');
        $ratio = 0;
        $exchangeRate = new ExchangeRate(
            $sourceCurrency,
            $targetCurrency,
            $ratio
        );
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testNegativeRatio()
    {
        $sourceCurrency = new Currency('EUR');
        $targetCurrency = new Currency('USD');
        $ratio = -1;
        $exchangeRate = new ExchangeRate(
            $sourceCurrency,
            $targetCurrency,
            $ratio
        );
    }

    public function testSwapCurrencies()
    {
        $sourceCurrency = new Currency('EUR');
        $targetCurrency = new Currency('USD');
        $ratio = 1.12;
        $exchangeRate = new ExchangeRate(
            $sourceCurrency,
            $targetCurrency,
            $ratio
        );
        $revertedExchangeRate = $exchangeRate->swapCurrencies();

        $this->assertNotSame($revertedExchangeRate, $exchangeRate);
        $this->assertSame($targetCurrency, $revertedExchangeRate->getSourceCurrency());
        $this->assertSame($sourceCurrency, $revertedExchangeRate->getTargetCurrency());
        $this->assertSame(1 / $ratio, $revertedExchangeRate->getRatio());
    }

}
