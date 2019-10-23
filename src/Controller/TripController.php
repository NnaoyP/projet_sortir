<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Entity\Trip;
use App\Entity\TripPlace;
use App\Entity\TripStatus;
use App\Form\TripType;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TripController extends AbstractController
{
    /**
     * @Route("/trip", name="trip")
     * @param Request $request
     * @return Response
     */
    public function searchTrip(Request $request)
    {
        // récupération des sorties publiées
        //$trips = $this->getDoctrine()->getRepository(Trip::class)->findByFilter($request->request);

        $trips = $this->getDoctrine()->getRepository(Trip::class)->findAll();
        $places = $this->getDoctrine()->getRepository(TripPlace::class)->findAll();

        return $this->render("trip/index.html.twig", [
            'trips' => $trips,
            'places' => $places
        ]);
    }

    /**
     * @Route("/trip/add", name="trip_add")
     * @method Participant getUser()
     * @param Request $request
     * @param EntityManagerInterface $em
     * @return Response
     * @throws Exception
     */
    public function addTrip(Request $request,
                            EntityManagerInterface $em) {
        // création de la sortie a mapper avec le formulaire
        $trip = new Trip();


        // récupération des données non séléctionnables par l'utilisateur
        $organizer = $this->getUser();
        $places = $em->getRepository(TripPlace::class)->findAll();

        $status = $em->getRepository(TripStatus::class)->find(1);
        $trip->setStatus($status);
        $trip->setParticipantArea($organizer->getParticipantArea());
        $trip->setOrganizer($organizer);
        $trip->addParticipant($organizer);

        // création du formulaire et association de la sortie au formulaire
        $tripType = $this->createForm(TripType::class, $trip);

        $tripType->handleRequest($request);
        if ($tripType->isSubmitted() && $tripType->isValid()) {

            $em->persist($trip);
            $em->flush();
        }

        return $this->render("trip/add.html.twig", [
            'tripType' => $tripType->createView(),
            'tripOrganizer' => $organizer,
            'tripPlaces' => $places
        ]);
    }

    /**
     * @Route("/trip/registerParticipant/{id}", name="trip_register_participant")
     * @param $tripId
     */
    public function addParticipant($tripId) {
        $trip = $this->getDoctrine()->getRepository(Trip::class)->find($tripId);
        $trip->addParticipant($this->getUser());
    }

    /**
     * @Route("/trip/removeParticipant/{id}", name ="remove_register_participant")
     * @param $tripId
     */
    public function removeParticipant($tripId) {
        $trip = $this->getDoctrine()->getRepository(Trip::class)->find($tripId);
        $trip->removeParticipant($this->getUser());
    }
}
