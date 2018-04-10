<?php

namespace BenTools\Currency\Tests\Provider;

use BenTools\Currency\Model\Currency;
use BenTools\Currency\Model\ExchangeRateInterface;
use BenTools\Currency\Provider\EuropeanCentralBankProvider;
use BenTools\Currency\Tests\ClientMockTrait;
use BenTools\Currency\Tests\Tests;
use Http\Mock\Client;
use PHPUnit\Framework\TestCase;

class EuropeanCentralBankProviderTest extends TestCase
{

    use ClientMockTrait;

    /**
     * @var EuropeanCentralBankProvider
     */
    private $provider;

    public function setUp()
    {
        $this->client = new Client();
        $this->provider = new EuropeanCentralBankProvider($this->client);
    }

    public function testGetExchangeRate()
    {
        $EUR = new Currency('EUR');
        $USD = new Currency('USD');

        $this->mockResponse(Tests::loadFixtureFile('EuropeanCentralBank/2018-03-28.xml'));
        $exchangeRate = $this->provider->getExchangeRate($EUR, $USD, new \DateTime('2018-03-28'));
        $this->assertInstanceOf(ExchangeRateInterface::class, $exchangeRate);
        $this->assertEquals(1.2398, $exchangeRate->getRatio());

        $this->mockResponse(Tests::loadFixtureFile('EuropeanCentralBank/2018-03-28.xml'));
        $exchangeRate = $this->provider->getExchangeRate($USD, $EUR, new \DateTime('2018-03-28'));
        $this->assertInstanceOf(ExchangeRateInterface::class, $exchangeRate);
        $this->assertEquals(1 / 1.2398, $exchangeRate->getRatio());
    }

    public function testGetLastExchangeRate()
    {
        $EUR = new Currency('EUR');
        $USD = new Currency('USD');

        $content = Tests::loadFixtureFile('EuropeanCentralBank/live.xml');
        $content = str_replace('{{live}}', date('Y-m-d'), $content);

        $this->mockResponse($content);
        $exchangeRate = $this->provider->getExchangeRate($EUR, $USD);
        $this->assertInstanceOf(ExchangeRateInterface::class, $exchangeRate);
        $this->assertEquals(1.2304, $exchangeRate->getRatio());

        $this->mockResponse($content);
        $exchangeRate = $this->provider->getExchangeRate($USD, $EUR);
        $this->assertInstanceOf(ExchangeRateInterface::class, $exchangeRate);
        $this->assertEquals(1 / 1.2304, $exchangeRate->getRatio());
    }

    /**
     * @expectedException  \BenTools\Currency\Model\ExchangeRateNotFoundException
     */
    public function testExchangeRateFails()
    {
        $GBP = new Currency('GBP');
        $USD = new Currency('USD');

        $this->mockResponse(Tests::loadFixtureFile('EuropeanCentralBank/2018-03-28.xml'));
        $exchangeRate = $this->provider->getExchangeRate($GBP, $USD, new \DateTime('2018-03-28'));
    }

}
