<?php
declare(strict_types=1);

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Categorie;
use App\Repository\ArticleRepository;
use App\Repository\CategorieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

class ArticleController extends AbstractController
{
    public function index(ArticleRepository $articleRepository): Response
    {
        $articles = $articleRepository->findAll();

        return $this->render('article/index.html.twig', [
            'articles' => $articles,
        ]);
    }

    public function new(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger, CategorieRepository $categorieRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
    
        // Récupérer toutes les catégories pour le menu déroulant
        $categories = $categorieRepository->findAll();
    
        if ($request->isMethod('POST')) {
            $titre = $request->request->get('titre');
            $contenus = $request->request->get('contenus');
            $categorieId = $request->request->get('categorie'); // Récupère l'ID sélectionné
            $publishedAt = new \DateTimeImmutable();
    
            // Vérifier si la catégorie existe
            $categorie = $categorieRepository->find($categorieId);
            if (!$categorie) {
                $this->addFlash('error', 'Catégorie invalide.');
                return $this->redirectToRoute('article_new');
            }
    
            $article = new Article();
            $article->setTitre($titre)
                ->setContenus($contenus)
                ->setCategorie($categorie) // ✅ Maintenant, on enregistre un objet Categorie
                ->setPublishedAt($publishedAt)
                ->setUpdatedAt($publishedAt);
    
            /** @var UploadedFile|null $imageFile */
            $imageFile = $request->files->get('image');
    
            if ($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $imageFile->guessExtension();
    
                try {
                    $imageFile->move(
                        $this->getParameter('images_directory'),
                        $newFilename
                    );
                    $article->setImage($newFilename);
                } catch (FileException $e) {
                    $this->addFlash('error', 'Erreur lors de l’upload du fichier.');
                    return $this->redirectToRoute('article_new');
                }
            }
    
            $entityManager->persist($article);
            $entityManager->flush();
    
            $this->addFlash('success', 'Article créé avec succès !');
            return $this->redirectToRoute('article_index');
        }
    
        return $this->render('article/new.html.twig', [
            'categories' => $categories, // ✅ Passer les catégories à Twig
        ]);
    }
    

    
    public function edit(Request $request, Article $article, EntityManagerInterface $entityManager, SluggerInterface $slugger, CategorieRepository $categorieRepository): Response
{
    $this->denyAccessUnlessGranted('ROLE_ADMIN');

    // Récupérer toutes les catégories depuis le repository
    $categories = $categorieRepository->findAll();

    if ($request->isMethod('POST')) {
        $titre = $request->request->get('titre');
        $contenus = $request->request->get('contenus');
        $categorieId = $request->request->get('categorie');
        $updatedAt = new \DateTimeImmutable();

        // Vérifier si la catégorie existe
        $categorie = $categorieRepository->find($categorieId);
        if (!$categorie) {
            $this->addFlash('error', 'Catégorie invalide.');
            return $this->redirectToRoute('article_edit', ['id' => $article->getId()]);
        }

        $article->setTitre($titre)
            ->setContenus($contenus)
            ->setCategorie($categorie)
            ->setUpdatedAt($updatedAt);

        /** @var UploadedFile|null $imageFile */
        $imageFile = $request->files->get('image');

        if ($imageFile) {
            $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
            $safeFilename = $slugger->slug($originalFilename);
            $newFilename = $safeFilename . '-' . uniqid() . '.' . $imageFile->guessExtension();

            try {
                $imageFile->move(
                    $this->getParameter('images_directory'),
                    $newFilename
                );

                // Supprime l'ancienne image si elle existe
                if ($article->getImage()) {
                    $oldImagePath = $this->getParameter('images_directory') . '/' . $article->getImage();
                    if (file_exists($oldImagePath)) {
                        unlink($oldImagePath);
                    }
                }

                $article->setImage($newFilename);
            } catch (FileException $e) {
                $this->addFlash('error', 'Erreur lors de l’upload du fichier.');
            }
        }

        $entityManager->flush();

        $this->addFlash('success', 'Article mis à jour avec succès !');
        return $this->redirectToRoute('article_index');
    }

    return $this->render('article/edit.html.twig', [
        'article' => $article,
        'categories' => $categories, // Passer les catégories à Twig
    ]);
}

    public function show(Article $article): Response
    {
        return $this->render('article/show.html.twig', [
            'article' => $article,
        ]);
    }

}
