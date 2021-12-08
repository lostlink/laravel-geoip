<?php

namespace LostLink\GeoIP\Drivers;

use GeoIp2\WebService\Client;
use Illuminate\Support\Arr;
use LostLink\GeoIP\Exceptions\InvalidCredentialsException;

class MaxmindApiDriver extends MaxmindDriver
{
    /**
     * Create the maxmind web client.
     *
     * @throws \LostLink\GeoIP\Exceptions\InvalidCredentialsException
     *
     * @return \GeoIp2\WebService\Client
     */
    protected function create()
    {
        $userId = Arr::get($this->config, 'user_id', false);
        $licenseKey = Arr::get($this->config, 'license_key', false);

        // check and make sure they are set
        if (! $userId || ! $licenseKey) {
            throw new InvalidCredentialsException();
        }

        return new Client((int) $userId, $licenseKey);
    }
}
