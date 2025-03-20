<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Commentaire;
use App\Repository\CommentaireRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


class CommentaireController extends AbstractController
{
    public function index(CommentaireRepository $repo): Response
    {
        return $this->render('commentaire/index.html.twig', [
            'commentaires' => $repo->findAll(),
        ]);
    }

    public function new(Request $request, EntityManagerInterface $em): Response
    {
        if ($request->isMethod('POST')) {
            $contenu = $request->request->get('contenu');

            $commentaire = new Commentaire();
            $commentaire->setContenu($contenu);
            $commentaire->setCreatedAt(new \DateTimeImmutable());
            $commentaire->setAuteur($this->getUser());

            $em->persist($commentaire);
            $em->flush();

            $this->addFlash('success', 'Commentaire ajouté.');
            return $this->redirectToRoute('commentaire_index');
        }

        return $this->render('commentaire/new.html.twig');
    }

    public function show(Commentaire $commentaire): Response
    {
        return $this->render('commentaire/show.html.twig', [
            'commentaire' => $commentaire,
        ]);
    }

    public function edit(Request $request, Commentaire $commentaire, EntityManagerInterface $em): Response
    {
        if ($commentaire->getAuteur() !== $this->getUser() && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException('Tu ne peux modifier que tes commentaires.');
        }

        if ($request->isMethod('POST')) {
            $commentaire->setContenu($request->request->get('contenu'));
            $em->flush();

            $this->addFlash('success', 'Commentaire modifié.');
            return $this->redirectToRoute('commentaire_index');
        }

        return $this->render('commentaire/edit.html.twig', [
            'commentaire' => $commentaire,
        ]);
    }

    public function delete(Request $request, Commentaire $commentaire, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();

        if (!$this->isGranted('ROLE_ADMIN') && $commentaire->getAuteur() !== $user) {
            throw $this->createAccessDeniedException('Tu ne peux supprimer que tes propres commentaires.');
        }

        if ($this->isCsrfTokenValid('delete' . $commentaire->getId(), $request->request->get('_token'))) {
            $em->remove($commentaire);
            $em->flush();

            $this->addFlash('success', 'Commentaire supprimé.');
        }

        return $this->redirectToRoute('commentaire_index');
    }

        public function list(EntityManagerInterface $em): JsonResponse
        {
            $commentaires = $em->getRepository(Commentaire::class)->findAll();
            return $this->json($commentaires, 200, [], ['groups' => 'comment:read']);
        }
    
        public function create(Request $request, EntityManagerInterface $em): JsonResponse
        {
            $this->denyAccessUnlessGranted('ROLE_ADMIN');
    
            $data = json_decode($request->getContent(), true);
            $commentaire = new Commentaire();
            $commentaire->setContenu($data['contenu']);
            $commentaire->setCreatedAt(new \DateTime());
    
            $post = $em->getRepository(Post::class)->find($data['post_id']);
            $commentaire->setPost($post);
    
            $em->persist($commentaire);
            $em->flush();
    
            return $this->json(['message' => 'Commentaire ajouté avec succès'], 201);
        }

}

