[![Latest Stable Version](https://poser.pugx.org/bentools/currency/v/stable)](https://packagist.org/packages/bentools/currency)
[![License](https://poser.pugx.org/bentools/currency/license)](https://packagist.org/packages/bentools/currency)
[![Build Status](https://img.shields.io/travis/bpolaszek/currency/master.svg?style=flat-square)](https://travis-ci.org/bpolaszek/currency)
[![Quality Score](https://img.shields.io/scrutinizer/g/bpolaszek/currency.svg?style=flat-square)](https://scrutinizer-ci.com/g/bpolaszek/currency)
[![Total Downloads](https://poser.pugx.org/bentools/currency/downloads)](https://packagist.org/packages/bentools/currency)

A very simple, framework-agnostic, PHP library to work with currencies.

## Install

> composer require bentools/currency:1.0.x-dev

## Example use

```php
use BenTools\Currency\Converter\CurrencyConverter;
use BenTools\Currency\Model\Currency;
use BenTools\Currency\Provider\EuropeanCentralBankProvider;
use DateTime;

$eur = new Currency('EUR');
$usd = new Currency('USD');

$exchangeRateProvider = new EuropeanCentralBankProvider();
$exchangeRate = $exchangeRateProvider->getExchangeRate($eur, $usd, new DateTime('yesterday'));

$currencyConverter = new CurrencyConverter($exchangeRate);
var_dump($currencyConverter->convert(299, $usd, $eur)); // float(242.67510753997)
var_dump($currencyConverter->convert(10.99, $eur, $usd)); // float(13.540779)
```

`bentools/currency` respects SOLID principles. So feel free to implement your own:

- CurrencyInterface (simple value object with a `getCode()` method)
- ExchangeRateInterface (value object with source currency, target currency and ratio)
- ExchangeRateFactoryInterface (how to instanciate ExchangeRate objects)
- ExchangeRateProviderInterface (provide live or historical rates)
- CurrencyConverterInterface (to convert from one currency to another)

## Online rate providers

We provide adapters for popular API providers:

- [European Central Bank](https://www.ecb.europa.eu/stats/policy_and_exchange_rates/euro_reference_exchange_rates/html/index.en.html) - free use without limit, EUR-based, ~32 currencies supported
- [Fixer.io](https://fixer.io/)* - ~170 currencies supported, including cryptocurrencies, EUR-based in free plan
- [CurrencyLayer.com](https://fixer.io/)* - ~170 currencies supported, including cryptocurrencies, USD-based in free plan
- [OpenExchangeRates.org](https://fixer.io/)* - ~200 currencies supported, including cryptocurrencies, USD-based in free plan

_\* Only free plan supported by the built-in adapters. Feel free to submit a PR for supporting paid plans._

## PSR-16 provider

Cache your exchange rates for the duration of your choice with the PSR-16 decorator:

```php
use BenTools\Currency\Model\Currency;
use BenTools\Currency\Provider\EuropeanCentralBankProvider;
use BenTools\Currency\Provider\PSR16CacheProvider;
use Redis;
use Symfony\Component\Cache\Simple\RedisCache;

$eur = new Currency('EUR');
$usd = new Currency('USD');

$redis = new Redis();
$redis->connect('localhost');
$ttl = 3600;
$provider = new PSR16CacheProvider(
    new EuropeanCentralBankProvider(), 
    new RedisCache($redis, 'currencies', $ttl)
);

$exchangeRate = $provider->getExchangeRate($eur, $usd);
```

## Average exchange rate provider

Get an average exchange rate from different providers. If you provide a tolerance, an exception will be thrown if the difference between the lowest and the highest rate exceeds it.

```php
use BenTools\Currency\Model\Currency;
use BenTools\Currency\Provider\AverageExchangeRateProvider;
use BenTools\Currency\Provider\CurrencyLayerProvider;
use BenTools\Currency\Provider\EuropeanCentralBankProvider;
use BenTools\Currency\Provider\FixerIOProvider;
use BenTools\Currency\Provider\OpenExchangeRatesProvider;
use DateTimeImmutable;

$eur = new Currency('EUR');
$usd = new Currency('USD');

$providers = [
    new EuropeanCentralBankProvider(),
    new OpenExchangeRatesProvider($openExchangeApiKey),
    new FixerIOProvider($fixerIoApiKey),
    new CurrencyLayerProvider($currencyLayerApiKey),
];

$tolerance = 0.005;
$exchangeRateProvider = AverageExchangeRateProvider::create($tolerance)->withProviders(...$providers);

dump($exchangeRateProvider->getExchangeRate($eur, $usd, new DateTimeImmutable('2018-03-29'))->getRatio());
```


## Framework agnostic

`bentools/currency` leverages  [HttpPlug](http://docs.php-http.org/en/latest/) to connect to the APIs. This means:

- You're free to use any HTTP client (Guzzle 5, Guzzle 6, React, Zend, Buzz, ...) already existing in your project
- By default, `bentools/currency` will automagically discover the client to use
- You can enforce a specific client with its specific configuration in any `ExchangeRateProvider`.

Don't forget that most free plans limit to 1000 calls/month, so you'd better configure your Http Client to cache responses. If you use Guzzle 6+, have a look at [kevinrob/guzzle-cache-middleware](https://github.com/Kevinrob/guzzle-cache-middleware)

## Tests

> ./vendor/bin/phpspec run


## License

MIT