<?php

namespace BenTools\Currency\Tests\Provider;

use BenTools\Currency\Model\Currency;
use BenTools\Currency\Model\ExchangeRateInterface;
use BenTools\Currency\Provider\AverageExchangeRateProvider;
use BenTools\Currency\Tests\ProviderMockTrait;
use PHPUnit\Framework\TestCase;

class AverageExchangeRateProviderTest extends TestCase
{
    use ProviderMockTrait;

    public function testGetExchangeRate()
    {

        $numberOfProviders = random_int(3, 10);
        $ratios = [];
        for ($i = 1; $i <= $numberOfProviders; $i++) {
            $ratios[] = $ratio = $this->generateFakeRatio();
            $providers[] = $this->generateFakeProvider($ratio);
        }

        $provider = AverageExchangeRateProvider::create()->withProviders(...$providers);

        $averageRatio = array_sum($ratios) / count($ratios);
        $exchangeRate = $provider->getExchangeRate(new Currency('EUR'), new Currency('USD'));
        $this->assertInstanceOf(ExchangeRateInterface::class, $exchangeRate);
        $this->assertEquals($averageRatio, $exchangeRate->getRatio());
    }

    public function testPrecision()
    {
        $provider = AverageExchangeRateProvider::create(0.02)->withProviders(
            $this->generateFakeProvider(1.12),
            $this->generateFakeProvider(1.13)
        );
        $exchangeRate = $provider->getExchangeRate(new Currency('EUR'), new Currency('USD'));
        $this->assertEquals(1.125, $exchangeRate->getRatio());
    }

    /**
     * @expectedException  \RuntimeException
     * @expectedExceptionMessage Tolerance fault: 0.03 difference between minimum and maximum ratio, 0.02 allowed.
     */
    public function testPrecisionFails()
    {
        $provider = AverageExchangeRateProvider::create(0.02)->withProviders(
            $this->generateFakeProvider(1.10),
            $this->generateFakeProvider(1.13)
        );
        $exchangeRate = $provider->getExchangeRate(new Currency('EUR'), new Currency('USD'));
    }

}
