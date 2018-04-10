<?php

namespace BenTools\Currency\Tests\Model;

use BenTools\Currency\Model\Currency;
use BenTools\Currency\Model\ExchangeRate;
use BenTools\Currency\Model\NativeExchangeRateFactory;
use PHPUnit\Framework\TestCase;

class NativeExchangeRateFactoryTest extends TestCase
{

    public function testCreate()
    {
        $sourceCurrency = new Currency('EUR');
        $targetCurrency = new Currency('USD');
        $ratio = 1.12;
        $factory = new NativeExchangeRateFactory();
        $exchangeRate = $factory->create($sourceCurrency, $targetCurrency, $ratio);
        $this->assertInstanceOf(ExchangeRate::class, $exchangeRate);
        $this->assertSame($sourceCurrency, $exchangeRate->getSourceCurrency());
        $this->assertSame($targetCurrency, $exchangeRate->getTargetCurrency());
        $this->assertSame($ratio, $exchangeRate->getRatio());
    }
}
