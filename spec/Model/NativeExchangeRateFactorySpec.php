<?php

namespace spec\BenTools\Currency\Model;

use BenTools\Currency\Model\Currency;
use BenTools\Currency\Model\ExchangeRate;
use BenTools\Currency\Model\NativeExchangeRateFactory;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class NativeExchangeRateFactorySpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(NativeExchangeRateFactory::class);
    }

    function it_returns_an_exchange_rate_object()
    {
        $this->create(new Currency('USD'), new Currency('EUR'), 1)->shouldReturnAnInstanceOf(ExchangeRate::class);
    }
}
