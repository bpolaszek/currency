<?php

namespace BenTools\Currency\Model;

final class NativeExchangeRateFactory implements ExchangeRateFactoryInterface
{
    /**
     * @inheritDoc
     */
    public function create(CurrencyInterface $sourceCurrency, CurrencyInterface $targetCurrency, float $ratio): ExchangeRateInterface
    {
        return new ExchangeRate($sourceCurrency, $targetCurrency, $ratio);
    }
}
