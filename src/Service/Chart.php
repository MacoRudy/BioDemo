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


}