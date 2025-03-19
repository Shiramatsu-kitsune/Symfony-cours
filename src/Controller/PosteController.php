<?php
declare(strict_types=1);

namespace App\Controller;

use App\Entity\Poste;
use App\Repository\PosteRepository;
use App\Repository\CategorieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;


class PosteController extends AbstractController
{
    public function index(PosteRepository $posteRepository): Response
    {
        return $this->render('poste/index.html.twig', [
            'postes' => $posteRepository->findAll(),
        ]);
    }

    public function new(
        Request $request,
        EntityManagerInterface $entityManager,
        SluggerInterface $slugger
    ): Response {
        if ($request->isMethod('POST')) {
            $titre = $request->request->get('titre');
            $contenus = $request->request->get('contenus');
            $publishedAt = new \DateTimeImmutable();
    
            $poste = new Poste();
            $poste->setTitre($titre)
                ->setContenus($contenus)
                ->setPublishedAt($publishedAt)
                ->setUpdatedAt($publishedAt);
    
            // Gérer l'auteur → user connecté
            $poste->setAuteur($this->getUser());
    
            $imageFile = $request->files->get('image');
            if ($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $imageFile->guessExtension();
    
                $imageFile->move($this->getParameter('images_directory'), $newFilename);
                $poste->setImage($newFilename);
            }
    
            $entityManager->persist($poste);
            $entityManager->flush();
    
            $this->addFlash('success', 'Poste créé avec succès.');
            return $this->redirectToRoute('poste_index');
        }
    
        return $this->render('poste/new.html.twig', [
            'poste' => new Poste(), // pour éviter les erreurs twig
        ]);
    }
    

    
    public function edit(
        Request $request,
        Poste $poste,
        EntityManagerInterface $entityManager,
        SluggerInterface $slugger
    ): Response {
        if ($request->isMethod('POST')) {
            $poste->setTitre($request->request->get('titre'));
            $poste->setContenus($request->request->get('contenus'));
            $poste->setUpdatedAt(new \DateTimeImmutable());

            $imageFile = $request->files->get('image');
            if ($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $imageFile->guessExtension();

                $imageFile->move($this->getParameter('images_directory'), $newFilename);
                $poste->setImage($newFilename);
            }

            $entityManager->flush();
            $this->addFlash('success', 'Poste mis à jour avec succès.');
            return $this->redirectToRoute('poste_index');
        }

        return $this->render('poste/edit.html.twig', [
            'poste' => $poste,
            'categories' => $categorieRepository->findAll(),
        ]);
    }

    public function delete(Request $request, Poste $poste, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
    
        if (!$this->isGranted('ROLE_ADMIN') && $poste->getAuteur() !== $user) {
            throw $this->createAccessDeniedException('Vous ne pouvez supprimer que vos propres postes.');
        }
    
        if ($this->isCsrfTokenValid('delete' . $poste->getId(), $request->request->get('_token'))) {
            $entityManager->remove($poste);
            $entityManager->flush();
            $this->addFlash('success', 'Poste supprimé.');
        }
    
        return $this->redirectToRoute('poste_index');
}
}
