<?php

namespace BenTools\Currency\Provider;

use BenTools\Currency\Model\CurrencyInterface;
use BenTools\Currency\Model\ExchangeRateInterface;
use BenTools\Currency\Model\ExchangeRateNotFoundException;
use DateTimeInterface;

interface ExchangeRateProviderInterface
{

    /**
     * Return an ExchangeRateInterface object for the given currencies.
     * When no exchange rate can be found, the implementation MUST throw an ExchangeRateNotFoundException.
     *
     * @param CurrencyInterface      $sourceCurrency
     * @param CurrencyInterface      $targetCurrency
     * @param DateTimeInterface|null $date - When null, the implementation must provide the last exchange rate known.
     * @return ExchangeRateInterface
     */
    public function getExchangeRate(CurrencyInterface $sourceCurrency, CurrencyInterface $targetCurrency, DateTimeInterface $date = null): ExchangeRateInterface;
}
