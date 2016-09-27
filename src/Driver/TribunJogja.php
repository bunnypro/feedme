<?php namespace Bunnypro\FeedMe\Driver;

use Bunnypro\FeedMe\Contract\FeedDriver;
use Carbon\Carbon;
use Symfony\Component\DomCrawler\Crawler;

class TribunJogja implements FeedDriver
{
    protected $url = 'http://jogja.tribunnews.com/populer';

    protected $sections = [];

    protected $types = [];

    protected $options = [];

    /**
     * @var Crawler
     */
    private $data;

    protected $nodeLocation = [
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
        'sections' => '#sectionpil',
        'types' => '#type'
    ];

    private $articles = [];

    public function url()
    {
        $query = http_build_query($this->options) ?: null;

        return $this->url . ($query ? '?' . $query : '');
    }

    public function setData(Crawler $data)
    {
        $this->data = $data;
    }

    public function generate()
    {
        foreach ($this->loadArticlesChildren() as $article) {
            $content = new Crawler($article);

            $this->articles[] = new \Bunnypro\FeedMe\Article([
                'number' => $this->getValue($content, $this->node('number')),
                'title' => $this->getValue($content, $this->node('title')),
                'image' => $this->getValue($content, $this->node('image')),
                'link' => $this->getValue($content, $this->node('url')),
                'description' => $this->getValue($content, $this->node('description')),
                'date' => new Carbon(@explode(',', $this->getValue($content, $this->node('date')))[1]),
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

    public function section($section)
    {
        $this->options['section'] = $section;

        return $this;
    }

    public function type($type)
    {
        $this->options['type'] = $type;

        return $this;
    }

    public function clearOption()
    {
        $this->options = [];
    }

    public function sections()
    {
        if (empty($this->sections)) {
            $this->loadDataIfNotLoaded();

            $sections = $this->data->filter($this->node('sections'));

            foreach ($sections->children() as $section) {
                $s = new Crawler($section);

                $this->sections[$s->attr('value')] = $s->text();
            }
        }

        return $this->sections;
    }

    public function types()
    {
        if (empty($this->types)) {
            $this->loadDataIfNotLoaded();

            $types = $this->data->filter($this->node('types'));

            foreach ($types->children() as $type) {
                $t = new Crawler($type);

                $this->types[$t->attr('value')] = $t->text();
            }
        }

        return $this->types;
    }

    private function loadDataIfNotLoaded()
    {
        if (! $this->data instanceof Crawler) {
            $response = file_get_contents($this->url);

            $this->data = new Crawler($response);
        }
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