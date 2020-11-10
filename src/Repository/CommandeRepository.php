<?php

namespace App\Repository;

use App\Entity\Commande;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Commande|null find($id, $lockMode = null, $lockVersion = null)
 * @method Commande|null findOneBy(array $criteria, array $orderBy = null)
 * @method Commande[]    findAll()
 * @method Commande[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CommandeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Commande::class);
    }

    public function findSemainesAvecCommande()
    {
        return $this->createQueryBuilder('c')
            ->select('c.semaine')
            ->distinct()
            ->orderBy('c.semaine', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findAnneesAvecCommande()
    {
        return $this->createQueryBuilder('c')
            ->select('c.annee')
            ->distinct()
            ->orderBy('c.annee', 'ASC')
            ->getQuery()
            ->getResult();
    }


    public function findCommandesAvecDepot()
    {
        return $this->createQueryBuilder('c')
            ->join('c.depot', 'd')
            ->addSelect('d')
            ->join('c.user', 'u')
            ->addSelect('u')
            ->orderBy('u.nom', 'ASC')
            ->getQuery()->getResult();

    }


    public function findAnneesDesCommandesSelonClient($user)
    {

        return $this->createQueryBuilder('c')
            ->select('c.annee')
            ->distinct()
            ->where('c.user = :user')
            ->setParameter('user', $user)
            ->orderBy('c.annee', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findSemaineDesCommandesSelonClientEtAnnee($user, $annee)
    {
        return $this->createQueryBuilder('c')
            ->select('c.semaine')
            ->distinct()
            ->andWhere('c.user = :user')
            ->setParameter('user', $user)
            ->andWhere('c.annee = :annee')
            ->setParameter('annee', $annee)
            ->orderBy('c.semaine', 'ASC')
            ->getQuery()
            ->getResult();

    }

    public function findSemaineDesCommandesSelonAnnee($annee)
    {
        return $this->createQueryBuilder('c')
            ->select('c.semaine')
            ->distinct()
            ->andWhere('c.annee = :annee')
            ->setParameter('annee', $annee)
            ->orderBy('c.semaine', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findCommandesSelonSemaineEtAnnee($annee, $semaine)
    {
        $qb = $this->createQueryBuilder('c')
            ->join('c.user', 'user')
            ->join('c.depot', 'depot')
            ->join('c.details', 'details')
            ->join('details.produit', 'produit')
            ->join('details.producteur', 'producteur')
            ->join('produit.categorie', 'categorie')
            ->select('c,user,depot,details,produit,producteur,categorie')
            ->where('c.annee = :annee')
            ->setParameter('annee', $annee);
        if ($semaine != 0) {
            $qb->andWhere('c.semaine = :semaine')
                ->setParameter('semaine', $semaine);
        }
        $qb->orderBy('depot.nom', 'ASC')
            ->addOrderBy('producteur.nom');


        return $qb->getQuery()->getResult();
    }

    public function findCommandesSelonDepot($annee, $semaine, $depot)
    {
        $qb = $this->createQueryBuilder('c')
            ->join('c.user', 'user')
            ->join('c.depot', 'depot')
            ->join('c.details', 'details')
            ->join('details.produit', 'produit')
            ->join('details.producteur', 'producteur')
            ->join('produit.categorie', 'categorie')
            ->select('c,user,depot,details,produit,producteur,categorie')
            ->where('c.annee = :annee')
            ->setParameter('annee', $annee)
            ->andWhere('c.depot = :depot')
            ->setParameter('depot', $depot);
        if ($semaine != 0) {
            $qb->andWhere('c.semaine = :semaine')
                ->setParameter('semaine', $semaine);
        }
        $qb->orderBy('producteur.nom', 'ASC');


        return $qb->getQuery()->getResult();
    }

    public function findDepotSelonSemaineEtAnnee($annee, $semaine)
    {
        return $this->createQueryBuilder('c')
            ->join('c.depot', 'd')
            ->select('d.nom, d.id')
            ->distinct()
            ->andWhere('c.annee = :annee')
            ->setParameter('annee', $annee)
            ->andWhere('c.semaine = :semaine')
            ->setParameter('semaine', $semaine)
            ->orderBy('d.nom', 'ASC')
            ->getQuery()
            ->getResult();

    }


    public function findProducteurSelonSemaineEtAnnee($annee, $semaine)
    {
        return $this->createQueryBuilder('c')
            ->join('c.details', 'd')
            ->join('d.producteur', 'p')
            ->select('p.nom, p.id')
            ->distinct()
            ->andWhere('c.annee = :annee')
            ->setParameter('annee', $annee)
            ->andWhere('c.semaine = :semaine')
            ->setParameter('semaine', $semaine)
            ->orderBy('p.nom', 'ASC')
            ->getQuery()
            ->getResult();

    }




}
