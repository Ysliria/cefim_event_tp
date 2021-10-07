<?php

namespace App\Controller;

use App\Repository\ParticipationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProfileController extends AbstractController
{
    /**
     * @Route("/profile", name="profile")
     */
    public function index(ParticipationRepository $participationRepository): Response
    {
        $participationsList = $participationRepository->findByUser($this->getUser());

        return $this->render('profile/index.html.twig', [
            'participation_list' => $participationsList
        ]);
    }
}
