<?php

namespace spec\BenTools\Currency\Provider;

use BenTools\Currency\Provider\EuropeanCentralBankRateProvider;
use PhpSpec\ObjectBehavior;

class EuropeanCentralBankRateProviderSpec extends ObjectBehavior
{

    function it_is_initializable()
    {
        $this->shouldHaveType(EuropeanCentralBankRateProvider::class);
    }
}
