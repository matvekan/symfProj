<?php

declare(strict_types=1);

namespace App\Controller\Admin\Interest;

use App\DTO\Request\Admin\Interest\CreateAdminInterestRequestDto;
use App\DTOValidator\RequestDtoValidator;
use App\Service\Admin\Interest\CreateAdminInterestHandler;
use App\Service\Http\JsonRequestParser;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class CreateAdminInterestController extends AbstractController
{
    public function __construct(
        private CreateAdminInterestHandler $createAdminInterestHandler,
        private JsonRequestParser $jsonRequestParser,
        private RequestDtoValidator $requestDtoValidator,
    )
    {
    }

    #[Route('/api/admin/interests', name: 'admin_interests_store', methods: ['POST'])]
    public function __invoke(Request $request): JsonResponse
    {
        try {
            $data = $this->jsonRequestParser->parse($request);
        } catch (\InvalidArgumentException $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }

        $requestDto = CreateAdminInterestRequestDto::fromArray($data);
        $this->requestDtoValidator->validate($requestDto);

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
