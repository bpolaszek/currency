<?php

namespace BenTools\Currency\Converter;

interface CurrencyConverterInterface
{

    /**
     * @param float  $amount
     * @param string $sourceCurrencyCode
     * @param string $targetCurrencyCode
     * @return float
     */
    public function convert(float $amount, string $sourceCurrencyCode, string $targetCurrencyCode): float;
}
