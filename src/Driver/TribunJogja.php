<?php namespace Bunnypro\FeedMe\Driver;

use Bunnypro\FeedMe\Contract\FeedDriver;
use Symfony\Component\DomCrawler\Crawler;

class TribunJogja implements FeedDriver
{
    private $url = 'http://jogja.tribunnews.com/populer/?section=diy&type=1d';

    /**
     * @var Crawler
     */
    private $data;

    private $nodeLocation = [
        'articles' => 'body > div.main > div.content > div.fl.w677 > div.bsh.ovh > div.p2030 > div.lsi.pt10.pb10 > ul',
        'article' => 'li > div',
        'number' => 'div.fl.f22.italic.grey.fbo.bggrey.ac',
        'title' => 'h3.fbo.f16.pt5.ln19 > a',
        'image' => [
            'node' => 'img',
            'value' => [
                'method' => 'attr',
                'param' => 'src'
            ]
        ],
        'url' => [
            'node' => 'h3.fbo.f16.pt5.ln19 > a',
            'value' => [
                'method' => 'attr',
                'param' => 'href'
            ]
        ],
        'description' => 'h4',
        'date' => 'time',
    ];

    private $articles = [];

    public function url()
    {
        return $this->url;
    }

    public function setData(Crawler $data)
    {
        $this->data = $data;
    }

    public function generate()
    {
        foreach ($this->loadArticlesChildren() as $article) {
            $content = new \Symfony\Component\DomCrawler\Crawler($article);

            $this->articles[] = new \Bunnypro\FeedMe\Article([
                'number' => stripslashes($content->filter('div.fl.f22.italic.grey.fbo.bggrey.ac')->text()),
                'title' => stripslashes($content->filter('h3.fbo.f16.pt5.ln19 > a')->text()),
                'image' => $content->filter('img')->attr('src'),
                'link' => $content->filter('h3.fbo.f16.pt5.ln19 > a')->attr('href'),
                'description' => stripslashes($content->filter('h4')->text()),
                'date' => stripslashes($content->filter('time')->text()),
            ]);
        }

        return $this;
    }

    protected function loadArticlesChildren()
    {
        return $this->data->filter($this->nodeLocation['articles'])->children();
    }

    public function articles()
    {
        return $this->articles;
    }
}