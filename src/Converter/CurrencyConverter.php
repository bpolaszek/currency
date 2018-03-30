<?php

namespace BenTools\Currency\Converter;

use BenTools\Currency\Model\CurrencyInterface;
use BenTools\Currency\Model\ExchangeRateInterface;

final class CurrencyConverter implements CurrencyConverterInterface
{
    /**
     * @var ExchangeRateInterface[]
     */
    private $exchangeRates = [];

    /**
     * CurrencyConverter constructor.
     * @param ExchangeRateInterface[] ...$exchangeRates
     */
    public function __construct(ExchangeRateInterface ...$exchangeRates)
    {
        foreach ($exchangeRates as $exchangeRate) {
            $this->registerExchangeRate($exchangeRate);
        }
    }

    /**
     * @param ExchangeRateInterface $exchangeRate
     */
    public function registerExchangeRate(ExchangeRateInterface $exchangeRate): void
    {
        $source = $exchangeRate->getSourceCurrency()->getCode();
        $target = $exchangeRate->getTargetCurrency()->getCode();
        $this->exchangeRates[$source][$target] = $exchangeRate;

        $invertExchangeRate = $exchangeRate->invertCurrencies();

        if (!$this->hasExchangeRate($invertExchangeRate->getSourceCurrency()->getCode(), $invertExchangeRate->getTargetCurrency()->getCode())) {
            $source = $invertExchangeRate->getSourceCurrency()->getCode();
            $target = $invertExchangeRate->getTargetCurrency()->getCode();
            $this->exchangeRates[$source][$target] = $invertExchangeRate;
        }
    }

    /**
     * @param string $sourceCurrencyCode
     * @param string $targetCurrencyCode
     * @return ExchangeRateInterface|null
     */
    public function getExchangeRate(string $sourceCurrencyCode, string $targetCurrencyCode): ?ExchangeRateInterface
    {
        return $this->exchangeRates[$sourceCurrencyCode][$targetCurrencyCode] ?? null;
    }

    /**
     * @param string $sourceCurrencyCode
     * @param string $targetCurrencyCode
     * @return bool
     */
    public function hasExchangeRate(string $sourceCurrencyCode, string $targetCurrencyCode): bool
    {
        return isset($this->exchangeRates[$sourceCurrencyCode][$targetCurrencyCode]);
    }

    /**
     * @inheritDoc
     */
    public function convert(float $amount, CurrencyInterface $sourceCurrency, CurrencyInterface $targetCurrency): float
    {
        $exchangeRate = $this->getExchangeRate($sourceCurrency->getCode(), $targetCurrency->getCode());

        if (null === $exchangeRate) {
            throw new \RuntimeException(sprintf("No exchange rate registered for converting %s to %s", $sourceCurrency->getCode(), $targetCurrency->getCode()));
        }

        return $amount * $exchangeRate->getRatio();
    }
}
