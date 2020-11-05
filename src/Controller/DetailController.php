<?php

namespace App\Controller;

use App\Entity\Commande;
use App\Entity\Detail;
use App\Entity\Produit;
use App\Form\DetailFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin")
 */
class DetailController extends AbstractController
{
    /**
     * @Route("/detail", name="detail")
     */
    public function index()
    {
        return $this->render('detail/index.html.twig', [
            'controller_name' => 'DetailController',
        ]);
    }


    /**
     * @Route("/detail/add/{id}", name="creation_detail", requirements={"id":"\d+"})))
     * @param Request $request
     * @param EntityManagerInterface $em
     * @param $id
     * @return RedirectResponse|Response
     */
    public function add(Request $request, EntityManagerInterface $em, $id)
    {
        $detail = new Detail();

        $commande = $this->getDoctrine()->getRepository(Commande::class)->find($id);
        $form = $this->createForm(DetailFormType::class, $detail);
        $form->handleRequest($request);


        if ($form->isSubmitted() && $form->isValid()) {

            // recuperation du prix et de la quantité a ajouter
            $prix = $form->get('produit')->getData()->getPrix();
            $quantite = $form->get('quantite')->getData();

            // mise a jour du montant total de la commande
            $commande->setMontant($commande->getMontant() + ($prix * $quantite));

            // Ajout des données en recuperant les valeurs du produit ajouté
            $detail->setPrix($prix);
            $detail->setQuantite($quantite);

            // ajout de la commande
            $detail->setCommande($commande);

            // Ajout du producteur du produit
            $detail->setProducteur($form->get('produit')->getData()->getProducteur());

            // Ajout du detail et mise a jour de la commande correspondante
            $em->persist($commande);
            $em->persist($detail);

            $em->flush();

            $this->addFlash('success', 'Produit ajouté a la commande avec succès');
            return $this->redirectToRoute("detail_commande", [
                "id" => $id
            ]);
        }

        return $this->render('detail/creationDetail.html.twig', [
                'detailForm' => $form->createView(), 'commande' => $commande]
        );
    }

    /**
     * @Route("/detail/detail", name="detail_detail")
     */
    public function detail()
    {
        return $this->render('detail/detail.html.twig', [
            'controller_name' => 'DetailController',
        ]);
    }

    /**
     * @Route("/detail/prix", name="detail_prix", methods={"POST"})
     * @param Request $request
     * @return JsonResponse
     */
    public function prix(Request $request)
    {
        $idProduit = $request->get('idProduit');

        $prix = $this->getDoctrine()->getRepository(Produit::class)->findPrixDuProduit($idProduit);

        return new JsonResponse($prix);

    }


}
