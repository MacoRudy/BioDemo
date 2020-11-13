<?php

namespace App\Controller;

use App\Entity\Commande;
use App\Service\Chart;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class GraphiqueController extends AbstractController
{
    /**
     * @Route("/graphique", name="graphique")
     * @return Response
     */
    public function index()
    {
        $annees = $this->getDoctrine()->getRepository(Commande::class)->findAnneesAvecCommande();
        return $this->render('graphique/index.html.twig', ['annees' => $annees]);
    }


    /**
     * @Route("/graphique/commandes/date/barres", name="commandes_par_date_barre_graphique", methods={"POST"})
     * @param Chart $chart
     * @param Request $request
     * @return Response
     */
    public function commandesParDateBarre(Chart $chart, Request $request)
    {
        $annee = $request->request->get('annee');
        if ($request->request->has('mois')) {
            $chart = $chart->commandesParMoisBarre($annee);
        } else {
            $chart = $chart->commandesParSemaineBarre($annee);
        }
        return $this->render('graphique/barGraph.html.twig', ['chart' => $chart]);
    }


    /**
     * @Route("/graphique/commandes/date/camembert", name="commandes_par_date_camembert_graphique", methods={"POST"})
     * @param Chart $chart
     * @param Request $request
     * @return Response
     */
    public function commandesParDateCamembert(Request $request)
    {
        $annee = $request->request->get('annee');

        return $this->render('graphique/3dPieChart.html.twig', ['annee'=> $annee]);
    }



    /**
     * @Route("/graphique/commandes/date/3d", name="commandes_3d_graphique", methods={"POST"})
     * @param Chart $chart
     * @return Response
     */
    public function Ajax3DPieChart(Request $request, Chart $chart)
    {
        $annee = $request->get('annee');
        $arrayToDataTable = $chart->commandesParMoisCamembert($annee);
        return new JsonResponse($arrayToDataTable);
    }


}
