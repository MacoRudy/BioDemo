<?php


namespace App\Service;


use App\Entity\Commande;
use App\Entity\Detail;
use Doctrine\ORM\EntityManagerInterface;

class ChartData
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function DataCommandesParSemaineBarre($annee)
    {
        $commandes = $this->em->getRepository(Commande::class)->findNombreCommandesParSemaineEtAnnee($annee);
        $arrayToDataTable[] = ['Semaine', 'Nombre de commande', ['role' => 'tooltip']];
        foreach ($commandes as $commande) {
            $arrayToDataTable[] = [$commande['semaine'], intval($commande['nbreCommande']), intval($commande['nbreCommande'])];
        }
        return $arrayToDataTable;
    }

    public function DataCommandesParMoisBarre($annee)
    {
        setlocale(LC_TIME, 'fr_FR.utf8', 'fra');

        $commandes = $this->em->getRepository(Commande::class)->findNombreCommandesParMoisEtAnnee($annee);
        $arrayToDataTable[] = ['Mois', 'Nombre de commandes', ['role' => 'tooltip']];
        foreach ($commandes as $commande) {
            $mois = utf8_encode(strftime('%B', mktime(0, 0, 0, $commande['mois'], 1)));
            $arrayToDataTable[] = [$mois, intval($commande['nbreCommande']), intval($commande['nbreCommande'])];
        }
        return $arrayToDataTable;
    }


    public function DataCommandesParMoisCamembert($annee)
    {
        setlocale(LC_TIME, 'fr_FR.utf8', 'fra');

        $commandes = $this->em->getRepository(Commande::class)->findNombreCommandesParMoisEtAnnee($annee);
        $arrayToDataTable[] = ['Mois', 'Nombre de commandes'];
        foreach ($commandes as $commande) {
            $mois = utf8_encode(strftime('%B', mktime(0, 0, 0, $commande['mois'], 1)));
            $arrayToDataTable[] = [$mois, intval($commande['nbreCommande'])];
        }
        return $arrayToDataTable;

    }

    public function DataVentesParProducteur($annee, $semaine)
    {

        $details = $this->em->getRepository(Detail::class)->findVenteProducteur($annee, $semaine);
        $arrayToDataTable[] = ['Producteur', 'Montant (€)'];
        $idProd = 0;
        $total = 0;
        $nomProd = '';
        // si on change de producteur sauf la premiere boucle
        foreach ($details as $detail) {
            if ($detail->getProducteur()->getId() != $idProd and $idProd != 0) {
                $arrayToDataTable[] = [$nomProd, $total];
                $total = 0;
            }
            $idProd = $detail->getProducteur()->getId();
            $nomProd = $detail->getProducteur()->getNom();
            $total = $total + ($detail->getQuantite() * $detail->getPrix());
        }
        $arrayToDataTable[] = [$nomProd, $total];
        return $arrayToDataTable;
    }

    public function DataVentesParDepot($annee, $semaine)
    {
        $details = $this->em->getRepository(Detail::class)->findVenteDepot($annee, $semaine);
        $arrayToDataTable[] = ['Dépôt', 'Montant (€)'];
        $idDepot = 0;
        $total = 0;
        $nomDepot = '';
        // si on change de depot sauf la premiere boucle
        foreach ($details as $detail) {
            if ($detail->getCommande()->getDepot()->getId() != $idDepot and $idDepot != 0) {
                $arrayToDataTable[] = [$nomDepot, $total];
                $total = 0;
            }

            $idDepot = $detail->getCommande()->getDepot()->getId();
            $nomDepot = $detail->getCommande()->getDepot()->getNom();
            $total = $total + ($detail->getQuantite() * $detail->getPrix());
        }
        $arrayToDataTable[] = [$nomDepot, $total];
        return $arrayToDataTable;

    }

    public function DataVentesParCategorie($annee, $semaine)
    {
        $details = $this->em->getRepository(Detail::class)->findVenteCategorie($annee, $semaine);

        $arrayToDataTable[] = ['Catégories', 'Montant (€)'];
        $idCat = 0;
        $total = 0;
        $nomCat = '';
        // si on change de depot sauf la premiere boucle
        foreach ($details as $detail) {
            if ($detail->getProduit()->getCategorie()->getCatParent()->getId() != $idCat and $idCat != 0) {
                $arrayToDataTable[] = [$nomCat, $total];
                $total = 0;
            }

            $idCat = $detail->getProduit()->getCategorie()->getCatParent()->getId();
            $nomCat = $detail->getProduit()->getCategorie()->getCatParent()->getNom();
            $total = $total + ($detail->getQuantite() * $detail->getPrix());
        }
        $arrayToDataTable[] = [$nomCat, $total];
        return $arrayToDataTable;
    }


}