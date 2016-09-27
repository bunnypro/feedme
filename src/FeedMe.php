<?php namespace Bunnypro\FeedMe;

use Bunnypro\FeedMe\Contract\FeedDriver;
use Symfony\Component\DomCrawler\Crawler;

class FeedMe
{
    /**
     * @var FeedDriver
     */
    protected $driver;

    public function __construct($driver)
    {
        $this->driver = $driver;
    }

    /**
     * @return FeedDriver
     */
    public function load()
    {
        $response = file_get_contents($this->driver->url());

        $this->driver->setData(new Crawler($response));

        return $this->driver->generate();
    }
}