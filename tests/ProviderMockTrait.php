<?php

namespace BenTools\Currency\Tests;

use BenTools\Currency\Model\CurrencyInterface;
use BenTools\Currency\Model\ExchangeRate;
use BenTools\Currency\Model\ExchangeRateInterface;
use BenTools\Currency\Provider\ExchangeRateProviderInterface;
use DateTimeInterface;

trait ProviderMockTrait
{
    /**
     * @param float $ratio
     * @return ExchangeRateProviderInterface
     */
    private function generateFakeProvider(float $ratio): ExchangeRateProviderInterface
    {
        return new class($ratio) implements ExchangeRateProviderInterface
        {
            private $ratio;

            public function __construct(float $ratio)
            {
                $this->ratio = $ratio;
            }

            public function getExchangeRate(CurrencyInterface $sourceCurrency, CurrencyInterface $targetCurrency, DateTimeInterface $date = null): ExchangeRateInterface
            {
                return new ExchangeRate($sourceCurrency, $targetCurrency, $this->ratio);
            }

        };
    }

    /**
     * @return float
     * @throws \Exception
     */
    private function generateFakeRatio(): float
    {
        return (float) sprintf('%s.%s', random_int(0, 1), random_int(1, 1000));
    }

}