<?php

declare(strict_types=1);

namespace App\Controller\Admin\Interest;

use App\Service\Admin\Interest\ListAdminInterestsFetcher;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

final class ListAdminInterestsController extends AbstractController
{
    public function __construct(private ListAdminInterestsFetcher $listAdminInterestsFetcher)
    {
    }

    #[Route('/api/admin/interests', name: 'admin_interests_index', methods: ['GET'])]
    public function __invoke(): JsonResponse
    {
        return $this->json($this->listAdminInterestsFetcher->fetch());
    }
}
