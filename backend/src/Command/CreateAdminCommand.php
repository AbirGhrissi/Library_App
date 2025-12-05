<?php

namespace App\Command;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:create-admin',
    description: 'Create an admin user',
)]
class CreateAdminCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $passwordHasher
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $helper = $this->getHelper('question');

        $io->title('Create Admin User');

        $emailQuestion = new Question('Email: ', 'admin@library.com');
        $email = $helper->ask($input, $output, $emailQuestion);

        $firstNameQuestion = new Question('First Name: ', 'Admin');
        $firstName = $helper->ask($input, $output, $firstNameQuestion);

        $lastNameQuestion = new Question('Last Name: ', 'User');
        $lastName = $helper->ask($input, $output, $lastNameQuestion);

        $passwordQuestion = new Question('Password: ', 'admin123');
        $passwordQuestion->setHidden(true);
        $password = $helper->ask($input, $output, $passwordQuestion);

        // Check if user already exists
        $existingUser = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $email]);
        if ($existingUser) {
            $io->error('A user with this email already exists!');
            return Command::FAILURE;
        }

        $user = new User();
        $user->setEmail($email);
        $user->setFirstName($firstName);
        $user->setLastName($lastName);
        $user->setRoles(['ROLE_ADMIN', 'ROLE_USER']);

        $hashedPassword = $this->passwordHasher->hashPassword($user, $password);
        $user->setPassword($hashedPassword);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $io->success('Admin user created successfully!');
        $io->table(
            ['Field', 'Value'],
            [
                ['Email', $email],
                ['First Name', $firstName],
                ['Last Name', $lastName],
                ['Roles', 'ROLE_ADMIN, ROLE_USER'],
            ]
        );

        return Command::SUCCESS;
    }
}
