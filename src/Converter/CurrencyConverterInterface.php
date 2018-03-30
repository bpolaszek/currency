<?php

namespace BenTools\Currency\Converter;

use BenTools\Currency\Model\CurrencyInterface;

interface CurrencyConverterInterface
{

    /**
     * @param float             $amount
     * @param CurrencyInterface $sourceCurrency
     * @param CurrencyInterface $targetCurrency
     * @return float
     */
    public function convert(float $amount, CurrencyInterface $sourceCurrency, CurrencyInterface $targetCurrency): float;
}
