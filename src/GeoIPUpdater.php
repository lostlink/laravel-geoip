<?php

namespace LostLink\GeoIP;

use Exception;
use GuzzleHttp\Client as GuzzleClient;
use Illuminate\Support\Arr;
use LostLink\GeoIP\Exceptions\InvalidCredentialsException;
use LostLink\GeoIP\Exceptions\InvalidDatabaseException;
use PharData;

class GeoIPUpdater
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
     * @param  array  $config
     */
    public function __construct(array $config, GuzzleClient $guzzle = null)
    {
        $this->config = $config;

        $this->guzzle = $guzzle ?? new GuzzleClient();
    }

    /**
     * Main update function.
     *
     * @return string|false
     */
    public function update()
    {
        if (! Arr::get($this->config, 'maxmind_database.database', false)) {
            throw new InvalidDatabaseException();
        }

        if (! Arr::get($this->config, 'maxmind_database.license_key', false)) {
            throw new InvalidCredentialsException();
        }

        return $this->updateMaxmindDatabase();
    }

    /**
     * Update function for maxmind database.
     *
     * @return string
     */
    protected function updateMaxmindDatabase()
    {
        $maxmindDatabaseUrl = collect([
            Arr::get($this->config, 'maxmind_database.download', 'https://download.maxmind.com/app/geoip_download?edition_id='),
            Arr::get($this->config, 'maxmind_database.edition', 'GeoIP2-City'),
            '&suffix=tar.gz&license_key=',
            Arr::get($this->config, 'maxmind_database.license_key'),
        ])->implode('');

        $database = Arr::get($this->config, 'maxmind_database.database', false);

        if (! file_exists($dir = pathinfo($database, PATHINFO_DIRNAME))) {
            mkdir($dir, 0777, true);
        }

        $tempDir = pathinfo($database, PATHINFO_DIRNAME);

        try {
            // Download database temp dir
            $tempFile = $tempDir . '/geoip';

            $this->guzzle->get($maxmindDatabaseUrl, ['sink' => $tempFile . '.tar.gz']);

            $p = new PharData($tempFile . '.tar.gz');
            $p->decompress();

            // Extract from the tar
            $phar = new PharData($tempFile . '.tar');
            $phar->extractTo($tempDir);

            $dir = head(glob("$tempDir/" . Arr::get($this->config, 'maxmind_database.edition', 'GeoIP2-City') . '_*'));

            @unlink($database);
            @unlink($tempFile . '.tar');

            // Save database to final location
            rename($dir . '/' . Arr::get($this->config, 'maxmind_database.edition', 'GeoIP2-City') . '.mmdb', $database);

            // Delete temp file
            @unlink($tempFile);

            array_map('unlink', glob("$dir/*.*"));
            @rmdir($dir);
        } catch (Exception $e) {
            return false;
        }

        return $database;
    }
}
