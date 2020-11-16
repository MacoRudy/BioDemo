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

    public function findDetailSelonProducteur($annee, $mois, $semaine, $producteur)
    {
        $qb = $this->createQueryBuilder('d')
            ->join('d.producteur', 'producteur')
            ->join('d.commande', 'commande')
            ->join('commande.depot', 'depot')
            ->join('commande.user', 'user')
            ->join('d.produit', 'produit')
            ->select('d,producteur,commande,produit,depot,user')
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
        $qb->andWhere('d.producteur = :prod')
            ->setParameter('prod', $producteur)
            ->orderBy('depot.id', 'ASC')
            ->addOrderBy('user.nom', 'ASC');

        return $qb->getQuery()->getResult();
    }


    public function findProduitSelonProducteur($annee, $mois, $semaine, $producteur)
    {
        $qb = $this->createQueryBuilder('d')
            ->join('d.commande', 'commande')
            ->join('d.produit', 'produit')
            ->select('d ,commande, produit')
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
        $qb->andWhere('d.producteur = :prod')
            ->setParameter('prod', $producteur)
            ->addOrderBy('produit.id', 'ASC');

        return $qb->getQuery()->getResult();
    }

    public function findVenteProducteur($annee, $semaine)
    {
        $qb = $this->createQueryBuilder('d')
            ->join('d.commande', 'commande')
            ->join('d.producteur', 'producteur')
            ->select('d , commande, producteur')
            ->andWhere('commande.annee = :annee')
            ->setParameter('annee', $annee);
        if ($semaine != 0) {
            $qb->andWhere('commande.semaine = :semaine')
                ->setParameter('semaine', $semaine);
        }
        $qb->orderBy('producteur.nom');

        return $qb->getQuery()->getResult();
    }

    public function findVenteDepot($annee, $semaine)
    {
        $qb = $this->createQueryBuilder('d')
            ->join('d.commande', 'commande')
            ->join('commande.depot', 'depot')
            ->select('d , commande, depot')
            ->andWhere('commande.annee = :annee')
            ->setParameter('annee', $annee);
        if ($semaine != 0) {
            $qb->andWhere('commande.semaine = :semaine')
                ->setParameter('semaine', $semaine);
        }
        $qb->orderBy('depot.nom');

        return $qb->getQuery()->getResult();
    }

    public function findVenteCategorie($annee, $semaine)
    {
        $qb = $this->createQueryBuilder('d')
            ->join('d.commande','commande')
            ->join('d.produit', 'produit')
            ->join('produit.categorie', 'categorie')
            ->select('d , produit, categorie, commande')
            ->andWhere('commande.annee = :annee')
            ->setParameter('annee', $annee);
        if ($semaine != 0) {
            $qb->andWhere('commande.semaine = :semaine')
                ->setParameter('semaine', $semaine);
        }
        $qb->orderBy('categorie.catParent');

        return $qb->getQuery()->getResult();
    }

}
