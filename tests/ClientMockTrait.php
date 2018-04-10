<?php

namespace BenTools\Currency\Tests;

use GuzzleHttp\Psr7\Response;
use Http\Mock\Client;

trait ClientMockTrait
{

    /**
     * @var Client
     */
    protected $client;

    protected function mockResponse(string $content)
    {
        $this->client->addResponse(new Response(200, [], $content));
    }
}