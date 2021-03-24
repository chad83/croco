<?php /** @noinspection PhpUndefinedClassInspection */

namespace App\Tests\App\Tests\Service;

use App\Service\Crawler;
use App\Entity\Job;
use Doctrine\Persistence\ObjectManager;
use PHPUnit\Framework\TestCase;

class CrawlerTest extends TestCase
{
    /**
     * @var Crawler
     */
    private Crawler $crawler;

    public function setUp(): void
    {
        $job = new Job();
        $job->setSite('www.example.com');

        $entityManager = $this->createMock(ObjectManager::class);

        $this->crawler = new Crawler(
            $entityManager,
            $job,
            [],
            [],
            ''
        );
    }

    public function testGetFileExtension()
    {
        $fileExt = $this->crawler->getFileExtension('https://www.example.com/test/file.pdf');
        $this->assertEquals('.pdf', $fileExt);
    }
    
    public function testCleanWebPath()
    {
        $url = $this->crawler->cleanWebPath('https://www.example.com/test/page1#anchor1');
        $this->assertEquals('https://www.example.com/test/page1', $url);
    }
}
