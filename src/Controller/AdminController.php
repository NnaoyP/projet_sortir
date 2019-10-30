<?php

namespace App\Controller;

use App\Entity\Participant;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class AdminController extends AbstractController
{
    /**
     * @Route("/admin", name="admin")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function index( )
    {
        $participants = $this->getDoctrine()->getRepository(Participant::class)->findAll();

        return $this->render('admin/index.html.twig', [
            'participants' => $participants
        ]);
    }

    /**
     *
     */
    public function action() {

    }
}
