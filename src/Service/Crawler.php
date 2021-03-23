<?php


namespace App\Service;

use Symfony\Component\DomCrawler\Crawler as DomCrawler;
use Symfony\Component\CssSelector\CssSelectorConverter;

class Crawler
{

    public function getPages(string $webPage): array
    {
        $html = file_get_contents($webPage);

        $crawler = new DomCrawler($html);
//        print_r($crawler);

//        $crawler = $crawler->filter('img');

//        $crawler = $crawler
//            ->filterXpath('//img')
//            ->extract(array('src', 'alt', 'height', 'width', '_text'));

        $crawler = $crawler
            ->filterXpath('//a')
            ->extract(array('href', 'alt', '_text'));

        $elements = [];
        foreach ($crawler as $domElement) {
            $elements[] = $domElement;
        }

        return $elements;
    }
}