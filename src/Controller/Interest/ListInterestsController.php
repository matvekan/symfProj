<?php

declare(strict_types=1);

namespace App\Controller\Interest;

use App\DTO\Output\Interest\InterestOutputDTO;
use App\Entity\Interest;
use App\Repository\Contract\InterestRepositoryInterface;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

final class ListInterestsController extends AbstractController
{
    public function __construct(private InterestRepositoryInterface $interestRepository)
    {
    }

    #[Route('/api/interests', name: 'interests_index', methods: ['GET'])]
    #[OA\Tag(name: 'Interest')]
    #[OA\Response(
        response: 200,
        description: 'Interests list',
        content: new OA\JsonContent(type: 'array', items: new OA\Items(ref: new Model(type: InterestOutputDTO::class)))
    )]
    #[OA\Response(response: 401, description: 'Unauthorized')]
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
