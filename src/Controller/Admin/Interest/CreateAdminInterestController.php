<?php

declare(strict_types=1);

namespace App\Controller\Admin\Interest;

use App\DTO\Output\Interest\InterestOutputDTO;
use App\DTO\Request\Admin\Interest\CreateAdminInterestRequestDto;
use App\Service\Admin\Interest\CreateAdminInterestHandler;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

final class CreateAdminInterestController extends AbstractController
{
    public function __construct(
        private CreateAdminInterestHandler $createAdminInterestHandler,
    ) {
    }

    #[Route('/api/admin/interests', name: 'admin_interests_store', methods: ['POST'])]
    #[OA\Tag(name: 'Admin')]
    #[OA\RequestBody(required: true, content: new OA\JsonContent(ref: new Model(type: CreateAdminInterestRequestDto::class)))]
    #[OA\Response(response: 201, description: 'Interest created', content: new OA\JsonContent(ref: new Model(type: InterestOutputDTO::class)))]
    #[OA\Response(response: 409, description: 'Interest already exists')]
    #[OA\Response(response: 422, description: 'Validation error')]
    public function __invoke(#[MapRequestPayload] CreateAdminInterestRequestDto $requestDto): JsonResponse
    {
        try {
            $interest = $this->createAdminInterestHandler->handle($requestDto);
        } catch (\InvalidArgumentException $e) {
            return $this->json(['error' => $e->getMessage()], 422);
        } catch (\DomainException $e) {
            return $this->json(['error' => $e->getMessage()], 409);
        }

        return $this->json([
            'id' => $interest->getId(),
            'name' => $interest->getName(),
        ], 201);
    }
}
