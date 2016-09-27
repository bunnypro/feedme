<?php namespace Bunnypro\FeedMe;

use Bunnypro\FeedMe\Contract\FeedDriver;
use Symfony\Component\DomCrawler\Crawler;

class FeedMe
{
    protected $driver;

    public function __construct(FeedDriver $driver)
    {
        $this->driver = $driver;
    }

    public function load()
    {
        $response = file_get_contents($this->driver->url());

        $this->driver->setData(new Crawler($response));

        $this->driver->generate();

        return $this;
    }

    public function articles()
    {
        return $this->driver->articles();
    }
}