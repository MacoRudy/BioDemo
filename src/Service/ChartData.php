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
                // on remet le total a 0;
                $total = 0;
            }
            $idProd = $detail->getProducteur()->getId();
            $nomProd = $detail->getProducteur()->getNom();
            $total = $total + ($detail->getQuantite() * $detail->getPrix());
        }
        $arrayToDataTable[] = [$nomProd, $total];
        return $arrayToDataTable;
    }




}