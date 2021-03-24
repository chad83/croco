<?php

namespace App\Controller;

use App\Entity\Job;
use App\Message\NewJobMessage;
use App\Repository\JobRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/')]
class JobController extends AbstractController
{

    /**
     * Adds a new job to the queue.
     *
     * @param MessageBusInterface $bus
     * @param Request $request
     * @return JsonResponse
     */
    #[Route('/run', name: 'api_job_run', methods: ['POST'])]
    public function runJob(MessageBusInterface $bus, Request $request): JsonResponse
    {
        $jsonRequest = $request->getContent();
        $args = json_decode($jsonRequest, true);

        if(!isset($args['site']) || empty($args['site'])){
            return new JsonResponse(['message' => 'Site can\'t be empty'], 400);
        }

        $forceCrawl = false;
        if(isset($args['force_crawl']) && $args['force_crawl'] === true){
            $forceCrawl = true;
        }

        $site = $args['site'];

        $normalizer = new \URL\Normalizer(trim($site));
        $site = $normalizer->normalize();

        // Save the job description to the db.
        $entityManager = $this->getDoctrine()->getManager();
        $job = new Job();
        $job->setSite($site)
            ->setDateStarted(new \DateTime())
            ->setStatus('pending')
            ->setShouldForceCrawl($forceCrawl);
        $entityManager->persist($job);
        $entityManager->flush();

        // Dispatch a message.
        $bus->dispatch(
            new NewJobMessage(
                $job->getId(),
                $this->getParameter('app.filtered_link_prefixes'),
                $this->getParameter('app.filtered_link_suffixes'),
                $this->getParameter('kernel.project_dir') . $this->getParameter('app.crawler_cache_path')
            )
        );

        return new JsonResponse(["jobId" => $job->getId()]);
    }

    /**
     * Fetches a job and its objects.
     *
     * @param $id
     * @param JobRepository $jobRepository
     * @return JsonResponse
     */
    #[Route('/{id}/results', name: 'api_job_results', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function getJobResults(int $id, JobRepository $jobRepository): JsonResponse
    {
        $job = $jobRepository
            ->find($id);

        // Check if the job exists.
        if (!$job) {
            throw $this->createNotFoundException(
                'No jobs were found for id ' . $id
            );
        }

        // todo: create a response for an in-progress job.

        // Create a response with the job description.
        $responseArray = [
            'jobId' => $id,
            'site' => $job->getSite(),
            'status' => $job->getStatus(),
            'dateStarted' => $job->getDateStarted(),
            'dateFinished' => $job->getDateFinished(),
            'objects' => []
        ];

        // Add the objects found for that job.
        $jobObjects = $job->getDoms();
        foreach ($jobObjects as $jobObject){
            $responseArray['objects'][] = [
                'fileName' => $jobObject->getFileName(),
                'type' => $jobObject->getType(),
                'fileType' => $jobObject->getFileType(),
                'filePath' => $jobObject->getFilePath(),
                'parentUrl' => $jobObject->getParentUrl(),
                'fileSize' => $jobObject->getFileSize(),
                'imagerWidth' => $jobObject->getWidth(),
                'imageHeight' => $jobObject->getHeight()
            ];
        }

        return new JsonResponse($responseArray);
    }

    /**
     * Gets a job status.
     *
     * @param int $id
     * @param JobRepository $jobRepository
     * @return JsonResponse
     */
    #[Route('/{id}/status', name: 'api_job_status', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function getJobStatus(int $id, JobRepository $jobRepository): JsonResponse
    {
        $job = $jobRepository
            ->find($id);

        // Check if the job exists.
        if (!$job) {
            throw $this->createNotFoundException(
                'No jobs were found for id ' . $id
            );
        }

        return new JsonResponse(['status' => $job->getStatus()]);
    }

}
