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
        $this->exchangeRates[$source][$target] = $exchangeRate->getRatio();

        if (!$this->hasExchangeRate($exchangeRate->getTargetCurrency()->getCode(), $exchangeRate->getSourceCurrency()->getCode())) {
            $source = $exchangeRate->getTargetCurrency()->getCode();
            $target = $exchangeRate->getSourceCurrency()->getCode();
            $this->exchangeRates[$source][$target] = 1 / $exchangeRate->getRatio();
        }
    }

    /**
     * @param string $sourceCurrencyCode
     * @param string $targetCurrencyCode
     * @return ExchangeRateInterface|null
     */
    private function getExchangeRate(string $sourceCurrencyCode, string $targetCurrencyCode): ?float
    {
        return $this->exchangeRates[$sourceCurrencyCode][$targetCurrencyCode] ?? null;
    }

    /**
     * @param string $sourceCurrencyCode
     * @param string $targetCurrencyCode
     * @return bool
     */
    private function hasExchangeRate(string $sourceCurrencyCode, string $targetCurrencyCode): bool
    {
        return isset($this->exchangeRates[$sourceCurrencyCode][$targetCurrencyCode]);
    }

    /**
     * @inheritDoc
     */
    public function convert(float $amount, CurrencyInterface $sourceCurrency, CurrencyInterface $targetCurrency): float
    {
        $ratio = $this->getExchangeRate($sourceCurrency->getCode(), $targetCurrency->getCode());

        if (null === $ratio) {
            throw new \RuntimeException(sprintf("No exchange rate registered for converting %s to %s", $sourceCurrency->getCode(), $targetCurrency->getCode()));
        }

        return $amount * $ratio;
    }
}
