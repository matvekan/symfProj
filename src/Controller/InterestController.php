<?php

namespace App\Controller;

use App\Entity\Interest;
use App\Repository\InterestRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

final class InterestController extends AbstractController
{
    public function __construct(private InterestRepository $interestRepository)
    {
    }

    #[Route('/api/interests', name: 'interests_index', methods: ['GET'])]
    public function index(): JsonResponse
    {
        $interests = $this->interestRepository->findBy([], ['name' => 'ASC']);
        $data = array_map(static fn (Interest $interest) => [
            'id' => $interest->getId(),
            'name' => $interest->getName(),
        ], $interests);

        return $this->json($data);
    }
}
