<?php

namespace App\Controller;

use App\Entity\Challenge;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;


final class AppController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(): Response
    {
        return $this->render('app/index.html.twig');
    }

    #[Route('/register', name: 'app_register')] //cette ligne est l'attribut
    public function register(): Response
    {
        return $this->render('app/register.html.twig', [
            'controller_name' => 'ConferenceController',
        ]);
    }

    #[Route('/challenge', name: 'challenge')] //cette ligne est l'attribut
    public function dashboard(EntityManagerInterface $manager): Response
    {
        $challenges = $manager->getRepository(Challenge::class)->findAll();
        return $this->render('app/challenge.html.twig', [
            'challenges' => $challenges,
        ]);
    }
}
