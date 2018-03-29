<?php

namespace BenTools\Currency\Model;

class ExchangeRateNotFoundException extends \RuntimeException
{
    /**
     * @inheritDoc
     */
    public function __construct(CurrencyInterface $sourceCurrency, CurrencyInterface $targetCurrency, string $message = null)
    {
        $message = $message ?? sprintf('Unable to find exchange rate for %s to %s.', $sourceCurrency->getCode(), $targetCurrency->getName());
        parent::__construct($message);
    }
}
