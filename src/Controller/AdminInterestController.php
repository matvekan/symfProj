<?php

namespace App\Controller;

use App\Entity\Interest;
use App\Repository\InterestRepository;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/admin/interests')]
final class AdminInterestController extends AbstractController
{
    public function __construct(
        private InterestRepository $interestRepository,
        private CacheItemPoolInterface $cachePool,
    ) {
    }

    #[Route('', name: 'admin_interests_index', methods: ['GET'])]
    public function index(): JsonResponse
    {
        $interests = $this->interestRepository->findBy([], ['name' => 'ASC']);
        $data = array_map(static fn (Interest $interest) => [
            'id' => $interest->getId(),
            'name' => $interest->getName(),
        ], $interests);

        return $this->json($data);
    }

    #[Route('', name: 'admin_interests_store', methods: ['POST'])]
    public function store(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $name = is_array($data) ? trim((string) ($data['name'] ?? '')) : '';
        if ($name === '') {
            return $this->json(['error' => 'name is required.'], 422);
        }

        $existing = $this->interestRepository->findByName($name);
        if ($existing !== null) {
            return $this->json(['error' => 'Interest already exists.'], 409);
        }

        $interest = new Interest();
        $interest->setName($name);
        $this->interestRepository->store($interest);
        $this->cachePool->clear();

        return $this->json([
            'id' => $interest->getId(),
            'name' => $interest->getName(),
        ], 201);
    }
}
