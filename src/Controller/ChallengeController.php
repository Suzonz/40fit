<?php

namespace App\Controller;

use App\Entity\Challenge;
use App\Entity\Evaluation;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Form\EvaluationReportType as FormEvaluationReportType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;


#[Route('/challenge', name: 'challenge_')]
final class ChallengeController extends AbstractController
{
    #[Route('/{id}', name: 'show')]
    public function show(int $id, EntityManagerInterface $manager, Request $request): Response
    {
        $challenge = $manager->getRepository(Challenge::class)->find($id);
        $user = $this->getUser();
        
        if (!$challenge) {
            throw $this->createNotFoundException("Challenge #{$id} introuvable");
        }

        $evaluation = new Evaluation();
        $evaluation->setChallenge($challenge);
        $evaluation->setIdUser($user);

        $form = $this->createForm(FormEvaluationReportType::class, $evaluation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $manager->persist($evaluation);
            $manager->flush();


            return $this->redirectToRoute('challenge_show', [
                'id' => $challenge->getId()
            ]);
        }

        return $this->render('challenge/show.html.twig', [
            'challenge' => $challenge,
            'evaluationReportForm' => $form,

        ]);
    }
}
