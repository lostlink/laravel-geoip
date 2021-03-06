<?php

namespace LostLink\GeoIP\Tests;

use Exception;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Psr7\Response;
use LostLink\GeoIP\Exceptions\InvalidCredentialsException;
use LostLink\GeoIP\Exceptions\InvalidDatabaseException;
use LostLink\GeoIP\GeoIPUpdater;
use Mockery;
use Phar;
use PharData;

class GeoIPUpdaterTest extends AbstractTestCase
{
    public function test_no_database()
    {
        $this->expectException(InvalidDatabaseException::class);

        (new GeoIPUpdater([]))->update();
    }

    public function test_no_license_key()
    {
        $this->expectException(InvalidCredentialsException::class);

        $database = __DIR__ . '/data/GeoLite2-City.mmdb';
        $config = [
            'driver' => 'maxmind_database',
            'maxmind_database' => [
                'database' => $database,
            ],
        ];

        (new GeoIPUpdater($config))->update();
    }

    public function test_maxmind_updater()
    {
        $database = __DIR__ . '/data/GeoLite2-City.mmdb';
        $config = [
            'driver' => 'maxmind_database',
            'maxmind_database' => [
                'database' => $database,
                'license_key' => 'test',
                'edition' => 'GeoLite2-City',
            ],
        ];

        // create the file
        $p = new PharData(__DIR__ . '/data/test.tar');
        $p->addEmptyDir('GeoLite2-City_today');
        $p['GeoLite2-City_today/GeoLite2-City.mmdb'] = 'test';
        $p->compress(Phar::GZ);
        unlink(__DIR__ . '/data/test.tar');
        rename(__DIR__ . '/data/test.tar.gz', __DIR__ . '/data/geoip.tar.gz');

        $client = Mockery::mock(GuzzleClient::class);

        $client->shouldReceive('get')
            ->once()
            ->withSomeOfArgs('https://download.maxmind.com/app/geoip_download?edition_id=GeoLite2-City&suffix=tar.gz&license_key=test')
            ->andReturn(new Response);

        $geoipUpdater = new GeoIPUpdater($config, $client);

        $this->assertEquals($geoipUpdater->update(), $database);

        unlink($database);
        unlink(__DIR__ . '/data/geoip.tar.gz');
    }

    public function test_maxmind_updater_invalid_url()
    {
        $database = __DIR__ . '/data/GeoLite2-City.mmdb';
        $config = [
            'driver' => 'maxmind_database',
            'maxmind_database' => [
                'database' => $database,
                'download' => 'http://example.com/maxmind_database.mmdb.gz?license_key=',
                'license_key' => 'test',
                'edition' => 'maxmind_database',
            ],
        ];

        $client = Mockery::mock(GuzzleClient::class);

        $client->shouldReceive('get')
// TODO: Fix the InvalidURL Test
//            ->once()
            ->withSomeOfArgs('http://example.com/maxmind_database.mmdb.gz?license_key=test')
            ->andThrow(new Exception);

        $geoipUpdater = new GeoIPUpdater($config, $client);

        $this->assertFalse($geoipUpdater->update());
    }
}
