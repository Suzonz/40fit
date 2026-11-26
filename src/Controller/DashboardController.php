<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\EvaluationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Le tableau de bord : pour chaque défi tenté, le dernier résultat, le meilleur,
 * l'évolution depuis le premier essai et l'objectif atteint ou non.
 */
#[IsGranted('ROLE_USER')]
final class DashboardController extends AbstractController
{
    #[Route('/dashboard', name: 'app_dashboard')]
    public function dashboard(EvaluationRepository $evaluationRepository): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $evaluations = $evaluationRepository->findByUser($user); // du plus récent au plus ancien

        // Un résumé par défi : le premier résultat lu est le plus récent
        $summaries = [];
        foreach ($evaluations as $evaluation) {
            $challenge = $evaluation->getChallenge();
            $id = $challenge->getId();
            $summaries[$id] ??= ['challenge' => $challenge, 'latest' => $evaluation, 'best' => $evaluation, 'first' => $evaluation, 'count' => 0];
            if ($challenge->isBetter($evaluation->getValue(), $summaries[$id]['best']->getValue())) {
                $summaries[$id]['best'] = $evaluation;
            }
            $summaries[$id]['first'] = $evaluation; // le dernier lu est le plus ancien
            $summaries[$id]['count']++;
        }

        $reached = count(array_filter($summaries, fn (array $s) => $s['best']->getProgress() >= 100));

        return $this->render('app/dashboard.html.twig', [
            'summaries' => $summaries,
            'evaluationCount' => count($evaluations),
            'reachedCount' => $reached,
        ]);
    }
}
