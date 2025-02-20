<?php


namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;


  
class UserController extends AbstractController
{
   
    public function index(UserRepository $userRepository): Response
    {
        return $this->render('user/index.html.twig', [
            'users' => $userRepository->findAll(),
        ]);
    }

     
    public function new(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response
    {
        
        if ($request->isMethod('POST')) {
            dump('Formulaire soumis');
            
            $email = $request->request->get('email');
            $password = $request->request->get('password');
            $pseudonyme = $request->request->get('pseudonyme'); 
            $roles = (array) $request->request->all('roles');
        
            dump($email, $password, $pseudonyme, $roles);
        
            if (!$pseudonyme) {
                throw new \Exception("Le pseudonyme est obligatoire !");
            }
        
            $user = new User();
            $user->setEmail($email);
            $user->setPassword($passwordHasher->hashPassword($user, $password));
            $user->setPseudonyme($pseudonyme);
            $user->setUsername($pseudonyme);
            $user->setRoles($roles);
        
            dump($user);
        
            $entityManager->persist($user);
            $entityManager->flush();
        
            dump('Redirection vers login');
            exit;
            
            return $this->redirectToRoute('app_login');
        }
        
        return $this->render('user/new.html.twig');
        
    }        
    
    public function edit(Request $request, User $user, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response
    {
        if ($request->isMethod('POST')) {
            $email = $request->request->get('email');
            $password = $request->request->get('password');
            $roles = (array) $request->request->get('roles', []); 
            
            $user->setEmail($email);
            if (!empty($password)) {
                $user->setPassword($passwordHasher->hashPassword($user, $password));
            }
            $user->setRoles($roles); 
            $entityManager->flush();

            return $this->redirectToRoute('user_index');
        }

        return $this->render('user/edit.html.twig', [
            'user' => $user,
        ]);
    }

    
    public function delete(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->request->get('_token'))) {
            $entityManager->remove($user);
            $entityManager->flush();
        }

        return $this->redirectToRoute('user_index');
    }
}
