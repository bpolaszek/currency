<?php

namespace BenTools\Currency\Model;

use Symfony\Component\Intl\Intl;

final class Currency implements CurrencyInterface
{
    /**
     * @var string
     */
    private $code;

    /**
     * Currency constructor.
     * @param string $code
     */
    public function __construct(string $code)
    {
        $this->code = strtoupper($code);
    }

    /**
     * @inheritDoc
     */
    public function getCode(): string
    {
        return $this->code;
    }

    /**
     * @inheritDoc
     */
    public function getName(string $locale = null): string
    {
        if (class_exists('Symfony\Component\Intl\Intl')) {
            return Intl::getCurrencyBundle()->getCurrencyName($this->getCode(), $locale) ?? $this->code;
        }
        return $this->code;
    }
}
