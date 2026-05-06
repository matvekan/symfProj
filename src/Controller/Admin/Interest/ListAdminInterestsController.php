<?php

declare(strict_types=1);

namespace App\Controller\Admin\Interest;

use App\DTO\Output\Interest\InterestOutputDTO;
use App\Service\Admin\Interest\ListAdminInterestsFetcher;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

final class ListAdminInterestsController extends AbstractController
{
    public function __construct(private ListAdminInterestsFetcher $listAdminInterestsFetcher)
    {
    }

    #[Route('/api/admin/interests', name: 'admin_interests_index', methods: ['GET'])]
    #[OA\Tag(name: 'Admin')]
    #[OA\Response(
        response: 200,
        description: 'Interests list',
        content: new OA\JsonContent(type: 'array', items: new OA\Items(ref: new Model(type: InterestOutputDTO::class)))
    )]
    #[OA\Response(response: 403, description: 'Forbidden')]
    public function __invoke(): JsonResponse
    {
        return $this->json($this->listAdminInterestsFetcher->fetch());
    }
}
