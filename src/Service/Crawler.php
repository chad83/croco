<?php


namespace App\Service;

use App\Entity\Job;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\DomCrawler\Crawler as DomCrawler;
use Symfony\Component\DomCrawler\UriResolver;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use App\Entity\Page;

class Crawler
{
    private string $siteRoot;
    private array $pages;

    private \Symfony\Contracts\HttpClient\HttpClientInterface $httpClient;

    private array $filteredLinkPrefixes;
    private array $filteredLinkSuffixes;
    /**
     * @var ObjectManager
     */
    private ObjectManager $entityManager;
    /**
     * @var Job
     */
    private Job $job;

    public function __construct(ObjectManager $entityManager, Job $job, string $siteRoot, array $filteredLinkPrefixes, array $filteredLinkSuffixes)
    {
        $this->siteRoot = $this->cleanWebPath($siteRoot);

//        $this->pages[$this->siteRoot] = [];
        $this->pages = [];

        $this->httpClient = HttpClient::create();

        $this->filteredLinkPrefixes = $filteredLinkPrefixes;
        $this->filteredLinkSuffixes = $filteredLinkSuffixes;
        $this->entityManager = $entityManager;
        $this->job = $job;
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
//        $path = strtolower(trim($path));

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
//        $path = strtolower(trim($path));

        foreach($this->filteredLinkPrefixes as $prefix){
            if(str_contains($path, $prefix)){
                return true;
            }
        }

        if($path === '#' || $path === '/' || empty($path)){
            return true;
        }

        return false;
    }

    private function isFileLink(string $path) : bool
    {
        foreach($this->filteredLinkSuffixes as $suffix){
            if(str_contains($path, $suffix)){
                return true;
            }
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
        if(substr($linkedPage, 0, 5) === 'http:' || substr($linkedPage, 0, 6) === 'https:' || substr($linkedPage, 0, 4) === 'www.'){
            return $linkedPage;
        } else {
            $normalizer = new \URL\Normalizer($this->siteRoot . '/' . $linkedPage);
            return $normalizer->normalize();
        }
    }

    private function addToPages(string $linkedPage, array $linkElement)
    {
        $this->pages[$linkedPage] = [
            'href' => $linkedPage,
            'alt' => $linkElement[1],
            'text' => $linkElement[2],
            'status' => null,
        ];
    }

    private function setPageStatus(string $linkedPage, int $status)
    {
        $this->pages[$linkedPage]['status'] = $status;
    }

    private function updatePageParent(string $page, string $parent)
    {
        if(!empty($parent)) {
            $this->pages[$page]['parents'][] = $parent;
        }
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

    public function getPages(string $webPage, bool $recurse = true, string $parent = '') : array|false
    {
//        $normalizer = new \URL\Normalizer($webPage);
//        $webPage = $normalizer->normalize();

//        if(empty($parent)) {
//            $webPage = UriResolver::resolve($webPage, $this->siteRoot);
//        } else {
//            $webPage = UriResolver::resolve($webPage, $parent);
//        }

        // Push the current page to the list of pages.
        if(!$this->isCrawled($webPage)) {
            $this->pages[$webPage] = [];
        }

        // Get the status and content of the current page.
        try {
            $response = $this->httpClient->request('GET', $webPage);
            $responseStatus = $response->getStatusCode();
            $this->setPageStatus($webPage, $responseStatus);
            $this->updatePageParent($webPage, $parent);

            if($responseStatus === 200) {
                $html = $response->getContent(false);
            } else {
                return false;
            }
        } catch (TransportExceptionInterface | ClientExceptionInterface | RedirectionExceptionInterface | ServerExceptionInterface $e) {
            return false;
        }



//        $crawler = new DomCrawler($html);
//        print_r($crawler);

//        $crawler = $crawler->filter('img');

//        $crawler = $crawler
//            ->filterXpath('//img')
//            ->extract(array('src', 'alt', 'height', 'width', '_text'));

        // Create a crawler instance to check for links.
        $crawler = new DomCrawler($html);
        $crawler = $crawler
            ->filterXpath('//a')
            ->extract(array('href', 'alt', '_text'));

        // Check the generated links.
        foreach ($crawler as $domElement) {
            // Check if the page is pointing back to itself.
//            $normalizer = new \URL\Normalizer($domElement[0]);
//            $linkedPage = $normalizer->normalize();

            $linkedPage = UriResolver::resolve($domElement[0], $webPage);
//            $linkedPage = $this->buildAbsolutePath($linkedPage);
//            $linkedPage = $this->cleanWebPath($domElement[0]);

            // Skip re-saving the site-root.
//            if($linkedPage === $this->siteRoot){
//                // Tree behavior.
//            }

            // If it's not a duplicated link.
            if(!$this->isCrawled($linkedPage)) {
                // Check if this is an outside link.
                if ($this->isOutgoingLink($linkedPage)) {
                    // Todo: Create outgoing links handler.
                } // Check if this is a utility link.
                else if ($this->isUtilityLink($linkedPage)) {
                    // Utility-link behavior.
                } else if ($this->isFileLink($linkedPage)) {
                    // File-link behavior.
                } // Save the page if it hasn't been saved already.
                else {
                    $this->addToPages($linkedPage, $domElement);

                    if($recurse) {
                        $this->getPages($linkedPage, true, $webPage);
                    }
                }
            }
        }

        $this->savePages($this->pages);
        return $this->pages;
    }

    private function savePages(array $pages)
    {

        foreach($pages as $uri => $pageData){
            $page = new Page();
            $page->setJob($this->job);
            $page->setPath($uri);
            $page->setStatusCode($pageData['status']);
            $page->setTitle('Ergonomic and stylish!');

            // tell Doctrine you want to (eventually) save the Product (no queries yet)
            $this->entityManager->persist($page);
        }

        $this->entityManager->flush();
    }
}