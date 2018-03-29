<?php

namespace BenTools\Currency\Model;

interface CurrencyInterface
{

    /**
     * @return string
     */
    public function getCode(): string;

    /**
     * @param string|null $locale
     * @return string
     */
    public function getName(string $locale = null): string;
}
