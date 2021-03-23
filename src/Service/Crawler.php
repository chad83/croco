<?php


namespace App\Service;

use Symfony\Component\DomCrawler\Crawler as DomCrawler;
use Symfony\Component\CssSelector\CssSelectorConverter;
use Symfony\Component\HttpClient\HttpClient;

class Crawler
{
    private string $siteRoot;
    private array $pages;

    private \Symfony\Contracts\HttpClient\HttpClientInterface $httpClient;

    private array $filteredLinkPrefixes;
    private array $filteredLinkSuffixes;

    public function __construct(string $siteRoot, array $filteredLinkPrefixes, array $filteredLinkSuffixes)
    {
        $this->siteRoot = $this->cleanWebPath($siteRoot);

//        $this->pages[$this->siteRoot] = [];
        $this->pages = [];

        $this->httpClient = HttpClient::create();

        $this->filteredLinkPrefixes = $filteredLinkPrefixes;
        $this->filteredLinkSuffixes = $filteredLinkSuffixes;
    }

    /**
     * Cleans a web path to make it uniform and comparable to other, generated paths.
     *
     * @param string $webPage
     * @return string
     */
    private function cleanWebPath(string $webPage) : string
    {
        $webPage = trim($webPage);
        $webPage = trim($webPage, '/');

        return $webPage;
    }

    /**
     * Checks if a link is outgoing (pointing outside this site's domain).
     *
     * @param string $path
     * @return bool
     */
    private function isOutgoingLink(string $path) : bool
    {
        $path = strtolower(trim($path));

        // Check if the path starts with web paths that is not an absolute link to this website.
        if((substr($path, 0, 4) === 'http' || substr($path, 0, 3) === 'www') &&
            !str_contains($path, $this->siteRoot)){
            return true;
        }

        return false;
    }

    /**
     * Checks if this is a utility link by comparing it to a set of prefixes.
     * The prefixes are defined in app.filtered_link_prefixes
     *
     * @param string $path
     * @return bool
     */
    private function isUtilityLink(string $path) : bool
    {
        $path = strtolower(trim($path));

        foreach($this->filteredLinkPrefixes as $prefix){
            if(str_contains($path, $prefix)){
                return true;
            }
        }

        foreach($this->filteredLinkSuffixes as $suffix){
            if(str_contains($path, $suffix)){
                return true;
            }
        }

        if($path === '#'){
            return true;
        }

        return false;
    }

    /**
     * Builds an absolute path from a relative one.
     *
     * @param string $linkedPage
     * @return string
     */
    private function buildAbsolutePath(string $linkedPage) : string
    {
        if(substr($linkedPage, 0, 4) === 'http' || substr($linkedPage, 0, 3) === 'www'){
            return $linkedPage;
        } else {
            return $this->siteRoot . '/' . $linkedPage;
        }
    }

    private function addToPages(string $linkedPage, array $linkElement)
    {
        $this->pages[$linkedPage] = [
            'href' => $this->buildAbsolutePath($linkedPage),
            'alt' => $linkElement[1],
            'text' => $linkElement[2]
        ];
    }

    /**
     * Checks if the page has already been saved.
     *
     * @param string $pagePath
     * @return bool
     */
    private function isCrawled(string $pagePath) : bool
    {
        if(array_key_exists($pagePath, $this->pages)){
            return true;
        }

        return false;
    }

    public function getPages(string $webPage) : array|false
    {
        $webPage = $this->cleanWebPath($webPage);


        $response = $this->httpClient->request('GET', $webPage);
        if($response->getStatusCode() === 200) {
            $html = $response->getContent();
        } else {
            return false;
        }

        $crawler = new DomCrawler($html);
//        print_r($crawler);

//        $crawler = $crawler->filter('img');

//        $crawler = $crawler
//            ->filterXpath('//img')
//            ->extract(array('src', 'alt', 'height', 'width', '_text'));

        $crawler = $crawler
            ->filterXpath('//a')
            ->extract(array('href', 'alt', '_text'));

        $currentPageElements = [];
        foreach ($crawler as $domElement) {
            // Check if the page is pointing back to itself.
            $linkedPage = $this->cleanWebPath($domElement[0]);

            // Skip re-saving the site-root.
            if($linkedPage === $this->siteRoot){
                // Tree behavior.
            }
            // Check if this is an outside link.
            else if($this->isOutgoingLink($linkedPage)){
                // Todo: Create outgoing links handler.
            }
            // Check if this is a utility link.
            else if($this->isUtilityLink($linkedPage)){
                // Utility-link behavior.
            }
            // Save the page if it hasn't been saved already.
            else if(!$this->isCrawled($linkedPage)) {
                $this->addToPages($linkedPage, $domElement);

//                $this->buildAbsolutePath($linkedPage);
//                $this->getPages($this->buildAbsolutePath($linkedPage));
            }
        }

        return $this->pages;
    }
}