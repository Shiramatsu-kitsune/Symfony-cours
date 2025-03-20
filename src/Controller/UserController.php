<?php


namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;


  
class UserController extends AbstractController
{
   
    public function index(UserRepository $userRepository): Response
    {
        return $this->render('user/index.html.twig', [
            'users' => $userRepository->findAll(),
        ]);
    }

    
    public function new(
        Request $request,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher,
        CsrfTokenManagerInterface $csrfTokenManager
    ): Response {
        if ($this->getUser()) {
            return $this->redirectToRoute('user_index');
        }
    
        $token = $csrfTokenManager->getToken('user_creation')->getValue();
        $errors = [];
    
        if ($request->isMethod('POST')) {
            $submittedToken = $request->request->get('_csrf_token');
            if (!$csrfTokenManager->isTokenValid(new CsrfToken('user_creation', $submittedToken))) {
                throw new \RuntimeException('Token CSRF invalide.');
            }
    
            $email = trim($request->request->get('email'));
            $password = $request->request->get('password');
            $pseudonyme = trim($request->request->get('pseudonyme'));
    
            // Empêche les utilisateurs de définir leur rôle manuellement
            $roles = ['ROLE_USER'];  // Par défaut
    
            // Si l'utilisateur est connecté ET admin → il peut définir les rôles
            $currentUser = $this->getUser();
            if ($currentUser && in_array('ROLE_ADMIN', $currentUser->getRoles(), true)) {
                $roles = (array) $request->request->get('roles', ['ROLE_USER']);
            }
    
            // Validation CNIL
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'Adresse email invalide.';
            }
    
            if (empty($pseudonyme)) {
                $errors[] = 'Le pseudonyme est obligatoire.';
            }
    
            if (strlen($password) < 12) {
                $errors[] = 'Le mot de passe doit contenir au moins 12 caractères.';
            }
            if (!preg_match('/[A-Z]/', $password)) {
                $errors[] = 'Le mot de passe doit contenir au moins une lettre majuscule.';
            }
            if (!preg_match('/[a-z]/', $password)) {
                $errors[] = 'Le mot de passe doit contenir au moins une lettre minuscule.';
            }
            if (!preg_match('/\d/', $password)) {
                $errors[] = 'Le mot de passe doit contenir au moins un chiffre.';
            }
            if (!preg_match('/[\W_]/', $password)) {
                $errors[] = 'Le mot de passe doit contenir au moins un caractère spécial.';
            }
    
            if (count($errors) === 0) {
                $user = new User();
                $user->setEmail($email);
                $user->setPassword($passwordHasher->hashPassword($user, $password));
                $user->setPseudonyme($pseudonyme);
                $user->setUsername($pseudonyme);
                $user->setRoles($roles);
    
                $entityManager->persist($user);
                $entityManager->flush();
    
                $this->addFlash('success', 'Utilisateur créé avec succès !');
                return $this->redirectToRoute('app_login');
            }
    
            return $this->render('user/new.html.twig', [
                'errors' => $errors,
                'email' => $email,
                'pseudonyme' => $pseudonyme,
                'roles' => $roles,
                'csrf_token' => $token,
            ]);
        }
    
        return $this->render('user/new.html.twig', [
            'csrf_token' => $token,
        ]);
    }
    
    


    
    public function edit(
        Request $request,
        User $user,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher
    ): Response {
        if ($request->isMethod('POST')) {
            $email = trim($request->request->get('email'));
            $password = $request->request->get('password');
    
            $user->setEmail($email);
    
            if (!empty($password)) {
                $user->setPassword($passwordHasher->hashPassword($user, $password));
            }
    
            // Rôle éditable seulement si admin
            $currentUser = $this->getUser();
            if ($currentUser && in_array('ROLE_ADMIN', $currentUser->getRoles(), true)) {
                $roles = (array) $request->request->get('roles', []);
                $user->setRoles($roles);
            }
    
            $entityManager->flush();
    
            $this->addFlash('success', 'Utilisateur mis à jour avec succès.');
            return $this->redirectToRoute('user_index');
        }
    
        return $this->render('user/edit.html.twig', [
            'user' => $user,
        ]);
    }
    

    
    public function delete(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        $currentUser = $this->getUser();
    
        // Protection : Seul admin OU l’utilisateur lui-même peut supprimer le compte
        if (
            !$currentUser ||
            (!$this->isGranted('ROLE_ADMIN') && $currentUser !== $user)
        ) {
            throw $this->createAccessDeniedException('Accès refusé.');
        }
    
        // Vérification CSRF
        if ($this->isCsrfTokenValid('delete' . $user->getId(), $request->request->get('_token'))) {
            $entityManager->remove($user);
            $entityManager->flush();
    
            // Si l’utilisateur supprime lui-même son compte → déconnexion après suppression
            if ($currentUser === $user) {
                return $this->redirectToRoute('app_logout');
            }
    
            $this->addFlash('success', 'Utilisateur supprimé.');
        }
    
        return $this->redirectToRoute('user_index');
    }
    public function changePassword(Request $request, UserPasswordHasherInterface $passwordHasher, int $id): RedirectResponse
{
    $user = $this->getDoctrine()->getRepository(User::class)->find($id);

    if (!$user || $user !== $this->getUser()) {
        throw $this->createAccessDeniedException('Accès refusé.');
    }

    $submittedToken = $request->request->get('_csrf_token');
    if (!$this->isCsrfTokenValid('change_password' . $user->getId(), $submittedToken)) {
        throw $this->createAccessDeniedException('Token CSRF invalide.');
    }

    $newPassword = $request->request->get('password');

    if ($newPassword) {
        $hashedPassword = $passwordHasher->hashPassword($user, $newPassword);
        $user->setPassword($hashedPassword);

        $em = $this->getDoctrine()->getManager();
        $em->flush();

        $this->addFlash('success', 'Mot de passe mis à jour avec succès.');
    } else {
        $this->addFlash('info', 'Aucun mot de passe saisi, modification ignorée.');
    }

    return $this->redirectToRoute('profile');
}

}
