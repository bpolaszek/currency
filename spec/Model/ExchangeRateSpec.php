<?php

namespace spec\BenTools\Currency\Model;

use BenTools\Currency\Model\Currency;
use BenTools\Currency\Model\ExchangeRate;
use BenTools\Currency\Model\ExchangeRateInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ExchangeRateSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->beConstructedWith(
            new Currency('USD'),
            new Currency('EUR'),
            0.803
        );
        $this->shouldHaveType(ExchangeRate::class);
        $this->shouldHaveType(ExchangeRateInterface::class);
    }

    function it_throws_an_exception_when_ratio_invalid()
    {
        $this->beConstructedWith(
            new Currency('USD'),
            new Currency('EUR'),
            0
        );
        $this->shouldThrow('\InvalidArgumentException')->duringInstantiation();
    }

    function it_inverts_properly()
    {
        $this->beConstructedWith(
            new Currency('USD'),
            new Currency('EUR'),
            0.803
        );
        $this->invertCurrencies()->shouldReturnAnInstanceOf(ExchangeRate::class);
        $this->invertCurrencies()->getRatio()->shouldBeApproximately(1.245, 0.001);
        $this->invertCurrencies()->getSourceCurrency()->getCode()->shouldEqual('EUR');
        $this->invertCurrencies()->getTargetCurrency()->getCode()->shouldEqual('USD');
    }
}
