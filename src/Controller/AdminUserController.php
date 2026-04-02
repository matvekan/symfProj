<?php

namespace App\Controller;

use App\Entity\User;
use App\Factory\UserFactory;
use App\Repository\UserRepository;
use App\Resource\UserResourse;
use App\ResponseBuilder\UserResponseBuilder;
use App\Service\UserService;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/admin/users')]
final class AdminUserController extends AbstractController
{
    public function __construct(
        private UserRepository $userRepository,
        private UserResponseBuilder $userResponseBuilder,
        private UserService $userService,
        private UserFactory $userFactory,
        private UserResourse $userResourse,
        private CacheItemPoolInterface $cachePool,
    ) {
    }

    #[Route('', name: 'admin_users_index', methods: ['GET'])]
    public function index(Request $request): JsonResponse
    {
        $page = max(1, (int) $request->query->get('page', 1));
        $limit = max(1, min(100, (int) $request->query->get('limit', 20)));

        $filters = [
            'name' => $request->query->get('name'),
            'email' => $request->query->get('email'),
            'role' => $request->query->get('role'),
            'createdFrom' => $request->query->get('createdFrom'),
            'createdTo' => $request->query->get('createdTo'),
            'interestName' => $request->query->get('interestName'),
            'interestIds' => $request->query->all('interestIds'),
        ];

        $cacheKey = 'admin_users_' . md5(json_encode([$filters, $page, $limit]));
        $cacheItem = $this->cachePool->getItem($cacheKey);
        if ($cacheItem->isHit()) {
            return new JsonResponse((string) $cacheItem->get(), 200, [], true);
        }

        $users = $this->userRepository->findByAdminFilters($filters, $page, $limit);
        $payload = $this->userResourse->userCollection($this->userFactory->makeUserOutputDTOs($users));

        $cacheItem->set($payload);
        $cacheItem->expiresAfter(300);
        $this->cachePool->save($cacheItem);

        return new JsonResponse($payload, 200, [], true);
    }

    #[Route('/{user}', name: 'admin_users_show', methods: ['GET'])]
    public function show(User $user): JsonResponse
    {
        return $this->userResponseBuilder->showUserResponse($user);
    }

    #[Route('/{user}', name: 'admin_users_update', methods: ['PATCH'])]
    public function update(User $user, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        if (!is_array($data)) {
            return $this->json(['error' => 'Invalid JSON body.'], 400);
        }

        $updated = $this->userService->updateUser($user, $data);

        return $this->userResponseBuilder->showUserResponse($updated);
    }
}
