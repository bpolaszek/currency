<?php

namespace BenTools\Currency\Model;

interface ExchangeRateFactoryInterface
{

    /**
     * @param CurrencyInterface $sourceCurrency
     * @param CurrencyInterface $targetCurrency
     * @param float             $ratio
     * @return ExchangeRateInterface
     */
    public function create(CurrencyInterface $sourceCurrency, CurrencyInterface $targetCurrency, float $ratio): ExchangeRateInterface;
}
