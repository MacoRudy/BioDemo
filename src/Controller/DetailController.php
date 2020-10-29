<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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
        return $this->render('detail/detail.html.twig', [
            'controller_name' => 'DetailController',
        ]);
    }
}
