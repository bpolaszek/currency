<?php

namespace BenTools\Currency\Model;

interface ExchangeRateInterface
{
    /**
     * @return float|null
     */
    public function getRatio(): float;

    /**
     * @return CurrencyInterface|null
     */
    public function getSourceCurrency(): CurrencyInterface;

    /**
     * @return CurrencyInterface|null
     */
    public function getTargetCurrency(): CurrencyInterface;
}
