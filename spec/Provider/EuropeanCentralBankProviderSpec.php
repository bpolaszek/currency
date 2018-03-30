<?php

namespace spec\BenTools\Currency\Provider;

use BenTools\Currency\Provider\EuropeanCentralBankProvider;
use PhpSpec\ObjectBehavior;

class EuropeanCentralBankProviderSpec extends ObjectBehavior
{

    function it_is_initializable()
    {
        $this->shouldHaveType(EuropeanCentralBankProvider::class);
    }
}
