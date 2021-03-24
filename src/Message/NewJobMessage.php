<?php

namespace App\Message;

final class NewJobMessage
{
    private int $jobId;
    private array $filteredLinkPrefixes;
    private array $filteredLinkSuffixes;
    private string $crawlerCachePath;

    public function __construct(int $jobId, array $filteredLinkPrefixes, array $filteredLinkSuffixes, string $crawlerCachePath)
     {
         $this->jobId = $jobId;
         $this->filteredLinkPrefixes = $filteredLinkPrefixes;
         $this->filteredLinkSuffixes = $filteredLinkSuffixes;
         $this->crawlerCachePath = $crawlerCachePath;
     }

    /**
     * @return int
     */
    public function getJobId(): int
    {
        return $this->jobId;
    }

    /**
     * @return array
     */
    public function getFilteredLinkPrefixes(): array
    {
        return $this->filteredLinkPrefixes;
    }

    /**
     * @return array
     */
    public function getFilteredLinkSuffixes(): array
    {
        return $this->filteredLinkSuffixes;
    }

    /**
     * @return string
     */
    public function getCrawlerCachePath(): string
    {
        return $this->crawlerCachePath;
    }
}
