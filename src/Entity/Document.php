<?php

namespace App\Entity;

use App\Repository\DocumentRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: DocumentRepository::class)]
class Document
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(["getLivres"])]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(["getLivres"])]
    private ?string $path_document = null;

    #[ORM\OneToMany(mappedBy: 'document', targetEntity: Livre::class)]
    private Collection $livre;

    public function __construct()
    {
        $this->livre = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPathDocument(): ?string
    {
        return $this->path_document;
    }

    public function setPathDocument(?string $path_document): static
    {
        $this->path_document = $path_document;

        return $this;
    }

    /**
     * @return Collection<int, Livre>
     */
    public function getLivre(): Collection
    {
        return $this->livre;
    }

    public function addLivre(Livre $livre): static
    {
        if (!$this->livre->contains($livre)) {
            $this->livre->add($livre);
            $livre->setDocument($this);
        }

        return $this;
    }

    public function removeLivre(Livre $livre): static
    {
        if ($this->livre->removeElement($livre)) {
            // set the owning side to null (unless already changed)
            if ($livre->getDocument() === $this) {
                $livre->setDocument(null);
            }
        }

        return $this;
    }
}
