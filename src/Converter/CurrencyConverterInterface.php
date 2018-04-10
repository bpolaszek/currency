<?php

namespace BenTools\Currency\Converter;

use BenTools\Currency\Model\CurrencyInterface;
use BenTools\Currency\Model\ExchangeRateNotFoundException;

interface CurrencyConverterInterface
{

    /**
     * @param float             $amount
     * @param CurrencyInterface $sourceCurrency
     * @param CurrencyInterface $targetCurrency
     * @return float
     * @throws ExchangeRateNotFoundException
     */
    public function convert(float $amount, CurrencyInterface $sourceCurrency, CurrencyInterface $targetCurrency): float;
}
