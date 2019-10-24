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
     * @Route("/trip", name="trip", methods={"GET"})
     * @return Response
     */
    public function searchTrip()
    {
        $trips = $this->getDoctrine()->getRepository(Trip::class)->findAll();
        $places = $this->getDoctrine()->getRepository(TripPlace::class)->findAll();

        return $this->render("trip/index.html.twig", [
            'trips' => $trips,
            'places' => $places
        ]);
    }

    /**
     * @Route("/trip/search", name="trip_search", methods={"GET"})
     * @param Request $request
     * @return Response
     */
    public function searchTripWithFilter(Request $request)
    {
        // récupération des sorties publiées

        $parameterBag = $request->query;
        $parameterBag->add(['email' =>  $this->getUser()->getEmail()]); //ajout du mail de l'utilisateur pour la recherche de sorties dont je suis l'organisateur

        $trips = $this->getDoctrine()->getRepository(Trip::class)->findByFilter($parameterBag);
        $places = $this->getDoctrine()->getRepository(TripPlace::class)->findAll();

        return $this->render("trip/index.html.twig", [
            'trips' => $trips,
            'places' => $places
        ]);
    }

    /**
     * @Route("/trip/add/", name="trip_add")
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

        $status = $em->getRepository(TripStatus::class)->find(TripStatus::OPEN);

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
            'tripOrganizer' => $organizer
        ]);
    }

    /**
     * @Route("/trip/add-participant/{tripId}", name="trip_add_participant")
     * @param Request $request
     */
    public function addParticipant(Request $request) {
        $trip = $this->getDoctrine()->getRepository(Trip::class)->find($request->attributes->get('tripId'));
        $trip->addParticipant($this->getUser());
    }

    /**
     * @Route("/trip/rem-participant/{tripId}", name ="trip_remove_participant")
     * @param Request $request
     */
    public function removeParticipant(Request $request) {
        $trip = $this->getDoctrine()->getRepository(Trip::class)->find($request->attributes->get('tripId'));
        $trip->removeParticipant($this->getUser());
    }

    /**
     * @Route("/trip/edit/{tripId}", name="trip_edit")
     * @param Request $request
     * @param EntityManagerInterface $em
     * @return Response
     */
    public function edit(Request $request, EntityManagerInterface $em) {
        // récupération du trip
        $trip = $this->getDoctrine()->getRepository(Trip::class)->find($request->attributes->get('tripId'));

        // création du formulaire et association de la sortie au formulaire
        $tripType = $this->createForm(TripType::class, $trip);

        $tripType->handleRequest($request);
        if ($tripType->isSubmitted() && $tripType->isValid()) {

            $em->persist($trip);
            $em->flush();
        }

        return $this->render("trip/edit.html.twig", [
            'tripType' => $tripType->createView()
        ]);
    }

    /**
     * @Route("trip/cancel/{tripId}, name="trip_cancel")
     * @param Request $request
     */
    public function canceled(Request $request) {
        $status = $em->getRepository(TripStatus::class)->find(TripStatus::OPEN);
    }
}
