<?php
declare(strict_types=1);

namespace App\Entity;

use App\Entity\User;
use App\Repository\PosteRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PosteRepository::class)]
class Poste
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private string $titre;

    #[ORM\Column(type: Types::TEXT)]
    private string $contenus;

    #[ORM\Column(length: 255)]
    private string $image;

    #[ORM\Column]
    private \DateTimeImmutable $publishedAt;

    #[ORM\Column]
    private \DateTimeImmutable $updatedAt;

    #[ORM\ManyToOne(inversedBy: 'postes')]
    #[ORM\JoinColumn(nullable: false)]
    private User $auteur;



    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitre(): string
    {
        return $this->titre;
    }

    public function setTitre(string $titre): static
    {
        $this->titre = $titre;
        return $this;
    }

    public function getContenus(): string
    {
        return $this->contenus;
    }

    public function setContenus(string $contenus): static
    {
        $this->contenus = $contenus;
        return $this;
    }

    public function setAuteur(User $auteur): static
    {
    
    $this->auteur = $auteur;
    return $this;

    }

    public function getImage(): string
    {
        return $this->image;
    }

    public function setImage(string $image): static
    {
        $this->image = $image;
        return $this;
    }

    public function getPublishedAt(): \DateTimeImmutable
    {
        return $this->publishedAt;
    }

    public function setPublishedAt(\DateTimeImmutable $publishedAt): static
    {
        $this->publishedAt = $publishedAt;
        return $this;
    }

    public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }


    public function getAuteur(): User
    {
        return $this->auteur;
    }

}
