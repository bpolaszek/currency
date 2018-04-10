<?php

namespace BenTools\Currency\Tests\Provider;

use BenTools\Currency\Model\Currency;
use BenTools\Currency\Model\ExchangeRateInterface;
use BenTools\Currency\Provider\FixerIOProvider;
use BenTools\Currency\Tests\ClientMockTrait;
use BenTools\Currency\Tests\Tests;
use Http\Mock\Client;
use PHPUnit\Framework\TestCase;

class FixerIOProviderTest extends TestCase
{

    use ClientMockTrait;

    /**
     * @var FixerIOProvider
     */
    private $provider;

    public function setUp()
    {
        $this->client = new Client();
        $this->provider = new FixerIOProvider('dummy_access_key', $this->client);
    }

    public function testGetExchangeRate()
    {
        $EUR = new Currency('EUR');
        $USD = new Currency('USD');

        $this->mockResponse(Tests::loadFixtureFile('FixerIO/2018-03-28.json'));
        $exchangeRate = $this->provider->getExchangeRate($EUR, $USD, new \DateTime('2018-03-28'));
        $this->assertInstanceOf(ExchangeRateInterface::class, $exchangeRate);
        $this->assertEquals(1.232735, $exchangeRate->getRatio());

        $this->mockResponse(Tests::loadFixtureFile('FixerIO/2018-03-28.json'));
        $exchangeRate = $this->provider->getExchangeRate($USD, $EUR, new \DateTime('2018-03-28'));
        $this->assertInstanceOf(ExchangeRateInterface::class, $exchangeRate);
        $this->assertEquals(1 / 1.232735, $exchangeRate->getRatio());
    }

    public function testGetLastExchangeRate()
    {
        $EUR = new Currency('EUR');
        $USD = new Currency('USD');

        $content = Tests::loadFixtureFile('FixerIO/live.json');
        $content = str_replace('{{live}}', date('Y-m-d'), $content);

        $this->mockResponse($content);
        $exchangeRate = $this->provider->getExchangeRate($EUR, $USD);
        $this->assertInstanceOf(ExchangeRateInterface::class, $exchangeRate);
        $this->assertEquals(1.232744, $exchangeRate->getRatio());

        $this->mockResponse($content);
        $exchangeRate = $this->provider->getExchangeRate($USD, $EUR);
        $this->assertInstanceOf(ExchangeRateInterface::class, $exchangeRate);
        $this->assertEquals(1 / 1.232744, $exchangeRate->getRatio());
    }

    /**
     * @expectedException  \BenTools\Currency\Model\ExchangeRateNotFoundException
     */
    public function testExchangeRateFails()
    {
        $GBP = new Currency('GBP');
        $USD = new Currency('USD');

        $this->mockResponse(Tests::loadFixtureFile('FixerIO/2018-03-28.json'));
        $exchangeRate = $this->provider->getExchangeRate($GBP, $USD, new \DateTime('2018-03-28'));
    }
}
