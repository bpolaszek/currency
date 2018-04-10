<?php

namespace BenTools\Currency\Provider;

use BenTools\Currency\Model\CurrencyInterface;
use BenTools\Currency\Model\ExchangeRateInterface;
use BenTools\Currency\Model\ExchangeRateNotFoundException;
use DateTimeInterface;
use Doctrine\Common\Persistence\ManagerRegistry;

class DoctrineORMProvider implements ExchangeRateProviderInterface
{
    private const DEFAULT_PROPERTY_MAP = [
        'sourceCurrency' => 'sourceCurrencyCode',
        'targetCurrency' => 'targetCurrencyCode',
        'day'            => 'day',
    ];

    /**
     * @var ManagerRegistry
     */
    private $managerRegistry;

    /**
     * @var string
     */
    private $entityClass;

    /**
     * @var array
     */
    private $propertyMap;

    /**
     * DoctrineORMProvider constructor.
     * @param ManagerRegistry $managerRegistry
     * @param string          $entityClass
     * @param array           $propertyMap
     * @throws \InvalidArgumentException
     */
    public function __construct(
        ManagerRegistry $managerRegistry,
        string $entityClass,
        array $propertyMap = self::DEFAULT_PROPERTY_MAP
    ) {
        if (!is_a($entityClass, ExchangeRateInterface::class, true)) {
            throw new \InvalidArgumentException(sprintf('%s must implement %s', $entityClass, ExchangeRateInterface::class));
        }
        $this->managerRegistry = $managerRegistry;
        $this->entityClass = $entityClass;
        $this->propertyMap = $propertyMap;
    }

    /**
     * @inheritDoc
     */
    public function getExchangeRate(CurrencyInterface $sourceCurrency, CurrencyInterface $targetCurrency, DateTimeInterface $day = null): ExchangeRateInterface
    {
        $em = $this->managerRegistry->getManagerForClass($this->entityClass);
        $repository = $em->getRepository($this->entityClass);
        if (null !== $day) {
            $exchangeRate = $repository->findOneBy([
                $this->propertyMap['sourceCurrency'] => $sourceCurrency->getCode(),
                $this->propertyMap['targetCurrency'] => $targetCurrency->getCode(),
                $this->propertyMap['day']            => $day,
            ]);

            if (null === $exchangeRate) {
                throw new ExchangeRateNotFoundException($sourceCurrency, $targetCurrency);
            }
            return $exchangeRate;
        }

        $criteria = [
            $this->propertyMap['sourceCurrency'] => $sourceCurrency->getCode(),
            $this->propertyMap['targetCurrency'] => $targetCurrency->getCode(),
        ];

        $orderBy = [];

        if (null !== $day) {
            $criteria[$this->propertyMap['day']] = $day;
            $orderBy = [
                $this->propertyMap['day'] => 'desc'
            ];
        }


        $results = $repository->findBy($criteria, $orderBy, 1);

        foreach ($results as $exchangeRate) {
            return $exchangeRate;
        }
        throw new ExchangeRateNotFoundException($sourceCurrency, $targetCurrency);
    }
}
