<?php


namespace App\Service;


use CMEN\GoogleChartsBundle\GoogleCharts\Charts\ColumnChart;

class Chart
{

    private $chartData;

    public function __construct(ChartData $chartData)
    {
        $this->chartData = $chartData;
    }

    public function commandesParMoisBarre($annee)
    {
        $arrayToDataTable = $this->chartData->DataCommandesParMoisBarre($annee);

        $chart = new ColumnChart();
        $chart->getData()->setArrayToDataTable($arrayToDataTable);
        $chart->getOptions()->setTitle('Commandes par Mois');
        $chart->getOptions()->getVAxis()->setTitle('Nombre de commandes');
        $chart->getOptions()->getVAxis()->setMinValue(0);
        $chart->getOptions()->getHAxis()->setTitle('Mois');
//        $chart->getOptions()->setWidth(900);
        $chart->getOptions()->setHeight(700);

        return $chart;
    }

    public function commandesParSemaineBarre($annee)
    {
        $arrayToDataTable = $this->chartData->DataCommandesParSemaineBarre($annee);

        $chart = new ColumnChart();
        $chart->getData()->setArrayToDataTable($arrayToDataTable);
        $chart->getOptions()->setTitle('Commandes par Semaine');
        $chart->getOptions()->getVAxis()->setTitle('Nombre de commandes');
        $chart->getOptions()->getVAxis()->setMinValue(0);
        $chart->getOptions()->getHAxis()->setTitle('Semaine');
        $chart->getOptions()->setHeight(700);

        return $chart;
    }

    public function commandesParMoisCamembert($annee)
    {
        return $this->chartData->DataCommandesParMoisCamembert($annee);
    }

    public function ventesParProducteur($annee, $semaine)
    {
        $arrayToDataTable = $this->chartData->DataVentesParProducteur($annee, $semaine);

        $chart = new ColumnChart();
        $chart->getData()->setArrayToDataTable($arrayToDataTable);
        if ($semaine != 0) {
            $chart->getOptions()->setTitle('Ventes par Producteur pour la semaine ' . $semaine . ' de ' . $annee);
        } else {
            $chart->getOptions()->setTitle('Ventes par Producteur pour ' . $annee);
        }
        $chart->getOptions()->getVAxis()->setTitle('Montant (€)');
        $chart->getOptions()->getVAxis()->setMinValue(0);
        $chart->getOptions()->getHAxis()->setTitle('Producteurs');
        $chart->getOptions()->setHeight(700);

        return $chart;
    }


    public function ventesProducteurCamembert($annee, $semaine)
    {
        return $this->chartData->DataVentesParProducteur($annee, $semaine);
    }

    public function ventesParDepot($annee, $semaine)
    {
        $arrayToDataTable = $this->chartData->DataVentesParDepot($annee, $semaine);

        $chart = new ColumnChart();
        $chart->getData()->setArrayToDataTable($arrayToDataTable);
        if ($semaine != 0) {
            $chart->getOptions()->setTitle('Ventes par Dépôt pour la semaine ' . $semaine . ' de ' . $annee);
        } else {
            $chart->getOptions()->setTitle('Ventes par Dépôt pour ' . $annee);
        }
        $chart->getOptions()->getVAxis()->setTitle('Montant (€)');
        $chart->getOptions()->getVAxis()->setMinValue(0);
        $chart->getOptions()->getHAxis()->setTitle('Dépôts');
        $chart->getOptions()->setHeight(700);

        return $chart;
    }

    public function ventesDepotCamembert($annee,  $semaine)
    {
        return $this->chartData->DataVentesParDepot($annee, $semaine);
    }


}