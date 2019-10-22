<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Entity\Trip;
use App\Form\TripType;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TripController extends AbstractController
{
    /**
     * @Route("/home", name="home")
     */
    public function index()
    {
        return $this->render("trip/index.html.twig", [
            'controller_name' => 'TripController'
        ]);
    }

    /**
     * @Route("/trip/add", name="addTrip")
     * @method Participant getUser()
     * @param Request $request
     * @param EntityManagerInterface $em
     * @return Response
     */
    public function addTrip(Request $request,
                            EntityManagerInterface $em) {
        $trip = new Trip();
        $organizer = $this->getUser();
        $tripType = $this->createForm(TripType::class, $trip);

        $tripType->handleRequest($request);
        if ($tripType->isSubmitted() && $tripType->isValid()) {
            $status = $this->getDoctrine()->getRepository(Trip::class)->find(1)->getStatus();
            $trip->setStatus($status);
            $trip->setOrganizer($organizer);
            $em->persist($trip);
            var_dump($trip);
            //$em->flush();
        }


        return $this->render("trip/add.html.twig", [
            'tripType' => $tripType->createView(),
            'tripOrganizer' => $organizer
        ]);
    }
}
