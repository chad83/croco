<?php

namespace App\MessageHandler;

use App\Entity\Job;
use App\Message\NewJobMessage;
use App\Service\Crawler;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

final class NewJobMessageHandler implements MessageHandlerInterface
{
    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;
    /**
     * @var EntityManagerInterface
     */
    private EntityManagerInterface $entityManager;

    public function __construct(LoggerInterface $logger, EntityManagerInterface $entityManager)
    {
        $this->logger = $logger;
        $this->entityManager = $entityManager;
    }

    public function __invoke(NewJobMessage $message)
    {
        $jobId = $message->getJobId();

        /** @var Job $job */
        $job = $this->entityManager
            ->getRepository(Job::class)
            ->find($jobId);

        $this->logger->info("The site is: " . $job->getSite());

        $crawler = new Crawler(
            $this->entityManager,
            $job,
            $message->getFilteredLinkPrefixes(),
            $message->getFilteredLinkSuffixes()
        );

        $crawler->crawl();

        $this->logger->info("Finished crawling", ["jobId" => $jobId]);
    }
}
