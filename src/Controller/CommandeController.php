<?php

namespace App\Controller;
use App\Entity\Commande;
use App\Entity\Detail;
use App\Entity\User;
use App\Form\CommandeFormType;
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
class CommandeController extends AbstractController
{
    /**
     * @Route("/commande", name="commande")
     */
    public function index()
    {
        $commande = $this->getDoctrine()->getRepository(Commande::class)->findCommandesAvecDepot();

        $clients = $this->getDoctrine()->getRepository(User::class)->findClientsAvecCommande();

        $annees = $this->getDoctrine()->getRepository(Commande::class)->findAnneesAvecCommande();

        return $this->render('commande/commande.html.twig',
            ['commande' => $commande, 'clients' => $clients,  'annees' => $annees]
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

        $produit = $this->getDoctrine()->getRepository(Detail::class)->findBy(['commande' => $commande], ['producteur' => 'ASC']);

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

//     TODO

        return $this->render('commande/edit.html.twig',
            ['commande' => $commande]
        );
    }

    /**
     * @Route("/commande/delete/{id}", name="delete_commande", requirements={"id":"\d+"}))
     */
    public function delete()
    {
        $commande = $this->getDoctrine()->getRepository(Commande::class)->findAll();

//     TODO

        return $this->redirectToRoute("commande");
    }


    /**
     * @Route("/commande/trier/client", name="tri_annee_par_client", methods={"POST"})
     * @param Request $request
     * @return JsonResponse
     */
    public function trierAnneesParClient(Request $request) {


        $id = $request->get('idClient');

        $user = $this->getDoctrine()->getRepository(User::class)->find($id);

        $annees = $this->getDoctrine()->getRepository(Commande::class)->findAnneesDesCommandesSelonClient($user);

        return new JsonResponse($annees);
    }

    /**
     * @Route("/commande/trier/annee/semaine", name="tri_semaine_par_annee", methods={"POST"})
     * @param Request $request
     * @return JsonResponse
     */
    public function trierSemainesParAnnee(Request $request) {


        $id = $request->request->get("idClient");
        $annee = $request->request->get('annee');



        $user = $this->getDoctrine()->getRepository(User::class)->find($id);

        $semaines = $this->getDoctrine()->getRepository(Commande::class)->findSemaineDesCommandesSelonClientEtAnnee($user, $annee);
        $annee = $this->getDoctrine()->getRepository(Commande::class)->findAnneesDesCommandesSelonClient($user);

        $anneeEtSemaines = array_merge($semaines,$annee);
        return new JsonResponse($anneeEtSemaines);
    }


    /**
     * @Route("/commande/trier/client/semaine", name="tri_semaine_par_client", methods={"POST"})
     * @param Request $request
     * @return JsonResponse
     */
    public function trierSemainesParClient(Request $request) {


        $id = $request->request->get("idClient");
        $annee = $request->request->get('annee');



        $user = $this->getDoctrine()->getRepository(User::class)->find($id);

        $semaines = $this->getDoctrine()->getRepository(Commande::class)->findSemaineDesCommandesSelonClientEtAnnee($user, $annee);
        $client =  $this->getDoctrine()->getRepository(User::class)->findClientsAvecCommandeSelonAnnee($annee);

        $clientEtSemaines = array_merge($semaines,$client);
        return new JsonResponse($clientEtSemaines);
    }


    /**
     * @Route("/commande/trier/annee", name="tri_client_par_annee", methods={"POST"})
     * @param Request $request
     * @return JsonResponse
     */
    public function trierClientParAnnee(Request $request) {
        $annee = $request->get('annee');

        $client =  $this->getDoctrine()->getRepository(User::class)->findClientsAvecCommandeSelonAnnee($annee);

        return new JsonResponse($client);
    }
}
