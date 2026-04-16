<?php

declare(strict_types=1);

namespace App\Command;

use App\DTO\Input\User\StoreUserInputDTO;
use App\DTO\Request\Admin\Interest\CreateAdminInterestRequestDto;
use App\Repository\Contract\InterestRepositoryInterface;
use App\Repository\Contract\UserRepositoryInterface;
use App\Service\Admin\Interest\CreateAdminInterestHandler;
use App\Service\UserService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:seed-demo-data', description: 'Create demo admin/user and interests')]
final class SeedDemoDataCommand extends Command
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private InterestRepositoryInterface $interestRepository,
        private UserService $userService,
        private CreateAdminInterestHandler $createAdminInterestHandler,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $footballId = $this->ensureInterest('football');
        $musicId = $this->ensureInterest('music');
        $this->ensureInterest('programming');

        $this->ensureUser(
            name: 'Admin',
            email: 'admin@example.com',
            role: 'ROLE_ADMIN',
            password: 'admin123',
            interestIds: [$footballId],
        );

        $this->ensureUser(
            name: 'User',
            email: 'user@example.com',
            role: 'ROLE_USER',
            password: 'user123',
            interestIds: [$musicId],
        );

        $output->writeln('Seed completed: admin@example.com/admin123 and user@example.com/user123');

        return Command::SUCCESS;
    }

    private function ensureInterest(string $name): int
    {
        $existing = $this->interestRepository->findByName($name);
        if ($existing !== null) {
            return (int) $existing->getId();
        }

        $created = $this->createAdminInterestHandler->handle(new CreateAdminInterestRequestDto($name));

        return (int) $created->getId();
    }

    /**
     * @param array<int, int> $interestIds
     */
    private function ensureUser(
        string $name,
        string $email,
        string $role,
        string $password,
        array $interestIds,
    ): void {
        if ($this->userRepository->findOneByEmail($email) !== null) {
            return;
        }

        $this->userService->store(new StoreUserInputDTO(
            name: $name,
            email: $email,
            password: $password,
            role: $role,
            createdAt: new \DateTimeImmutable(),
            interestIds: $interestIds,
        ));
    }
}
