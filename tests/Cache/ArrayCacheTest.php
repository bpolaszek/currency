<?php

namespace BenTools\Currency\Tests\Cache;

use BenTools\Currency\Cache\ArrayCache;
use PHPUnit\Framework\TestCase;

class ArrayCacheTest extends TestCase
{

    public function testGet()
    {
        $cache = new ArrayCache();
        $cache->set('foo', 'bar');
        $this->assertEquals('bar', $cache->get('foo'));
        $this->assertEquals(null, $cache->get('baz'));
    }

    public function testDelete()
    {
        $cache = new ArrayCache();
        $cache->set('foo', 'bar');
        $cache->delete('foo');
        $this->assertEquals(null, $cache->get('foo'));
    }

    public function testClear()
    {
        $cache = new ArrayCache();
        $cache->set('foo', 'bar');
        $cache->clear();
        $this->assertEquals(null, $cache->get('foo'));
    }

    public function testGetMultiple()
    {
        $cache = new ArrayCache();
        $values = [
            'foo' => 'bar',
            'baz' => 'bat'
        ];
        $cache->setMultiple($values);
        $this->assertEquals($values, iterable_to_array($cache->getMultiple(array_keys($values))));
    }

    public function testDeleteMultiple()
    {
        $cache = new ArrayCache();
        $values = [
            'foo' => 'bar',
            'baz' => 'bat',
            'bar' => 'foo',
        ];
        $cache->setMultiple($values);
        $cache->deleteMultiple(['foo', 'bar']);
        $this->assertEquals([
            'foo' => null,
            'baz' => 'bat',
            'bar' => null,
        ], iterable_to_array($cache->getMultiple(array_keys($values))));
    }

    public function testHas()
    {
        $cache = new ArrayCache();
        $cache->set('foo', 'bar');
        $this->assertTrue($cache->has('foo'));
        $this->assertFalse($cache->has('baz'));
    }

    public function testDefaultTtl()
    {
        $cache = new ArrayCache(1);
        $cache->set('foo', 'bar');
        $this->assertTrue($cache->has('foo'));
        $this->assertEquals('bar', $cache->get('foo'));
        sleep(1);
        $this->assertFalse($cache->has('foo'));
        $this->assertNull($cache->get('foo'));
    }

    public function testTtlOverride()
    {
        $cache = new ArrayCache(1);
        $cache->set('foo', 'bar', 2);
        $this->assertTrue($cache->has('foo'));
        $this->assertEquals('bar', $cache->get('foo'));
        sleep(1);
        $this->assertTrue($cache->has('foo'));
        $this->assertEquals('bar', $cache->get('foo'));
        sleep(1);
        $this->assertFalse($cache->has('foo'));
        $this->assertNull($cache->get('foo'));
    }
}
