<?php
declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class HomeController extends AbstractController
{
    public function index(): Response
    {
        return $this->render('home/home.html.twig');
    }

    public function profile(): Response
    {
        $user = $this->getUser(); // ← C’est ici qu’on récupère l'utilisateur connecté
    
        return $this->render('home/profile.html.twig', [
            'user' => $user, // ← On envoie la variable à Twig
        ]);
    }


}