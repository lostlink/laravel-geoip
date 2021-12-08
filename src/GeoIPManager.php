<?php

namespace LostLink\GeoIP;

use GuzzleHttp\Client as GuzzleClient;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use LostLink\GeoIP\Drivers\AbstractGeoIPDriver;
use LostLink\GeoIP\Drivers\IPApiDriver;
use LostLink\GeoIP\Drivers\IpStackDriver;
use LostLink\GeoIP\Drivers\MaxmindApiDriver;
use LostLink\GeoIP\Drivers\MaxmindDatabaseDriver;
use LostLink\GeoIP\Drivers\TelizeDriver;
use LostLink\GeoIP\Exceptions\InvalidDriverException;

class GeoIPManager
{
    /**
     * @var array
     */
    protected $config;

    /**
     * @var \GuzzleHttp\Client
     */
    protected $guzzle;

    /**
     * @param array $config
     */
    public function __construct(array $config, GuzzleClient $guzzle = null)
    {
        $this->config = $config;
        $this->guzzle = $guzzle;
    }

    /**
     * Get the driver based on config.
     *
     * @return \LostLink\GeoIP\AbstractGeoIPDriver
     */
    public function getDriver($driver = null): AbstractGeoIPDriver
    {
        $driver = $driver ?? Arr::get($this->config, 'driver', '');

        $method = 'create' . ucfirst(Str::camel($driver)) . 'Driver';

        if (! method_exists($this, $method)) {
            throw new InvalidDriverException(sprintf('Driver [%s] not supported.', $driver));
        }

        return $this->{$method}(Arr::get($this->config, $driver, []));
    }

    /**
     * Get the ip stack driver.
     *
     * @return \LostLink\GeoIP\IpStackDriver
     */
    protected function createIpStackDriver(array $data): IpStackDriver
    {
        return new IpStackDriver($data, $this->guzzle);
    }

    /**
     * Get the ip-api driver.
     *
     * @return \LostLink\GeoIP\IPApiDriver
     */
    protected function createIpApiDriver(array $data): IPApiDriver
    {
        return new IPApiDriver($data, $this->guzzle);
    }

    /**
     * Get the Maxmind driver.
     *
     * @return \LostLink\GeoIP\MaxmindDriver
     */
    protected function createMaxmindDatabaseDriver(array $data): MaxmindDatabaseDriver
    {
        return new MaxmindDatabaseDriver($data, $this->guzzle);
    }

    /**
     * Get the Maxmind driver.
     *
     * @return \LostLink\GeoIP\MaxmindDriver
     */
    protected function createMaxmindApiDriver(array $data): MaxmindApiDriver
    {
        return new MaxmindApiDriver($data, $this->guzzle);
    }

    /**
     * Get the telize driver.
     *
     * @return \LostLink\GeoIP\TelizeDriver
     */
    protected function createTelizeDriver(array $data): TelizeDriver
    {
        return new TelizeDriver($data, $this->guzzle);
    }
}
