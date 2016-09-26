<?php namespace Bunnypro\FeedMe\Contract;

use Symfony\Component\DomCrawler\Crawler;

interface FeedDriver
{
    public function url();

    public function setData(Crawler $data);

    public function generate();

    public function articles();
}