<?php

namespace App\Controller;

use App\Entity\Depot;
use App\Form\DepotFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
/**
 * @Route("/admin")
 */
class DepotController extends AbstractController
{
    /**
     * @Route("/depot", name="depot")
     */
    public function depot()
    {
        $depot = $this->getDoctrine()
            ->getRepository(Depot::class)
            ->findAll();

        if (!$depot) {
            throw $this->createNotFoundException(
                'Aucun dépot trouvée'
            );
        }

        return $this->render('depot/depot.html.twig',
            ['depot' => $depot]
        );
    }

    /**
     * @Route("/depot/add", name="creation_depot")
     * @param Request $request
     * @param EntityManagerInterface $em
     * @return RedirectResponse|Response
     */
    public function add(Request $request, EntityManagerInterface $em)
    {
        $depot = new Depot();
        $form = $this->createForm(DepotFormType::class, $depot);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $em->persist($depot);
            $em->flush();
            $this->addFlash('success', 'Dépôt ajouté avec succès');

            return $this->redirectToRoute("depot");
        }

        return $this->render('depot/creationDepot.html.twig', [
                'depotForm' => $form->createView()]
        );
    }

    /**
     * @Route("/depot/detail/{id}", name="detail_depot", requirements={"id":"\d+"})
     * @param $id
     * @return Response
     */
    public function detail($id)
    {
        $depotRepo = $this->getDoctrine()->getRepository(Depot::class);
        $detail = $depotRepo->find($id);

        return $this->render('depot/detail.html.twig', [
            "detail" => $detail
        ]);
    }

    /**
     * @Route("/depot/edit/{id}", name="edit_depot", requirements={"id":"\d+"})
     * @param $id
     * @param Request $request
     * @param EntityManagerInterface $em
     * @return RedirectResponse|Response
     */
    public function edit($id, Request $request, EntityManagerInterface $em)
    {
        $depotRepo = $this->getDoctrine()->getRepository(Depot::class);
        $depot = $depotRepo->find($id);
        $form = $this->createForm(DepotFormType::class, $depot);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $em->persist($depot);
            $em->flush();
            $this->addFlash('success', 'Dépôt mis à jour avec succès');
            return $this->redirectToRoute("depot");
        }

        return $this->render('depot/edit.html.twig', [
            'depotForm' => $form->createView(),
                'id' => $id]
        );
    }

    /**
     * @Route ("/depot/delete/{id}", name="delete_depot", requirements={"id":"\d+"})
     * @param $id
     * @param EntityManagerInterface $em
     * @return RedirectResponse|Response
     */
    public function delete($id, EntityManagerInterface $em)
    {
        $depotRepo = $this->getDoctrine()->getRepository(Depot::class);
        $depot = $depotRepo->find($id);

        if ($depot != null) {
            $em->remove($depot);
            $em->flush();
        }
        $this->addFlash('success', 'Dépôt supprimé avec succès');

        return $this->redirectToRoute("depot");
    }
}
