<?php

namespace BenTools\Currency\Tests\Model;

use BenTools\Currency\Model\Currency;
use PHPUnit\Framework\TestCase;

class CurrencyTest extends TestCase
{

    public function testGetCode()
    {
        $currency = new Currency('eur');
        $this->assertEquals('EUR', $currency->getCode());
    }
}
