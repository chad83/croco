<?php

namespace App\Message;

final class NewJobMessage
{
    private int $jobId;
    private array $filteredLinkPrefixes;
    private array $filteredLinkSuffixes;

    public function __construct(int $jobId, array $filteredLinkPrefixes, array $filteredLinkSuffixes)
     {
         $this->jobId = $jobId;
         $this->filteredLinkPrefixes = $filteredLinkPrefixes;
         $this->filteredLinkSuffixes = $filteredLinkSuffixes;
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
}
