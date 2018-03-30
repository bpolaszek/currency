<?php

namespace spec\BenTools\Currency\Converter;

use BenTools\Currency\Converter\CurrencyConverter;
use BenTools\Currency\Converter\CurrencyConverterInterface;
use BenTools\Currency\Model\Currency;
use BenTools\Currency\Model\ExchangeRate;
use BenTools\Currency\Model\ExchangeRateInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class CurrencyConverterSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith(
            new ExchangeRate(
                new Currency('USD'),
                new Currency('EUR'),
                0.803
            )
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(CurrencyConverter::class);
        $this->shouldHaveType(CurrencyConverterInterface::class);
    }

    function it_converts_an_amount_from_one_currency_to_another()
    {
        $this->convert(1, new Currency('USD'), new Currency('EUR'))->shouldReturn(0.803);
        $this->convert(0.803, new Currency('EUR'), new Currency('USD'))->shouldReturn(1.0);
    }

    function it_throws_an_exception_when_no_exchange_rate_is_found()
    {
        $this->shouldThrow()->during('convert', [1, new Currency('USD'), new Currency('GBP')]);
    }
}
