<?php

namespace BenTools\Currency\Model;

final class ExchangeRate implements ExchangeRateInterface
{
    /**
     * @var CurrencyInterface
     */
    private $sourceCurrency;

    /**
     * @var CurrencyInterface
     */
    private $targetCurrency;

    /**
     * @var float
     */
    private $ratio;

    /**
     * ExchangeRate constructor.
     * @param CurrencyInterface $sourceCurrency
     * @param CurrencyInterface $targetCurrency
     * @param float             $ratio
     */
    public function __construct(
        CurrencyInterface $sourceCurrency,
        CurrencyInterface $targetCurrency,
        float $ratio
    ) {
        $this->sourceCurrency = $sourceCurrency;
        $this->targetCurrency = $targetCurrency;
        if ($ratio <= 0) {
            throw new \InvalidArgumentException("Ratio must be a positive float.");
        }
        $this->ratio = $ratio;
    }

    /**
     * @inheritDoc
     */
    public function getRatio(): float
    {
        return $this->ratio;
    }

    /**
     * @inheritDoc
     */
    public function getSourceCurrency(): CurrencyInterface
    {
        return $this->sourceCurrency;
    }

    /**
     * @inheritDoc
     */
    public function getTargetCurrency(): CurrencyInterface
    {
        return $this->targetCurrency;
    }

    /**
     * @inheritDoc
     */
    public function swapCurrencies(): ExchangeRateInterface
    {
        $clone = clone $this;
        $clone->sourceCurrency = $this->targetCurrency;
        $clone->targetCurrency = $this->sourceCurrency;
        $clone->ratio = 1 / $this->ratio;
        return $clone;
    }
}
