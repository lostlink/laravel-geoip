<?php

namespace LostLink\GeoIP\Tests;

use BadMethodCallException;
use LostLink\GeoIP\Exceptions\InvalidDriverException;
use LostLink\GeoIP\GeoIP;

class GeoIPTest extends AbstractTestCase
{
    public function test_invalid_driver_exception()
    {
        $this->expectException(InvalidDriverException::class);

        $geoip = new GeoIP([]);
    }

    public function test_bad_method_call_exception()
    {
        $this->expectException(BadMethodCallException::class);

        $geoip = new GeoIP();

        $geoip->setNothing();
    }
}
