<?php

namespace App\Controller;

use App\Entity\Commande;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class StatistiqueController extends AbstractController
{
    /**
     * @Route("/statistique", name="statistique")
     */
    public function index()
    {
        $annees = $this->getDoctrine()->getRepository(Commande::class)->findAnneesAvecCommande();


        $param = [
            'annee' => 'annees',
            'producteur' => 'producteurs',
            'depot' => 'depots',
            'commande' => 'commandes',
            'client' => 'clients'
        ];

        return $this->render('statistique/index.html.twig', [
            'param' => $param , 'anneeCommande'=>$annees
        ]);
    }

    /**
     * @Route("/statistique/filtre", name="filtre_statistique", methods={"POST"})
     * @param Request $request
     */
    public function traitementFiltres(Request $request)
    {
        $listePHP = $request->getContent();
        $listeDecode = json_decode($listePHP);

        if (in_array("annees", $listeDecode)) {

        }


        return new JsonResponse($listeDecode);
    }

    /**
     * @Route("/etat", name="etat")
     */
    public function etat()
    {

        return $this->render('statistique/etats.html.twig');
    }


}
