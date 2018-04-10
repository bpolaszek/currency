<?php

namespace BenTools\Currency\Tests\Provider;

use BenTools\Currency\Model\Currency;
use BenTools\Currency\Model\CurrencyInterface;
use BenTools\Currency\Model\ExchangeRateInterface;
use BenTools\Currency\Provider\DoctrineORMProvider;
use BenTools\DoctrineStatic\ManagerRegistry;
use BenTools\DoctrineStatic\ObjectManager;
use BenTools\DoctrineStatic\ObjectRepository;
use PHPUnit\Framework\TestCase;

class DoctrineORMProviderTest extends TestCase
{

    /**
     * @var ExchangeRateInterface
     */
    private $prototype;

    /**
     * @var ManagerRegistry
     */
    private $doctrine;

    public function setUp()
    {
        $prototype = $this->prototype();
        $this->doctrine = new ManagerRegistry([
            'default' => new ObjectManager([
                new ObjectRepository(get_class($prototype)),
            ])
        ]);
    }

    public function testGetExchangeRate()
    {
        $className = get_class($this->prototype);
        $yesterday = new \DateTime('yesterday');
        $provider = new DoctrineORMProvider($this->doctrine, $className);
        $exchangeRate = $this->prototype('USD', 'EUR', 1.12, $yesterday);
        $em = $this->doctrine->getManager();
        $em->persist($exchangeRate);
        $em->flush();

        $this->assertSame($exchangeRate, $provider->getExchangeRate(new Currency('USD'), new Currency('EUR'), $yesterday));
    }

    /**
     * @param null                    $sourceCurrencyCode
     * @param null                    $targetCurrencyCode
     * @param float|null              $ratio
     * @param \DateTimeInterface|null $day
     * @return ExchangeRateInterface
     */
    private function prototype($sourceCurrencyCode = null, $targetCurrencyCode = null, float $ratio = null, \DateTimeInterface $day = null): ExchangeRateInterface
    {
        if (null === $this->prototype) {
            $this->prototype = new class($sourceCurrencyCode, $targetCurrencyCode, $ratio) implements ExchangeRateInterface
            {

                public $id, $sourceCurrencyCode, $targetCurrencyCode, $ratio, $day;

                /**
                 *  constructor.
                 * @param CurrencyInterface       $sourceCurrency
                 * @param CurrencyInterface       $targetCurrency
                 * @param float                   $ratio
                 * @param \DateTimeInterface|null $day
                 */
                public function __construct($sourceCurrencyCode = null, $targetCurrencyCode = null, float $ratio = null, \DateTimeInterface $day = null)
                {
                    if (null !== $sourceCurrencyCode && null !== $targetCurrencyCode) {
                        $this->id = sprintf('%s%s', $sourceCurrencyCode, $targetCurrencyCode);
                    }
                    $this->sourceCurrencyCode = $sourceCurrencyCode;
                    $this->targetCurrencyCode = $targetCurrencyCode;
                    $this->ratio = $ratio;
                    $this->day = $day;
                }

                public function getRatio(): float
                {
                    return $this->ratio;
                }

                public function getSourceCurrency(): CurrencyInterface
                {
                    return new Currency($this->sourceCurrencyCode);
                }

                public function getTargetCurrency(): CurrencyInterface
                {
                    return new Currency($this->targetCurrencyCode);
                }

                public function new($sourceCurrencyCode = null, $targetCurrencyCode = null, float $ratio = null, \DateTimeInterface $day = null): self
                {
                    return new self($sourceCurrencyCode, $targetCurrencyCode, $ratio, $day);
                }

            };
            return $this->prototype;
        }
        return $this->prototype->new($sourceCurrencyCode, $targetCurrencyCode, $ratio, $day);
    }
}
