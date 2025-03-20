<?php


namespace App\Controller;

use App\Entity\Article;
use App\Repository\ArticleRepository;
use App\Repository\CategorieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ArticleController extends AbstractController
{
    
    public function index(ArticleRepository $articleRepository): Response
    {
        $articles = $articleRepository->findAll();

        return $this->render('article/index.html.twig', [
            'articles' => $articles,
        ]);
    }

   
    public function new(
        Request $request,
        EntityManagerInterface $entityManager,
        CategorieRepository $categorieRepository
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
    
        $categories = $categorieRepository->findAll(); // ← Récupération BDD
    
        if ($request->isMethod('POST')) {
            $titre = $request->request->get('titre');
            $contenus = $request->request->get('contenus');
            $image = $request->request->get('image');
            $categorieNom = $request->request->get('categorie');
            $publishedAt = new \DateTimeImmutable();
    
            // Cherche l’objet Categorie
            $categorie = $categorieRepository->findOneBy(['nom' => $categorieNom]);
    
            if (!$categorie) {
                throw new \Exception('Catégorie introuvable.');
            }
    
            $article = new Article();
            $article->setTitre($titre)
                ->setContenus($contenus)
                ->setImage($image)
                ->setCategorie($categorie)
                ->setPublishedAt($publishedAt)
                ->setUpdatedAt($publishedAt);
    
            $entityManager->persist($article);
            $entityManager->flush();
    
            return $this->redirectToRoute('article_index');
        }
    
        return $this->render('article/new.html.twig', [
            'categories' => $categories, // ← Injection dans Twig
        ]);
    }
    


public function edit(Request $request, Article $article, EntityManagerInterface $entityManager): Response
{

    $this->denyAccessUnlessGranted('ROLE_ADMIN');

    if ($request->isMethod('POST')) {
        $titre = $request->request->get('titre');
        $contenus = $request->request->get('contenus');
        $image = $request->request->get('image');
        $categorie = $request->request->get('categorie');
        $updatedAt = new \DateTimeImmutable();

        $article->setTitre($titre)
            ->setContenus($contenus)
            ->setImage($image)
            ->setCategorie($categorie)
            ->setUpdatedAt($updatedAt);

        $entityManager->flush();

        return $this->redirectToRoute('article_index');
    }

    return $this->render('article/edit.html.twig', [
        'article' => $article,
    ]);
}



public function delete(Request $request, Article $article, EntityManagerInterface $entityManager): Response
{
    $this->denyAccessUnlessGranted('ROLE_ADMIN');

    if ($this->isCsrfTokenValid('delete' . $article->getId(), $request->request->get('_token'))) {
        $entityManager->remove($article);
        $entityManager->flush();
    }

    return $this->redirectToRoute('article_index');
}


public function byCategorie(string $nom, ArticleRepository $articleRepository): Response
{
    $articles = $articleRepository->findBy(['categorie' => $nom]);

    return $this->render('article/index.html.twig', [
        'articles' => $articles,
        'categorie' => $nom,
    ]);
}


}