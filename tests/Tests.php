<?php

namespace BenTools\Currency\Tests;

class Tests
{

    /**
     * @return string
     */
    public static function dir(): string
    {
        return __DIR__;
    }

    public static function loadFixtureFile(string $filename)
    {
        return file_get_contents(sprintf('%s/fixtures/%s', self::dir(), $filename));
    }

}