<?php

namespace App\Controller;

use App\Entity\Trip;
use App\Form\TripType;
use Doctrine\ORM\EntityManagerInterface;
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

        ]);
    }

    /**
     * @Route("/trip/add", name="addTrip")
     * @param Request $request
     * @param EntityManagerInterface $em
     * @return Response
     */
    public function addTrip(Request $request,
                            EntityManagerInterface $em) {
        $trip = new Trip();
        $tripType = $this->createForm(TripType::class, $trip);

        $tripType->handleRequest($request);
        if ($tripType->isSubmitted() && $tripType->isValid()) {
            $status = $this->getDoctrine()->getRepository(Trip::class)->find(1)->getStatus();
            $trip->setStatus($status);
            $em->persist($trip);
            var_dump($trip);
            //$em->flush();
        }


        return $this->render("trip/add.html.twig", [
            "tripType" => $tripType->createView()
        ]);
    }
}
