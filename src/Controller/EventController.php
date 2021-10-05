<?php

namespace App\Controller;

use App\Entity\Event;
use App\Form\EventType;
use App\Repository\EventRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class EventController extends AbstractController
{
    /**
     * @Route("/events", name="event")
     */
    public function index(EventRepository $eventRepository): Response
    {
        $events = $eventRepository->findAll();

        return $this->render('event/index.html.twig', [
            'events' => $events,
        ]);
    }

    /**
     * @Route("/events/add", name="event_add")
     */
    public function add(Request $request, EntityManagerInterface $entityManager)
    {
        $event = new Event();
        $addEventForm = $this->createForm(EventType::class, $event);

        $addEventForm->handleRequest($request);

        if($addEventForm->isSubmitted() && $addEventForm->isValid()) {
            $event = $addEventForm->getData();

            $entityManager->persist($event);
            $entityManager->flush();
        }

        return $this->render('event/add.html.twig', [
            'addEventForm' => $addEventForm->createView()
        ]);
    }

    /**
     * @Route("/events/{event}", name="event_show")
     */
    public function show(Event $event)
    {
        return  $this->render('event/show.html.twig', [
            'event' => $event
        ]);
    }
}
