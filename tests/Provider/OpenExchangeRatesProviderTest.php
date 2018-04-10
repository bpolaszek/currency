<?php

namespace BenTools\Currency\Tests\Provider;

use BenTools\Currency\Model\Currency;
use BenTools\Currency\Model\ExchangeRateInterface;
use BenTools\Currency\Provider\OpenExchangeRatesProvider;
use BenTools\Currency\Tests\ClientMockTrait;
use BenTools\Currency\Tests\Tests;
use Http\Mock\Client;
use PHPUnit\Framework\TestCase;

class OpenExchangeRatesProviderTest extends TestCase
{

    use ClientMockTrait;

    /**
     * @var OpenExchangeRatesProvider
     */
    private $provider;

    public function setUp()
    {
        $this->client = new Client();
        $this->provider = new OpenExchangeRatesProvider('dummy_access_key', $this->client);
    }

    public function testGetExchangeRate()
    {
        $EUR = new Currency('EUR');
        $USD = new Currency('USD');

        $this->mockResponse(Tests::loadFixtureFile('OpenExchangeRates/2018-03-28.json'));
        $exchangeRate = $this->provider->getExchangeRate($EUR, $USD, new \DateTime('2018-03-28'));
        $this->assertInstanceOf(ExchangeRateInterface::class, $exchangeRate);
        $this->assertEquals(1 / 0.812179, $exchangeRate->getRatio());

        $this->mockResponse(Tests::loadFixtureFile('OpenExchangeRates/2018-03-28.json'));
        $exchangeRate = $this->provider->getExchangeRate($USD, $EUR, new \DateTime('2018-03-28'));
        $this->assertInstanceOf(ExchangeRateInterface::class, $exchangeRate);
        $this->assertEquals(0.812179, $exchangeRate->getRatio());
    }

    public function testGetLastExchangeRate()
    {
        $EUR = new Currency('EUR');
        $USD = new Currency('USD');

        $content = Tests::loadFixtureFile('OpenExchangeRates/live.json');

        $this->mockResponse($content);
        $exchangeRate = $this->provider->getExchangeRate($EUR, $USD);
        $this->assertInstanceOf(ExchangeRateInterface::class, $exchangeRate);
        $this->assertEquals(1 / 0.81159, $exchangeRate->getRatio());

        $this->mockResponse($content);
        $exchangeRate = $this->provider->getExchangeRate($USD, $EUR);
        $this->assertInstanceOf(ExchangeRateInterface::class, $exchangeRate);
        $this->assertEquals(0.81159, $exchangeRate->getRatio());
    }

    /**
     * @expectedException  \BenTools\Currency\Model\ExchangeRateNotFoundException
     */
    public function testExchangeRateFails()
    {
        $GBP = new Currency('GBP');
        $EUR = new Currency('EUR');

        $this->mockResponse(Tests::loadFixtureFile('OpenExchangeRates/2018-03-28.json'));
        $exchangeRate = $this->provider->getExchangeRate($GBP, $EUR, new \DateTime('2018-03-28'));
    }
}
