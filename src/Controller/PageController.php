<?php

namespace App\Controller;

use App\Entity\Page;
use App\Form\PageType;
use App\Repository\PageRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use App\Service\Crawler;


#[Route('/page')]
class PageController extends AbstractController
{

    #[Route('/generatetree', name: 'generate_tree')]
    public function generateSiteTree()
    {
        $site = 'https://www.chadiwehbe.com';

        $crawler = new Crawler('https://www.chadiwehbe.com',
            $this->getParameter('app.filtered_link_prefixes'),
            $this->getParameter('app.filtered_link_suffixes'));
        $sitePages = $crawler->getPages($site);

        echo '<pre>';
        print_r($sitePages);
        echo '</pre>';

        return new JsonResponse($sitePages);
    }

    #[Route('/', name: 'page_index', methods: ['GET'])]
    public function index(PageRepository $pageRepository): Response
    {
        return $this->render('page/index.html.twig', [
            'pages' => $pageRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'page_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $page = new Page();
        $form = $this->createForm(PageType::class, $page);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($page);
            $entityManager->flush();

            return $this->redirectToRoute('page_index');
        }

        return $this->render('page/new.html.twig', [
            'page' => $page,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'page_show', methods: ['GET'])]
    public function show(Page $page): Response
    {
        return $this->render('page/show.html.twig', [
            'page' => $page,
        ]);
    }

    #[Route('/{id}/edit', name: 'page_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Page $page): Response
    {
        $form = $this->createForm(PageType::class, $page);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('page_index');
        }

        return $this->render('page/edit.html.twig', [
            'page' => $page,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'page_delete', methods: ['POST'])]
    public function delete(Request $request, Page $page): Response
    {
        if ($this->isCsrfTokenValid('delete'.$page->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($page);
            $entityManager->flush();
        }

        return $this->redirectToRoute('page_index');
    }


}
