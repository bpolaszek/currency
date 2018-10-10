<?php

namespace BenTools\Currency\Model;

interface ExchangeRateInterface
{
    /**
     * @return float
     */
    public function getRatio(): float;

    /**
     * @return CurrencyInterface
     */
    public function getSourceCurrency(): CurrencyInterface;

    /**
     * @return CurrencyInterface
     */
    public function getTargetCurrency(): CurrencyInterface;
}
