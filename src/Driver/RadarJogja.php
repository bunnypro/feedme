<?php namespace Bunnypro\FeedMe\Driver;

use Bunnypro\FeedMe\Contract\FeedDriver;
use Carbon\Carbon;
use Symfony\Component\DomCrawler\Crawler;

class RadarJogja implements FeedDriver
{
    private $url = 'http://www.radarjogja.co.id/';

    private $articles = [];

    private $data;

    private $nodeLocation = [
        'articles' => 'div.popular-posts > ul',
        'title' => '.wpp-post-title',
        'image' => [
            'node' => '.wpp-thumbnail',
            'value' => [
                'method' => 'attr',
                'param' => 'src'
            ]
        ],
        'url' => [
            'node' => '.wpp-post-title',
            'value' => [
                'method' => 'attr',
                'param' => 'href'
            ]
        ],
        'date' => '.wpp-date'
    ];

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
                'number' => null,
                'title' => $this->getValue($content, $this->node('title')),
                'image' => $this->getValue($content, $this->node('image')),
                'link' => $this->getValue($content, $this->node('url')),
                'description' => null,
                'date' => new Carbon(substr($this->getValue($content, $this->node('date')), 10)),
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

    protected function getValue($content, $node)
    {
        if (! is_array($node)) {
            return $content->filter($node)->text();
        }

        return $content->filter($node['node'])->$node['value']['method']($node['value']['param']);
    }

    protected function node($names)
    {
        $node = $this->nodeLocation;

        foreach (explode('.', $names) as $name) {
            if (! array_key_exists($name, $node)) {
                return;
            }

            $node = $node[$name];
        }

        return $node;
    }
}