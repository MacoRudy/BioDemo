<?php


namespace App\Tests\Entity;

use App\Entity\Categorie;
use App\Entity\Commande;
use App\Entity\Depot;
use App\Entity\Detail;
use App\Entity\Producteur;
use App\Entity\Produit;
use App\Entity\User;
use DateTime;
use PHPUnit\Framework\TestCase;

class EntityTest extends TestCase
{
    public function testEntities()
    {
        $date = new DateTime();
        $user = new User();
        $depot = new Depot();
        $producteur = new Producteur();
        $commande = new Commande();
        $categorie = new Categorie();
        $detail = new Detail();
        $produit = new Produit();

        $producteur->setNom("NomProducteur");
        $producteur->setAdresse("AdresseProducteur");
        $producteur->setCodePostal(55555);
        $producteur->setCode("CodeProducteur");
        $producteur->setEmail("email@producteur.fr");
        $producteur->setVille("VilleProducteur");
        $producteur->setTelephone("9988776655");
        $producteur->setDescription("DescriptionProducteur");
        $producteur->setSiret("Siret");
        $producteur->addDetail($detail);
        $producteur->addProduit($produit);

        $depot->setNom("NomDepot");
        $depot->setTelephone("0011223344");
        $depot->setVille("VilleDepot");
        $depot->setEmail("email@depot.fr");
        $depot->setCodePostal(98765);
        $depot->setAdresse("AdresseDepot");
        $depot->addUser($user);
        $depot->addCommande($commande);

        $user->setNom("Nom");
        $user->setPrenom("Prenom");
        $user->setAdresse("Adresse");
        $user->setCodePostal(12345);
        $user->setEmail("test@test.com");
        $user->setVille("Ville");
        $user->setTelephone("0123456789");
        $user->setRoles(["ROLE_USER"]);
        $user->setValide(true);
        $user->setDateInscription($date);
        $user->setPassword('password');
        $user->setProducteur($producteur);

        $user->setDepot($depot);


        $commande->setDepot($depot);
        $commande->setMontant(1000);
        $commande->setAnnee(2020);
        $commande->setSemaine(52);
        $commande->setDateLivraison($date);
        $commande->setDateCreation($date);
        $commande->setUser($user);


        $categorie->setNom("NomCategorie");
        $categorie->setCatParent($categorie);
        $categorie->addCatEnfant($categorie);
        $categorie->addProduit($produit);

        $produit->setPrix("123");
        $produit->setProducteur($producteur);
        $produit->setDescription("DescriptionProduit");
        $produit->setNom("NomProduit");
        $produit->setCategorie($categorie);
        $produit->setReference("ReferenceProduit");
        $produit->addDetail($detail);


        $detail->setProducteur($producteur);
        $detail->setQuantite(12);
        $detail->setPrix("365");
        $detail->setCommande($commande);
        $detail->setProduit($produit);

        $commande->addDetail($detail);
        $user->addCommande($commande);

        $this->assertEquals("Nom", $user->getNom());
        $this->assertEquals("Prenom", $user->getPrenom());
        $this->assertEquals("Adresse", $user->getAdresse());
        $this->assertEquals(12345, $user->getCodePostal());
        $this->assertEquals("test@test.com", $user->getEmail());
        $this->assertEquals("Ville", $user->getVille());
        $this->assertEquals("0123456789", $user->getTelephone());
        $this->assertEquals(["ROLE_USER"], $user->getRoles());
        $this->assertEquals(true, $user->getValide());
        $this->assertEquals($date, $user->getDateInscription());
        $this->assertEquals("password", $user->getPassword());
        $this->assertEquals($producteur, $user->getProducteur());
        $this->assertEquals($depot, $user->getDepot());
        $this->assertEquals($produit, $detail->getProduit());

        $produit->removeDetail($detail);
        $this->assertEquals(true, $produit->getDetails()->isEmpty());

        $categorie->removeProduit($produit);
        $this->assertEquals(true, $categorie->getProduits()->isEmpty());

        $categorie->removeCatEnfant($categorie);
        $this->assertEquals(true, $categorie->getCatEnfants()->isEmpty());

        $commande->removeDetail($detail);
        $this->assertEquals(true, $commande->getDetails()->isEmpty());

        $depot->removeCommande($commande);
        $this->assertEquals(true, $depot->getCommandes()->isEmpty());

        $depot->removeUser($user);
        $this->assertEquals(true, $depot->getUsers()->isEmpty());


        $producteur->removeDetail($detail);
        $producteur->removeProduit($produit);
        $this->assertEquals(true, $producteur->getDetails()->isEmpty());
        $this->assertEquals(true, $producteur->getProduits()->isEmpty());

        $user->removeCommande($commande);
        $this->assertEquals(true, $user->getCommandes()->isEmpty());


    }
}