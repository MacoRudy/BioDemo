<?php

namespace App\Repository;

use App\Entity\Produit;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Produit|null find($id, $lockMode = null, $lockVersion = null)
 * @method Produit|null findOneBy(array $criteria, array $orderBy = null)
 * @method Produit[]    findAll()
 * @method Produit[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProduitRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Produit::class);
    }

// Liste de produit classée par producteurs puis par categories
    public function findProduitByProducteur()
    {
        return $this->createQueryBuilder('p')
            ->select('p')
            ->join('p.categorie', 'c')
            ->where('c.catParent is not null')
            ->addOrderBy('p.producteur', 'ASC')
            ->addOrderBy('c.catParent', 'ASC')
            ->addOrderBy('c.id', 'ASC')
            ->getQuery()
            ->getResult();
    }

    // Liste de produit classée par catégories puis par producteurs
    public function findProduitByCategorie()
    {
        return $this->createQueryBuilder('p')
            ->select('p')
            ->join('p.categorie', 'c')
            ->where('c.catParent is not null')
            ->addOrderBy('c.catParent', 'ASC')
            ->addOrderBy('c.id', 'ASC')
            ->addOrderBy('p.producteur', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findPrixDuProduit($id)
    {
        return $this->createQueryBuilder('p')
            ->select('p.prix')
            ->andWhere('p.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getResult();
    }


    public function findProduitSelonCategorie($annee, $mois, $semaine)
    {
        $qb = $this->createQueryBuilder('p')
            ->join('p.details', 'details')
            ->join('p.categorie', 'categorie')
            ->join('categorie.catParent', 'catParent')
            ->join('details.commande', 'commande')
            ->select('p, details, categorie, catParent')
            ->andWhere('commande.annee = :annee')
            ->setParameter('annee', $annee);
        if ($semaine != 0) {
            $qb->andWhere('commande.semaine = :semaine')
                ->setParameter('semaine', $semaine);
        }
        if ($mois != 0) {
            $qb->andWhere('MONTH(commande.dateCreation) = :mois')
                ->setParameter('mois', $mois);
        }
        $qb->addOrderBy('catParent.id', 'ASC')
            ->addOrderBy('categorie.id', 'ASC')
            ->addOrderBy('p.nom', 'ASC');
        return $qb->getQuery()->getResult();
    }
}
