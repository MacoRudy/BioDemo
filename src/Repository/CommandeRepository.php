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

        $qb = $this->createQueryBuilder('c')
            ->join('c.depot', 'd')
            ->addSelect('d')
            ->Join('c.user', 'u')
            ->addSelect('u')
            ->orderBy('u.nom', 'ASC');
        return $qb->getQuery()->getResult();

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


}
