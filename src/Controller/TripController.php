<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Entity\Trip;
use App\Entity\TripPlace;
use App\Entity\TripStatus;
use App\Form\TripType;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
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
        $trips = $this->getDoctrine()->getRepository(Trip::class)->findAllNotClosed();
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
        $parameterBag->add(['userId' => $this->getUser()->getId()]);
        
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
        $places = $em->getRepository(TripPlace::class)->findAll();

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

            return $this->redirectToRoute('trip');
        }

        return $this->render("trip/add.html.twig", [
            'tripType' => $tripType->createView(),
            'tripOrganizer' => $organizer,
            'places' => $places
        ]);
    }

    /**
     * @Route("/trip/add-participant/{tripId}", name="trip_add_participant")
     * @method Participant getUser()
     * @param Request $request
     * @param EntityManagerInterface $em
     * @return RedirectResponse
     * @throws Exception
     */
    public function addParticipant(Request $request, EntityManagerInterface $em) {
        $trip = $this->getDoctrine()->getRepository(Trip::class)->find($request->attributes->get('tripId'));

        if (sizeof($trip->getParticipants()) < $trip->getMaxRegistrationNumber()
            and !$trip->getParticipants()->contains($this->getUser())
            and new \DateTime() < $trip->getDeadlineDate()
            and ($trip->getStatus()->getId() == TripStatus::OPEN)) {
            $trip->addParticipant($this->getUser());

            if (sizeof($trip->getParticipants()) == $trip->getMaxRegistrationNumber()) {
                $trip->setStatus($this->getDoctrine()->getRepository(TripStatus::class)->find(TripStatus::FULL));
            }

            $em->persist($trip);
            $em->flush();
        }

        return $this->redirectToRoute('trip');
    }

    /**
     * @Route("/trip/rem-participant/{tripId}", name ="trip_remove_participant")
     * @param Request $request
     * @param EntityManagerInterface $em
     * @return RedirectResponse
     */
    public function removeParticipant(Request $request, EntityManagerInterface $em) {
        $trip = $this->getDoctrine()->getRepository(Trip::class)->find($request->attributes->get('tripId'));
        if ($trip->getParticipants()->contains($this->getUser())
            and $trip->getOrganizer() != $this->getUser()
            and ($trip->getStatus()->getId() == TripStatus::OPEN or $trip->getStatus()->getId() == TripStatus::FULL)) {
            $trip->removeParticipant($this->getUser());

            if ( $trip->getStatus()->getId() == TripStatus::FULL) {
                $trip->setStatus($this->getDoctrine()->getRepository(TripStatus::class)->find(TripStatus::OPEN));
            }

            $em->persist($trip);
            $em->flush();
        }
        return $this->redirectToRoute('trip');
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
     * @Route("/trip/action/{tripId}/{action}", name="trip_actions")
     * @param Request $request
     * @param EntityManagerInterface $em
     * @return RedirectResponse
     */
    public function actions(Request $request,EntityManagerInterface $em) {
        $trip = $this->getDoctrine()->getRepository(Trip::class)->find($request->attributes->get('tripId'));

        switch ($request->attributes->get('action')) {
            case 'publish' :
                $status = $this->getDoctrine()->getRepository(TripStatus::class)->find(TripStatus::OPEN);
                $trip->setStatus($status);
                break;
            case 'cancel' :

                $status = $this->getDoctrine()->getRepository(TripStatus::class)->find(TripStatus::CANCELED);
                if ($trip->getStatus()->getId() != TripStatus::CANCELED) {
                    $trip->setStatus($status);
                }
                break;

            default:
                throw $this->createNotFoundException("L'url demandée n'est pas attribuée.");
        }

        $em->persist($trip);
        $em->flush();

        return $this->redirectToRoute('trip');
    }

    /**
     * @Route("trip/details/{tripId}", name="trip_details")
     * @param Request $request
     * @return Response
     */
    public function details(Request $request) {
        $trip = $this->getDoctrine()->getRepository(Trip::class)->find($request->attributes->get('tripId'));

        return $this->render("trip/details.html.twig", [
            'trip' => $trip
        ]);
    }
}
