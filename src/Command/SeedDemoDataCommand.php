<?php

namespace App\Command;

use App\Entity\Interest;
use App\Entity\User;
use App\Repository\Contract\InterestRepositoryInterface;
use App\Repository\Contract\UserRepositoryInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(name: 'app:seed-demo-data', description: 'Create demo admin/user and interests')]
class SeedDemoDataCommand extends Command
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private InterestRepositoryInterface $interestRepository,
        private UserPasswordHasherInterface $passwordHasher,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $interestNames = ['football', 'music', 'programming'];
        $interests = [];
        foreach ($interestNames as $name) {
            $interest = $this->interestRepository->findByName($name);
            if ($interest === null) {
                $interest = new Interest();
                $interest->setName($name);
                $this->interestRepository->store($interest);
            }
            $interests[] = $interest;
        }

        $admin = $this->userRepository->findOneByEmail('admin@example.com');
        if ($admin === null) {
            $admin = new User();
            $admin->setName('Admin');
            $admin->setEmail('admin@example.com');
            $admin->setRole('ROLE_ADMIN');
            $admin->setCreatedAt(new \DateTimeImmutable());
            $admin->setPassword($this->passwordHasher->hashPassword($admin, 'admin123'));
            $admin->addInterest($interests[0]);
            $this->userRepository->store($admin);
        }

        $user = $this->userRepository->findOneByEmail('user@example.com');
        if ($user === null) {
            $user = new User();
            $user->setName('User');
            $user->setEmail('user@example.com');
            $user->setRole('ROLE_USER');
            $user->setCreatedAt(new \DateTimeImmutable());
            $user->setPassword($this->passwordHasher->hashPassword($user, 'user123'));
            $user->addInterest($interests[1]);
            $this->userRepository->store($user);
        }

        $output->writeln('Seed completed: admin@example.com/admin123 and user@example.com/user123');

        return Command::SUCCESS;
    }
}
