<?php

namespace App\Controller;

use App\Entity\Commande;
use App\Entity\Detail;
use App\Form\CommandeFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin")
 */
class CommandeController extends AbstractController
{
    /**
     * @Route("/commande", name="commande")
     */
    public function index()
    {
        $commande = $this->getDoctrine()->getRepository(Commande::class)->findAll();

//        if (!$commande) {
//            throw $this->createNotFoundException(
//                'Aucune commande trouvée'
//            );
//        }

        return $this->render('commande/commande.html.twig',
            ['commande' => $commande]
        );
    }


    /**
     * @Route("/commande/add", name="creation_commande")
     * @param Request $request
     * @param EntityManagerInterface $em
     * @return RedirectResponse|Response
     */
    public function add(Request $request, EntityManagerInterface $em)
    {
        $commande = new Commande();
        $form = $this->createForm(CommandeFormType::class, $commande);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $commande->setAnnee($form->get('dateCreation')->getData()->format("Y"));
            $commande->setSemaine($form->get('dateCreation')->getData()->format("W"));
            $em->persist($commande);
            $em->flush();

            $this->addFlash('success', 'Commande ajouté avec succès');
            return $this->redirectToRoute("commande");
        }

        return $this->render('commande/creationCommande.html.twig', [
                'commandeForm' => $form->createView()]
        );
    }

    /**
     * @Route("/commande/detail/{id}", name="detail_commande", requirements={"id":"\d+"})
     * @param $id
     * @return Response
     */
    public function detail($id)
    {
        $commandeRepo = $this->getDoctrine()->getRepository(Commande::class);
        $commande = $commandeRepo->find($id);

        $produit = $this->getDoctrine()->getRepository(Detail::class)->findBy(['commande'=>$commande]);

        return $this->render('commande/detail.html.twig', [
            "commande" => $commande,
            "produit" => $produit
        ]);
    }


}
