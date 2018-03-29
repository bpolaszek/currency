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

    /**
     * The implementation must return a NEW object with swapped currencies and inverted ratio.
     *
     * @return ExchangeRateInterface
     */
    public function invertCurrencies(): ExchangeRateInterface;
}
