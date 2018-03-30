<?php

namespace BenTools\Currency\Model;

interface CurrencyInterface
{

    /**
     * The currency standard code, uppercased.
     *
     * @return string
     */
    public function getCode(): string;

    /**
     * @param string|null $locale
     * @return string
     */
    public function getName(string $locale = null): string;
}
