<?php

namespace App\Command;

use App\Entity\Interest;
use App\Entity\User;
use App\Factory\UserFactory;
use App\ResponseBuilder\UserResponseBuilder;
use App\Service\UserService;
use App\DTOValidator\UserDTOValidator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'go',
    description: 'Add a short description for your command',
)]
class GoCommand extends Command
{
    public function __construct(
        private UserService            $userService,
        private UserDTOValidator       $userValidator,
        private UserResponseBuilder    $userResponseBuilder,
        private UserFactory            $userFactory,
    )
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $data=[
            'name'=>"igorNew",
            'email'=>"igorNew@gmail.com",
            'password'=>"123",
            'role'=>"user",
            'created_at'=>"2026-03-28",
            'interestIds'=>[1],
        ];

        $storeUserInputDTO= $this->userFactory->makeStoreUserInputDTO($data);
        $this->userValidator->validate($storeUserInputDTO);

        $user= $this->userService->store($storeUserInputDTO);

        $res = $this->userResponseBuilder->storeUserResponse($user);
        dd($res);
        return Command::SUCCESS;
    }
}



