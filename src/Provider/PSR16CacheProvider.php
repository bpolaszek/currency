<?php

namespace BenTools\Currency\Provider;

use BenTools\Currency\Cache\ArrayCache;
use BenTools\Currency\Model\CurrencyInterface;
use BenTools\Currency\Model\ExchangeRateInterface;
use DateTimeInterface;
use Psr\SimpleCache\CacheInterface;

final class PSR16CacheProvider implements ExchangeRateProviderInterface
{
    /**
     * @var ExchangeRateProviderInterface
     */
    private $exchangeRateProvider;

    /**
     * @var CacheInterface
     */
    private $cache;

    /**
     * @var bool
     */
    private $storeInvertedRate;

    /**
     * PSR16CacheProvider constructor.
     * @param ExchangeRateProviderInterface $exchangeRateProvider
     * @param CacheInterface|null           $cache
     * @param bool                          $storeInvertedRate
     */
    public function __construct(
        ExchangeRateProviderInterface $exchangeRateProvider,
        CacheInterface $cache = null,
        bool $storeInvertedRate = false
    ) {
        $this->exchangeRateProvider = $exchangeRateProvider;
        $this->cache = $cache ?? new ArrayCache();
        $this->storeInvertedRate = $storeInvertedRate;
    }

    /**
     * @inheritDoc
     */
    public function getExchangeRate(CurrencyInterface $sourceCurrency, CurrencyInterface $targetCurrency, DateTimeInterface $date = null): ExchangeRateInterface
    {
        if ($this->cache->has($this->getKey($sourceCurrency, $targetCurrency, $date))) {
            return $this->cache->get($this->getKey($sourceCurrency, $targetCurrency, $date));
        }

        $exchangeRate = $this->exchangeRateProvider->getExchangeRate($sourceCurrency, $targetCurrency, $date);
        $this->cache->set($this->getKey($sourceCurrency, $targetCurrency, $date), $exchangeRate);

        if (true === $this->storeInvertedRate) {
            $this->cache->set($this->getKey($targetCurrency, $sourceCurrency, $date), $exchangeRate->swapCurrencies());
        }

        return $exchangeRate;
    }

    /**
     * @param CurrencyInterface      $sourceCurrency
     * @param CurrencyInterface      $targetCurrency
     * @param DateTimeInterface|null $date
     * @return string
     */
    private function getKey(CurrencyInterface $sourceCurrency, CurrencyInterface $targetCurrency, DateTimeInterface $date = null): string
    {
        if (null === $date) {
            return sprintf('%s-%s', $sourceCurrency->getCode(), $targetCurrency->getCode());
        }
        return sprintf('%s-%s-%s', $sourceCurrency->getCode(), $targetCurrency->getCode(), $date->format('U'));
    }
}
