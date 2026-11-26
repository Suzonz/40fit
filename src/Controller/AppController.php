<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * L'accueil. L'inscription est dans RegistrationController, la connexion dans
 * SecurityController, les défis dans ChallengeController.
 */
final class AppController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(): Response
    {
        return $this->render('app/index.html.twig');
    }

    /** Ancienne adresse de la liste des défis. */
    #[Route('/challenge', name: 'challenge')]
    public function challengeList(): Response
    {
        return $this->redirectToRoute('challenge_index', [], Response::HTTP_MOVED_PERMANENTLY);
    }
}
