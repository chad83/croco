<?php

namespace App\Controller;

use App\Entity\Job;
use App\Form\JobType;
use App\Repository\JobRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/job')]
class JobController extends AbstractController
{

    /**
     * Fetches a job and its objects.
     *
     * @param $id
     * @param JobRepository $jobRepository
     */
    public function getJobResults(int $id, jobRepository $jobRepository): JsonResponse
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


//    #[Route('/run', name: 'api_job_new', methods: ['POST'])]
//    public function newJob(): JsonResponse
//    {
//        $request = Request::createFromGlobals();
//        $test = $request->toArray();
//        return new JsonResponse([$test['site']]);
//    }

    #[Route('/', name: 'job_index', methods: ['GET'])]
    public function index(JobRepository $jobRepository): Response
    {
        return $this->render('job/index.html.twig', [
            'jobs' => $jobRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'job_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $job = new Job();
        $form = $this->createForm(JobType::class, $job);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($job);
            $entityManager->flush();

            return $this->redirectToRoute('job_index');
        }

        return $this->render('job/new.html.twig', [
            'job' => $job,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'job_show', methods: ['GET'])]
    public function show(Job $job): Response
    {
        return $this->render('job/show.html.twig', [
            'job' => $job,
        ]);
    }

    #[Route('/{id}/edit', name: 'job_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Job $job): Response
    {
        $form = $this->createForm(JobType::class, $job);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('job_index');
        }

        return $this->render('job/edit.html.twig', [
            'job' => $job,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'job_delete', methods: ['POST'])]
    public function delete(Request $request, Job $job): Response
    {
        if ($this->isCsrfTokenValid('delete'.$job->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($job);
            $entityManager->flush();
        }

        return $this->redirectToRoute('job_index');
    }
}
