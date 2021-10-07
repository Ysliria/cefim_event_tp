<?php

namespace App\Controller;

use App\Entity\Event;
use App\Entity\Participation;
use App\Form\EventEvaluationType;
use App\Form\EventType;
use App\Form\ParticipationType;
use App\Repository\EventRepository;
use App\Repository\ParticipationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
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
     * @IsGranted("ROLE_CONTRIBUTOR")
     */
    public function add(Request $request, EntityManagerInterface $entityManager)
    {
        $event        = new Event();
        $addEventForm = $this->createForm(EventType::class, $event);

        $addEventForm->handleRequest($request);

        if ($addEventForm->isSubmitted() && $addEventForm->isValid()) {
            $event = $addEventForm->getData();

            $entityManager->persist($event);
            $entityManager->flush();

            return $this->redirectToRoute('event_show', [
                'event' => $event->getId()
            ]);
        }

        return $this->render('event/add.html.twig', [
            'addEventForm' => $addEventForm->createView()
        ]);
    }

    /**
     * @Route("/events/{event}/update", name="event_update")
     * @IsGranted("ROLE_CONTRIBUTOR")
     */
    public function update(Event $event, Request $request, EntityManagerInterface $entityManager)
    {
        $updateEventForm = $this->createForm(EventType::class, $event);

        $updateEventForm->handleRequest($request);

        if ($updateEventForm->isSubmitted() && $updateEventForm->isValid()) {
            $entityManager->flush();
        }

        return $this->render('event/update.html.twig', [
            'updateEventForm' => $updateEventForm->createView(),
            'event'           => $event
        ]);
    }

    /**
     * @Route("/events/{event}/delete", name="event_delete")
     * @IsGranted("ROLE_ADMIN")
     */
    public function delete(Event $event, EntityManagerInterface $entityManager)
    {
        $deleteMessage = $event->getName() . ' a bien été supprimé !';

        $entityManager->remove($event);
        $entityManager->flush();

        $this->addFlash('success', $deleteMessage);

        return $this->redirectToRoute('event');
    }

    /**
     * @Route("/events/{event}", name="event_show")
     */
    public function show(Event $event, ParticipationRepository $participationRepository)
    {
        $evaluations = $participationRepository->findByEvent($event);

        return $this->render('event/show.html.twig', [
            'event' => $event,
            'evaluations' => $evaluations
        ]);
    }

    /**
     * @Route("/events/{event}/participate", name="event_participate")
     * @IsGranted("ROLE_USER")
     */
    public function participate(Event $event, Request $request, EntityManagerInterface $entityManager)
    {
        $participation = new Participation();
        $participation->setEvent($event)->setUser($this->getUser());

        $eventParticipationForm = $this->createForm(ParticipationType::class, $participation);

        $eventParticipationForm->handleRequest($request);

        if ($eventParticipationForm->isSubmitted() && $eventParticipationForm->isValid()) {
            $participation = $eventParticipationForm->getData();

            $entityManager->persist($participation);
            $entityManager->flush();
        }

        return $this->render('event/participate.html.twig', [
            'event' => $event,
            'eventParticipationForm' => $eventParticipationForm->createView()
        ]);
    }

    /**
     * @Route("/events/{event}/evaluate", name="event_evaluate")
     * @IsGranted("ROLE_USER")
     */
    public function evaluate(Event $event, EntityManagerInterface $entityManager, Request $request, ParticipationRepository $participationRepository)
    {
        $participation = $participationRepository->findOneBy([
            'user' => $this->getUser(),
            'event' => $event]);

        $eventEvaluateForm = $this->createForm(EventEvaluationType::class, $participation);

        $eventEvaluateForm->handleRequest($request);

        if($eventEvaluateForm->isSubmitted() && $eventEvaluateForm->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('event_show', ['event' => $event->getId()]);
        }


        return $this->render('event/evaluate.html.twig', [
            'eventEvaluateForm' => $eventEvaluateForm->createView()
        ]);
    }
}
