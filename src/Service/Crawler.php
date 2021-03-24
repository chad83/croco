<?php


namespace App\Service;

use App\Entity\Dom;
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

use Symfony\Component\HttpClient\CachingHttpClient;
use Symfony\Component\HttpKernel\HttpCache\Store;

class Crawler
{
    private string $siteRoot;
    private array $pages;
    private array $fileLinks;

    private \Symfony\Contracts\HttpClient\HttpClientInterface $httpClient;

    private array $filteredLinkPrefixes;
    private array $filteredLinkSuffixes;
    private ObjectManager $entityManager;
    private Job $job;

    public function __construct(ObjectManager $entityManager, Job $job, array $filteredLinkPrefixes, array $filteredLinkSuffixes, string $cacheDir)
    {
        $this->siteRoot = $job->getSite();

        $this->pages = [];
        $this->fileLinks = [];

        // Cache settings setup.
        $store = new Store($cacheDir);
        $this->httpClient = HttpClient::create();
        $this->httpClient = new CachingHttpClient($this->httpClient, $store);

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
        // Filter out anchor links so they default to the page that includes them.
        if(str_contains($webPage, '#')){
            $webPage = substr($webPage, 0, strrpos($webPage, '#'));
        }

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

    /**
     * Checks if the item found is a file to be saved by comparing it to a preset list of file extensions.
     *
     * @param string $path
     * @return bool
     */
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
     * Returns the file extension from a file name or a file path.
     *
     * @param string $path
     * @return string
     */
    private function getFileExtension(string $path) : string
    {
        return substr($path, strrpos($path ,'.'));
    }

    /**
     * Builds an absolute path from a relative one.
     *
     * @param string $linkedPage
     * @return string
     */
//    private function buildAbsolutePath(string $linkedPage) : string
//    {
//        if(substr($linkedPage, 0, 5) === 'http:' || substr($linkedPage, 0, 6) === 'https:' || substr($linkedPage, 0, 4) === 'www.'){
//            return $linkedPage;
//        } else {
//            $normalizer = new \URL\Normalizer($this->siteRoot . '/' . $linkedPage);
//            return $normalizer->normalize();
//        }
//    }

    /**
     * Adds a single page data to the array of pages to be saved at the end of the crawl.
     *
     * @param string $linkedPage
     * @param array $linkElement
     * @param string $title
     */
    private function addToPages(string $linkedPage, array $linkElement, string $title = null)
    {
        $this->pages[$linkedPage] = [
            'href' => $linkedPage,
            'alt' => $linkElement[1],
            'text' => $linkElement[2],
            'title' => $title,
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

    /**
     * Main Crawling function. Runs through a page's links then recursively checks each of them.
     *
     * @param string $webPage
     * @param bool $recurse
     * @param string $parent
     * @return bool
     */
    public function getPages(string $webPage, bool $recurse = true, string $parent = '')
    {
        $webPage = $this->cleanWebPath($webPage);

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

        // Create a crawler instance to check for links.
        $crawler = new DomCrawler($html);

        $title = $crawler
            ->filterXPath('//head/title')
            ->extract(array('_text'));

        // Get the links.
        $links = $crawler
            ->filterXpath('//a')
            ->extract(array('href', 'alt', '_text'));

        // Check the generated links.
        foreach ($links as $domElement) {

            $linkedPage = UriResolver::resolve($this->cleanWebPath($domElement[0]), $webPage);

            // If this page hasn't been crawled yet (case where it's linked to from multiple pages).
            if(!$this->isCrawled($linkedPage)) {
                // Check if this is an outside link.
                if ($this->isOutgoingLink($linkedPage)) {
                    // Todo: Create outgoing links handler.
                }
                // Check if this is a utility link.
                else if ($this->isUtilityLink($linkedPage)) {
                    // Utility-link behavior.
                }
                // Check if this is a file link.
                else if ($this->isFileLink($linkedPage)) {
                    $fileName = basename($linkedPage);
                    $this->fileLinks[$fileName] = [
                        'type' => 'file',
                        'parentUrl' => $webPage,
                        'filePath' => $linkedPage,
                        'fileName' => $fileName,
                        'fileType' => $this->getFileExtension($linkedPage)
                    ];
                }
                // Save the page if it hasn't been saved already.
                else {
                    $this->addToPages($linkedPage,
                        $domElement,
                        isset($title[0]) ? $title[0] : null
                    );

                    if($recurse) {
                        $this->getPages($linkedPage, true, $webPage);
                    }
                }
            }
        }

        // Crawl through any images in the page.
        $pageImages = $crawler
            ->filterXpath('//img')
            ->extract(array('src', 'alt', 'height', 'width', '_text'));

        foreach($pageImages as $image){
            $src = trim($image[0]);
            $fileName = basename($src);

            $this->fileLinks[$fileName] = [
                'type' => 'image',
                'parentUrl' => $webPage,
                'filePath' => $src,
                'fileName' => $fileName,
                'fileType' => $this->getFileExtension($src),
            ];
        }
    }

    /**
     * Saves the site's pages.
     *
     * @param array $pages
     */
    private function savePages(array $pages)
    {
        foreach($pages as $uri => $pageData){
            $page = new Page();
            $page->setJob($this->job);
            $page->setPath($uri);
            $page->setStatusCode($pageData['status'] ?? null);
            $page->setTitle($pageData['title'] ?? '');

            $this->entityManager->persist($page);
        }

        $this->entityManager->flush();
    }

    /**
     * Save the objects found during the crawl (files and images).
     *
     * @param array $fileLinks
     */
    private function saveFileLinks(array $fileLinks)
    {
        foreach($fileLinks as $fileLink){
            $dom = new Dom();
            $dom->setJob($this->job)
                ->setType($fileLink['type'])
                ->setParentUrl($fileLink['parentUrl'])
                ->setFileName($fileLink['fileName'])
                ->setFilePath($fileLink['filePath'])
                ->setFileType($fileLink['fileType']);

            $this->entityManager->persist($dom);
        }

        $this->entityManager->flush();
    }

    /**
     * Updates the status of a job in the db.
     *
     * @param string $status
     */
    private function updateJobStatus(string $status)
    {
        // Update the job status.
        $this->job
            ->setStatus($status)
            ->setDateFinished(new \DateTime());
        $this->entityManager->persist($this->job);
        $this->entityManager->flush();
    }

    /**
     * Crawler wrapper for the messenger.
     */
    public function crawl()
    {
        // Update the job status: running.
        $this->updateJobStatus('running');

        $this->getPages($this->siteRoot, true);
        $this->savePages($this->pages);
        $this->saveFileLinks($this->fileLinks);

        // Update the job status: done.
        $this->updateJobStatus('finished');
    }
}