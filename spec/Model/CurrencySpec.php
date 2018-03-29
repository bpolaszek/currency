<?php

namespace spec\BenTools\Currency\Model;

use BenTools\Currency\Model\Currency;
use BenTools\Currency\Model\CurrencyInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class CurrencySpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->beConstructedWith('USD');
        $this->shouldHaveType(Currency::class);
        $this->shouldHaveType(CurrencyInterface::class);
    }

    function it_returns_the_proper_code()
    {
        $this->beConstructedWith('USD');
        $this->getCode()->shouldReturn('USD');
    }
}
