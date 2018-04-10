<?php

namespace BenTools\Currency\Tests\Converter;

use BenTools\Currency\Converter\CurrencyConverter;
use BenTools\Currency\Model\Currency;
use BenTools\Currency\Model\ExchangeRate;
use BenTools\Currency\Model\ExchangeRateNotFoundException;
use PHPUnit\Framework\TestCase;

class CurrencyConverterTest extends TestCase
{

    public function testOneExchangeRateConverter()
    {
        $USD = new Currency('USD');
        $EUR = new Currency('EUR');
        $converter = new CurrencyConverter(new ExchangeRate($USD, $EUR, 1.23145));
        $this->assertEquals(2 * 1.23145, $converter->convert(2, $USD, $EUR));
        $this->assertEquals(2 * (1 / 1.23145), $converter->convert(2, $EUR, $USD));
    }

    public function testMultipleExchangeRateConverter()
    {
        $USD = new Currency('USD');
        $EUR = new Currency('EUR');
        $GBP = new Currency('GBP');
        $USDtoEUR = new ExchangeRate($USD, $EUR, 1.23145);
        $EURtoGBP = new ExchangeRate($EUR, $GBP, 0.870596469);

        $converter = new CurrencyConverter($USDtoEUR, $EURtoGBP);
        $this->assertEquals(2 * 1.23145, $converter->convert(2, $USD, $EUR));
        $this->assertEquals(2 * (1 / 1.23145), $converter->convert(2, $EUR, $USD));
        $this->assertEquals(2 * 0.870596469, $converter->convert(2, $EUR, $GBP));
        $this->assertEquals(2 * (1 / 0.870596469), $converter->convert(2, $GBP, $EUR));
    }

    /**
     * @expectedException \BenTools\Currency\Model\ExchangeRateNotFoundException
     */
    public function testExchangeRateNotFound()
    {
        $USD = new Currency('USD');
        $EUR = new Currency('EUR');
        $GBP = new Currency('GBP');
        $USDtoEUR = new ExchangeRate($USD, $EUR, 1.23145);
        $EURtoGBP = new ExchangeRate($EUR, $GBP, 0.870596469);

        $converter = new CurrencyConverter($USDtoEUR, $EURtoGBP);
        $converter->convert(2, $USD, $GBP);
    }

}
