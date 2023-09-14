<?php

namespace App\Entity;

use App\Repository\LivreRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: LivreRepository::class)]
class Livre
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(["getLivres", "getAuthor"])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(["getLivres", "getAuthor"])]
    private ?string $titre = null;

    #[ORM\Column(length: 255)]
    #[Groups(["getLivres", "getAuthor"])]
    private ?string $couverture = null;

    #[ORM\ManyToOne(inversedBy: 'livres')]
    #[Groups(["getLivres"])]
    private ?Author $author = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitre(): ?string
    {
        return $this->titre;
    }

    public function setTitre(string $titre): static
    {
        $this->titre = $titre;

        return $this;
    }

    public function getCouverture(): ?string
    {
        return $this->couverture;
    }

    public function setCouverture(string $couverture): static
    {
        $this->couverture = $couverture;

        return $this;
    }

    public function getAuthor(): ?Author
    {
        return $this->author;
    }

    public function setAuthor(?Author $author): static
    {
        $this->author = $author;

        return $this;
    }
}
