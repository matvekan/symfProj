<?php

declare(strict_types=1);

namespace App\Controller\Interest;

use App\Entity\Interest;
use App\Repository\Contract\InterestRepositoryInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

final class ListInterestsController extends AbstractController
{
    public function __construct(private InterestRepositoryInterface $interestRepository)
    {
    }

    #[Route('/api/interests', name: 'interests_index', methods: ['GET'])]
    public function __invoke(): JsonResponse
    {
        $interests = $this->interestRepository->findAllOrderedByName();
        $data = array_map(static fn (Interest $interest) => [
            'id' => $interest->getId(),
            'name' => $interest->getName(),
        ], $interests);

        return $this->json($data);
    }
}
