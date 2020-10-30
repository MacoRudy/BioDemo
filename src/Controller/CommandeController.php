<?php

namespace App\Controller;

use App\Entity\Commande;
use App\Entity\Detail;
use App\Entity\User;
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

        $clients = $this->getDoctrine()->getRepository(User::class)->findClientsAvecCommande();

        $semaines = $this->getDoctrine()->getRepository(Commande::class)->findSemainesAvecCommande();

        $annees = $this->getDoctrine()->getRepository(Commande::class)->findAnneesAvecCommande();

        return $this->render('commande/commande.html.twig',
            ['commande' => $commande, 'clients' => $clients, 'semaines' => $semaines, 'annees' => $annees]
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

        $produit = $this->getDoctrine()->getRepository(Detail::class)->findBy(['commande' => $commande], ['producteur'=>'ASC']);

        return $this->render('commande/detail.html.twig', [
            "commande" => $commande,
            "produit" => $produit
        ]);
    }

    /**
     * @Route("/commande/edit/{id}", name="edit_commande", requirements={"id":"\d+"}))
     */
    public function edit()
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
     * @Route("/commande/delete/{id}", name="delete_commande", requirements={"id":"\d+"}))
     */
    public function delete()
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
     * @Route("/commande/trier/client", name="trier_client_commande")
     * @param Request $request
     * @return Response
     */
    public function trierParClient(Request $request)
    {
        $id = $request->query->get('client');
        $userRepo = $this->getDoctrine()->getRepository(User::class);
        $user = $userRepo->find($id);


        $commande = $this->getDoctrine()->getRepository(Commande::class)->findBy(['user' => $user], ['dateCreation'=>'ASC']);

        $clients = $this->getDoctrine()->getRepository(User::class)->findClientsAvecCommande();

        $semaines = $this->getDoctrine()->getRepository(Commande::class)->findSemainesAvecCommande();

        $annees = $this->getDoctrine()->getRepository(Commande::class)->findAnneesAvecCommande();



        return $this->render('commande/commande.html.twig',
            ['commande' => $commande, 'clients' => $clients, 'semaines' => $semaines, 'annees' => $annees]
        );
    }

    /**
     * @Route("/commande/trier/semaine", name="trier_semaine_commande")
     */
    public function trierParSemaine()
    {


    }



    /**
     * @Route("/commande/trier/annee", name="trier_annee_commande")
     */
    public function trierParAnnee()
    {

    }



}
