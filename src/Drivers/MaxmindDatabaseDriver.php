<?php

namespace LostLink\GeoIP\Drivers;

use GeoIp2\Database\Reader;
use Illuminate\Support\Arr;
use LostLink\GeoIP\Exceptions\InvalidDatabaseException;
use LostLink\GeoIP\Exceptions\InvalidCredentialsException;
use MaxMind\Db\Reader\InvalidDatabaseException as MaxMindInvalidDatabaseException;

class MaxmindDatabaseDriver extends MaxmindDriver
{
    /**
     * Create the maxmind database reader.
     *
     * @throws \LostLink\GeoIP\Exceptions\InvalidCredentialsException
     *
     * @return \GeoIp2\Database\Reader
     */
    protected function create()
    {
        $database = Arr::get($this->config, 'database', false);

        // check if file exists first
        if (! $database || ! file_exists($database)) {
            throw new InvalidCredentialsException();
        }

        // catch maxmind exception and throw geoip exception
        try {
            return new Reader($database);
        } catch (MaxMindInvalidDatabaseException $e) {
            throw new InvalidDatabaseException($e->getMessage(), $e->getCode(), $e);
        }
    }
}
