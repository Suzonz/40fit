<?php

namespace App\Controller;

use App\Entity\Challenge;
use App\Entity\Evaluation;
use App\Entity\User;
use App\Form\ChallengeFilterType;
use App\Form\EvaluationFormType;
use App\Repository\ChallengeCategoryRepository;
use App\Repository\ChallengeRepository;
use App\Repository\EvaluationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Les défis et les résultats de l'utilisateur connecté.
 */
#[Route('/user/challenge', name: 'challenge_')]
#[IsGranted('ROLE_USER')]
final class ChallengeController extends AbstractController
{
    /** En session : l'id de la catégorie choisie dans le filtre de la liste. */
    private const FILTER_SESSION_KEY = 'challenge-filter-category-id';

    /**
     * La liste des défis, rangés par catégorie, avec un filtre par catégorie.
     * Le choix du filtre est gardé en session : en revenant sur la liste, il est toujours appliqué.
     */
    #[Route('/', name: 'index')]
    public function index(
        Request $request,
        ChallengeRepository $challengeRepository,
        ChallengeCategoryRepository $challengeCategoryRepository,
    ): Response {
        $session = $request->getSession();
        $categoryId = $session->get(self::FILTER_SESSION_KEY);
        $category = $categoryId ? $challengeCategoryRepository->find($categoryId) : null;

        $filterForm = $this->createForm(ChallengeFilterType::class, ['category' => $category]);
        $filterForm->handleRequest($request);

        if ($filterForm->isSubmitted() && $filterForm->isValid()) {
            $category = $filterForm->get('category')->getData();
            $session->set(self::FILTER_SESSION_KEY, $category?->getId());
        }

        $byCategory = [];
        foreach ($challengeRepository->findByCategory($category) as $challenge) {
            $byCategory[$challenge->getCategory()->getName()][] = $challenge;
        }

        return $this->render('challenge/index.html.twig', [
            'filterForm' => $filterForm,
            'challengesByCategory' => $byCategory,
        ]);
    }

    /** Un défi : sa description, son objectif et mes résultats. */
    #[Route('/{id}', name: 'show', requirements: ['id' => '\d+'])]
    public function show(Challenge $challenge, EvaluationRepository $evaluationRepository): Response
    {
        return $this->render('challenge/show.html.twig', [
            'challenge' => $challenge,
            'evaluations' => $evaluationRepository->findByUserAndChallenge($this->currentUser(), $challenge),
        ]);
    }

    #[Route('/{id}/evaluations/create', name: 'evaluations_create', requirements: ['id' => '\d+'])]
    public function evaluationCreate(Challenge $challenge, Request $request, EntityManagerInterface $entityManager): Response
    {
        $evaluation = (new Evaluation())
            ->setAssociatedUser($this->currentUser())
            ->setChallenge($challenge);

        $evaluationForm = $this->createForm(EvaluationFormType::class, $evaluation);

        $evaluationForm->handleRequest($request);
        if ($evaluationForm->isSubmitted() && $evaluationForm->isValid()) {
            $entityManager->persist($evaluation);
            $entityManager->flush();
            $this->addFlash('success', 'Résultat enregistré.');

            return $this->redirectToRoute('challenge_show', ['id' => $challenge->getId()]);
        }

        return $this->render('challenge/evaluation/create.html.twig', [
            'challenge' => $challenge,
            'evaluationForm' => $evaluationForm,
        ]);
    }

    #[Route('/{id}/evaluations/{evaluationId}/update', name: 'evaluations_update', requirements: ['id' => '\d+', 'evaluationId' => '\d+'])]
    public function evaluationUpdate(Challenge $challenge, #[MapEntity(id: 'evaluationId')] Evaluation $evaluation, Request $request, EntityManagerInterface $entityManager): Response
    {
        $this->denyUnlessOwnEvaluation($challenge, $evaluation);

        $evaluationForm = $this->createForm(EvaluationFormType::class, $evaluation);

        $evaluationForm->handleRequest($request);
        if ($evaluationForm->isSubmitted() && $evaluationForm->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'Résultat modifié.');

            return $this->redirectToRoute('challenge_show', ['id' => $challenge->getId()]);
        }

        return $this->render('challenge/evaluation/update.html.twig', [
            'challenge' => $challenge,
            'evaluation' => $evaluation,
            'evaluationForm' => $evaluationForm,
        ]);
    }

    #[Route('/{id}/evaluations/{evaluationId}/delete', name: 'evaluations_delete', requirements: ['id' => '\d+', 'evaluationId' => '\d+'], methods: ['POST'])]
    public function evaluationDelete(Challenge $challenge, #[MapEntity(id: 'evaluationId')] Evaluation $evaluation, Request $request, EntityManagerInterface $entityManager): Response
    {
        $this->denyUnlessOwnEvaluation($challenge, $evaluation);

        if ($this->isCsrfTokenValid('delete-evaluation-' . $evaluation->getId(), $request->request->getString('_token'))) {
            $entityManager->remove($evaluation);
            $entityManager->flush();
            $this->addFlash('success', 'Résultat supprimé.');
        }

        return $this->redirectToRoute('challenge_show', ['id' => $challenge->getId()]);
    }

    /** Un résultat ne se modifie (ou se supprime) que par son auteur, et depuis son propre défi. */
    private function denyUnlessOwnEvaluation(Challenge $challenge, Evaluation $evaluation): void
    {
        if ($evaluation->getChallenge() !== $challenge || $evaluation->getAssociatedUser() !== $this->currentUser()) {
            throw $this->createNotFoundException('Résultat introuvable.');
        }
    }

    private function currentUser(): User
    {
        /** @var User $user */
        $user = $this->getUser();

        return $user;
    }
}
