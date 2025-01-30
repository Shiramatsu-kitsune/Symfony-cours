<?php


namespace App\Controller;

use App\Entity\Article;
use App\Repository\ArticleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ArticleController extends AbstractController
{
    #[Route('/articles', name: 'article_index', methods: ['GET'])]
    public function index(ArticleRepository $articleRepository): Response
    {
        $articles = $articleRepository->findAll();

        return $this->render('article/index.html.twig', [
            'articles' => $articles,
        ]);
    }

    #[Route('/articles/new', name: 'article_new', methods: ['GET', 'POST'])]
public function new(Request $request, EntityManagerInterface $entityManager): Response
{
    // Vérifier si l'utilisateur est admin
    $this->denyAccessUnlessGranted('ROLE_ADMIN');

    if ($request->isMethod('POST')) {
        $titre = $request->request->get('titre');
        $contenus = $request->request->get('contenus');
        $image = $request->request->get('image');
        $categorie = $request->request->get('categorie');
        $publishedAt = new \DateTimeImmutable();

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

    return $this->render('article/new.html.twig');
}

#[Route('/articles/{id}/edit', name: 'article_edit', methods: ['GET', 'POST'])]
public function edit(Request $request, Article $article, EntityManagerInterface $entityManager): Response
{
    // Vérifier si l'utilisateur est admin
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


#[Route('/articles/{id}/delete', name: 'article_delete', methods: ['POST'])]
public function delete(Request $request, Article $article, EntityManagerInterface $entityManager): Response
{
    // Vérifier si l'utilisateur est admin
    $this->denyAccessUnlessGranted('ROLE_ADMIN');

    if ($this->isCsrfTokenValid('delete' . $article->getId(), $request->request->get('_token'))) {
        $entityManager->remove($article);
        $entityManager->flush();
    }

    return $this->redirectToRoute('article_index');
}

}
