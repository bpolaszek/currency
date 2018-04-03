<?php

namespace BenTools\Currency\Provider;

use BenTools\Currency\Model\CurrencyInterface;
use BenTools\Currency\Model\ExchangeRate;
use BenTools\Currency\Model\ExchangeRateFactoryInterface;
use BenTools\Currency\Model\ExchangeRateInterface;
use BenTools\Currency\Model\ExchangeRateNotFoundException;
use BenTools\Currency\Model\NativeExchangeRateFactory;
use DateTimeInterface;

final class AverageExchangeRateProvider implements ExchangeRateProviderInterface
{
    /**
     * @var ExchangeRateProviderInterface[]
     */
    private $exchangeRateProviders = [];

    /**
     * @var float
     */
    private $tolerance;

    /**
     * @var ExchangeRateFactoryInterface
     */
    private $exchangeRateFactory;

    /**
     * AverageExchangeRateProvider constructor.
     * @param float|null                        $tolerance
     * @param ExchangeRateFactoryInterface|null $exchangeRateFactory
     */
    public function __construct(float $tolerance = null, ExchangeRateFactoryInterface $exchangeRateFactory = null)
    {
        $this->tolerance = $tolerance;
        $this->exchangeRateFactory = $exchangeRateFactory ?? new NativeExchangeRateFactory();
    }

    /**
     * @param ExchangeRateProviderInterface[] ...$exchangeRateProviders
     * @return AverageExchangeRateProvider
     */
    public function withProviders(ExchangeRateProviderInterface ...$exchangeRateProviders): self
    {
        $clone = clone $this;
        foreach ($exchangeRateProviders as $exchangeRateProvider) {
            $clone->exchangeRateProviders[] = $exchangeRateProvider;
        }
        return $clone;
    }

    /**
     * @inheritDoc
     */
    public function getExchangeRate(CurrencyInterface $sourceCurrency, CurrencyInterface $targetCurrency, DateTimeInterface $date = null): ExchangeRateInterface
    {
        $exchangeRates = [];
        foreach ($this->exchangeRateProviders as $e => $exchangeRateProvider) {
            $exchangeRates[$e] = $er = $exchangeRateProvider->getExchangeRate($sourceCurrency, $targetCurrency, $date);
        }

        if (!$exchangeRates) {
            throw new ExchangeRateNotFoundException($sourceCurrency, $targetCurrency);
        }

        $averageRatio = self::getAverageRatio(...$exchangeRates);

        if (null !== $this->tolerance) {
            $this->validate(...$exchangeRates);
        }

        return $this->exchangeRateFactory->create($sourceCurrency, $targetCurrency, $averageRatio);
    }

    /**
     * @param ExchangeRateInterface[] ...$exchangeRates
     * @throws \RuntimeException
     */
    private function validate(ExchangeRateInterface ...$exchangeRates): void
    {
        $ratios = array_map(function (ExchangeRate $exchangeRate) {
            return $exchangeRate->getRatio();
        }, $exchangeRates);

        $min = min($ratios);
        $max = max($ratios);
        $diff = abs($max - $min);

        if ($diff > $this->tolerance) {
            throw new \RuntimeException(sprintf('Tolerance fault: %s difference between minimum and maximum ratio, %s allowed.', $diff, $this->tolerance));
        }
    }

    /**
     * @param array $array
     * @return float
     */
    private static function getAverageRatio(ExchangeRateInterface ...$exchangeRates): float
    {
        return array_sum(
            array_map(
                function (ExchangeRateInterface $exchangeRate): float {
                        return $exchangeRate->getRatio();
                },
                $exchangeRates
            )
        ) / count($exchangeRates);
    }

    /**
     * @param float|null                        $tolerance
     * @param ExchangeRateFactoryInterface|null $exchangeRateFactory
     * @return AverageExchangeRateProvider
     */
    public static function create(float $tolerance = null, ExchangeRateFactoryInterface $exchangeRateFactory = null): self
    {
        return new self($tolerance, $exchangeRateFactory);
    }
}
