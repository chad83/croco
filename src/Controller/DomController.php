<?php

namespace App\Controller;

use App\Entity\Dom;
use App\Form\DomType;
use App\Repository\DomRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/dom')]
class DomController extends AbstractController
{
    #[Route('/', name: 'dom_index', methods: ['GET'])]
    public function index(DomRepository $domRepository): Response
    {
        return $this->render('dom/index.html.twig', [
            'doms' => $domRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'dom_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $dom = new Dom();
        $form = $this->createForm(DomType::class, $dom);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($dom);
            $entityManager->flush();

            return $this->redirectToRoute('dom_index');
        }

        return $this->render('dom/new.html.twig', [
            'dom' => $dom,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'dom_show', methods: ['GET'])]
    public function show(Dom $dom): Response
    {
        return $this->render('dom/show.html.twig', [
            'dom' => $dom,
        ]);
    }

    #[Route('/{id}/edit', name: 'dom_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Dom $dom): Response
    {
        $form = $this->createForm(DomType::class, $dom);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('dom_index');
        }

        return $this->render('dom/edit.html.twig', [
            'dom' => $dom,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'dom_delete', methods: ['POST'])]
    public function delete(Request $request, Dom $dom): Response
    {
        if ($this->isCsrfTokenValid('delete'.$dom->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($dom);
            $entityManager->flush();
        }

        return $this->redirectToRoute('dom_index');
    }
}
