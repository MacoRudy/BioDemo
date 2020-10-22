<?php

namespace App\Entity;

use App\Repository\CategorieRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=CategorieRepository::class)
 */
class Categorie
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $nom;

    /**
     * @ORM\ManyToOne(targetEntity=Categorie::class, inversedBy="CatEnfants")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $catParent;

    /**
     * @ORM\OneToMany(targetEntity=Categorie::class, mappedBy="catParent")
     */
    private $CatEnfants;

    /**
     * @ORM\OneToMany(targetEntity=Produit::class, mappedBy="categorie")
     */
    private $produits;

    public function __construct()
    {
        $this->CatEnfants = new ArrayCollection();
        $this->produits = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): self
    {
        $this->nom = $nom;

        return $this;
    }

    public function getCatParent(): ?self
    {
        return $this->catParent;
    }

    public function setCatParent(?self $catParent): self
    {
        $this->catParent = $catParent;

        return $this;
    }

    /**
     * @return Collection|self[]
     */
    public function getCatEnfants(): Collection
    {
        return $this->CatEnfants;
    }

    public function addCatEnfant(self $catEnfant): self
    {
        if (!$this->CatEnfants->contains($catEnfant)) {
            $this->CatEnfants[] = $catEnfant;
            $catEnfant->setCatParent($this);
        }

        return $this;
    }

    public function removeCatEnfant(self $catEnfant): self
    {
        if ($this->CatEnfants->contains($catEnfant)) {
            $this->CatEnfants->removeElement($catEnfant);
            // set the owning side to null (unless already changed)
            if ($catEnfant->getCatParent() === $this) {
                $catEnfant->setCatParent(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Produit[]
     */
    public function getProduits(): Collection
    {
        return $this->produits;
    }

    public function addProduit(Produit $produit): self
    {
        if (!$this->produits->contains($produit)) {
            $this->produits[] = $produit;
            $produit->setCategorie($this);
        }

        return $this;
    }

    public function removeProduit(Produit $produit): self
    {
        if ($this->produits->contains($produit)) {
            $this->produits->removeElement($produit);
            // set the owning side to null (unless already changed)
            if ($produit->getCategorie() === $this) {
                $produit->setCategorie(null);
            }
        }

        return $this;
    }
}
