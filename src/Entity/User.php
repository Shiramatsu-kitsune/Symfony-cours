<?php
declare(strict_types=1);

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use App\Entity\Article;
use App\Entity\Poste;
use App\Entity\Commentaire;
use App\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $email = null;

    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column(type: 'json')]
    private array $roles = [];

    #[ORM\Column(length: 255)]
    private ?string $username = null;

    #[ORM\Column(length: 255)]
    private ?string $pseudonyme = null;

    #[ORM\OneToMany(mappedBy: 'auteur', targetEntity: Article::class, cascade: ['remove'])]
    private Collection $articles;

    #[ORM\OneToMany(mappedBy: 'auteur', targetEntity: Poste::class, cascade: ['remove'])]
    private Collection $postes;

    #[ORM\OneToMany(mappedBy: 'auteur', targetEntity: Commentaire::class, cascade: ['remove'])]
    private Collection $commentaires;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    public function getRoles(): array
    {
        $roles = $this->roles;
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): static
    {
        $this->username = $username;

        return $this;
    }

    public function getPseudonyme(): ?string
    {
        return $this->pseudonyme;
    }

    public function setPseudonyme(string $pseudonyme): static
    {
        $this->pseudonyme = $pseudonyme;

        return $this;
    }

    public function eraseCredentials(): void
    {
        
    }

    public function __construct()
    {
    
    $this->articles = new ArrayCollection();
    $this->postes = new ArrayCollection();
    $this->commentaires = new ArrayCollection();
    
    }


    public function getUserIdentifier(): string
    {
        return $this->email;
    }

    public function getArticles(): Collection 
    { 
        return $this->articles;
    }
   
    public function getPostes(): Collection 
    { 
        return $this->postes; 
    }
    
    public function getCommentaires(): Collection 
    { 
        return $this->commentaires; 
    }

}
