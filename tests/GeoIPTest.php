<?php

namespace LostLink\GeoIP\Tests;

use BadMethodCallException;
use LostLink\GeoIP\GeoIP;
use LostLink\GeoIP\Exceptions\InvalidDriverException;

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
