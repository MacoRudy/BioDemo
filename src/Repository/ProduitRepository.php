<?php

namespace App\Repository;

use App\Entity\Produit;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
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
            ->addOrderBy('p.categorie', 'ASC')
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
            ->addOrderBy('p.categorie', 'ASC')
            ->addOrderBy('p.producteur', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findPrixDuProduit($id)
    {
        try {
            return $this->createQueryBuilder('p')
                ->select('p.prix')
                ->andWhere('p.id = :id')
                ->setParameter('id', $id)
                ->getQuery()
                ->getSingleResult();
        } catch (NoResultException $e) {
        } catch (NonUniqueResultException $e) {
        }
    }

}
