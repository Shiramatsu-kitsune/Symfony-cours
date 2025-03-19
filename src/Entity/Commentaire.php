<?php

namespace App\Entity;


use App\Entity\Article;
use App\Entity\User;
use App\Repository\CommentaireRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CommentaireRepository::class)]
class Commentaire
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;
    
    #[ORM\Column(type: Types::TEXT)]
    private ?string $content = null;
    #[ORM\ManyToOne(inversedBy: 'commentaires')]
    #[ORM\JoinColumn(nullable: false)]
    private User $auteur;

    #[ORM\ManyToOne(inversedBy: 'commentaires')]
    #[ORM\JoinColumn(nullable: false)]
    private Article $article;

    public function getAuteur(): User
    {
        return $this->auteur;
    }

    public function setAuteur(User $auteur): static
    {
        $this->auteur = $auteur;
        return $this;
    }

    public function getArticle(): Article
    {
        return $this->article;
    }

    public function setArticle(Article $article): static
    {
        $this->article = $article;
        return $this;
    }


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): static
    {
        $this->content = $content;

        return $this;
    }
}
