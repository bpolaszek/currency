<?php

namespace BenTools\Currency\Provider;

use BenTools\Currency\Model\CurrencyInterface;
use BenTools\Currency\Model\ExchangeRateInterface;
use BenTools\Currency\Model\ExchangeRateNotFoundException;
use DateTimeInterface;

final class ChainedExchangeRateProvider implements ExchangeRateProviderInterface
{
    /**
     * @var ExchangeRateProviderInterface[]
     */
    private $exchangeRateProviders = [];

    /**
     * ChainedExchangeRateProvider constructor.
     * @param ExchangeRateProviderInterface[] $exchangeRateProviders
     */
    public function __construct(array $exchangeRateProviders)
    {
        $this->exchangeRateProviders = (function (ExchangeRateProviderInterface ...$exchangeRateProviders) {
            return $exchangeRateProviders;
        })(...$exchangeRateProviders);
    }

    /**
     * @inheritDoc
     */
    public function getExchangeRate(CurrencyInterface $sourceCurrency, CurrencyInterface $targetCurrency, DateTimeInterface $date = null): ExchangeRateInterface
    {
        foreach ($this->exchangeRateProviders as $exchangeRateProvider) {
            try {
                return $exchangeRateProvider->getExchangeRate($sourceCurrency, $targetCurrency, $date);
            } catch (ExchangeRateNotFoundException $e) {
                continue;
            }
        }
        throw new ExchangeRateNotFoundException($sourceCurrency, $targetCurrency);
    }

    /**
     * @param ExchangeRateProviderInterface ...$exchangeRateProviders
     * @return ChainedExchangeRateProvider
     */
    public function withProviders(ExchangeRateProviderInterface ...$exchangeRateProviders): self
    {
        $clone = clone $this;
        foreach ($exchangeRateProviders as $exchangeRateProvider) {
            $clone->exchangeRateProviders[] = $exchangeRateProvider;
        }
        return $clone;
    }
}
