<?php

namespace App\Repository;

use App\Entity\Detail;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Detail|null find($id, $lockMode = null, $lockVersion = null)
 * @method Detail|null findOneBy(array $criteria, array $orderBy = null)
 * @method Detail[]    findAll()
 * @method Detail[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DetailRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Detail::class);
    }

    public function findDetailSelonProducteur($annee, $semaine, $producteur)
    {
        return $this->createQueryBuilder('d')
            ->join('d.producteur', 'producteur')
            ->join('d.commande', 'commande')
            ->join('commande.depot', 'depot')
            ->join('commande.user', 'user')
            ->join('d.produit', 'produit')
            ->select('d,producteur,commande,produit,depot,user')
            ->andWhere('commande.annee = :annee')
            ->setParameter('annee', $annee)
            ->andWhere('commande.semaine = :semaine')
            ->setParameter('semaine', $semaine)
            ->andWhere('d.producteur = :prod')
            ->setParameter('prod', $producteur)
            ->orderBy('depot.id', 'ASC')
            ->addOrderBy('user.nom', 'ASC')
            ->getQuery()
            ->getResult();
    }


    public function findProduitSelonProducteur($annee, $semaine, $producteur)
    {
        return $this->createQueryBuilder('d')
            ->join('d.commande', 'commande')
            ->join('d.produit', 'produit')
            ->select('d ,commande, produit')
            ->andWhere('commande.annee = :annee')
            ->setParameter('annee', $annee)
            ->andWhere('commande.semaine = :semaine')
            ->setParameter('semaine', $semaine)
            ->andWhere('d.producteur = :prod')
            ->setParameter('prod', $producteur)
            ->addOrderBy('produit.id', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
