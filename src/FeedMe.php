<?php namespace Bunnypro\FeedMe;

use Bunnypro\FeedMe\Contract\FeedDriver;
use Bunnypro\FeedMe\Driver\TribunJogja;
use GuzzleHttp\Client;
use Symfony\Component\DomCrawler\Crawler;

class FeedMe
{
    public static $TRIBUN_JOGJA = TribunJogja::class;

    protected $driverName;

    /**
     * @var FeedDriver
     */
    protected $driver;

    protected $client;

    public function __construct($driverName = null)
    {
        $this->driverName = $driverName;

        $this->client = new Client();

        $this->loadDriver();
    }

    /**
     * @return FeedDriver
     */
    public function load()
    {
        $response = $this->client->get($this->driver->url())->getBody()->getContents();

        $this->driver->setData(new Crawler($response));

        return $this->driver->generate();
    }

    protected function loadDriver()
    {
        if (! $this->driver instanceof FeedDriver) {
            $this->driver = new $this->driverName;
        }
    }
}