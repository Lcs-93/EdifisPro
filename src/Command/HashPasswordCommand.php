<?php

namespace App\Command;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:create-user')]
class HashPasswordCommand extends Command
{
    private EntityManagerInterface $entityManager;
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
        $this->passwordHasher = $passwordHasher;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $user = new User();
        $user->setNom('admin');
        $user->setPrenom('prénom');
        $user->setEmail('admin@admin.com');
        $user->setRoles(['ROLE_USER']);

        // Hachage du mot de passe
        $hashedPassword = $this->passwordHasher->hashPassword($user, 'adminpassword');
        $user->setPassword($hashedPassword);

        // Sauvegarde en base
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $output->writeln('Utilisateur créé avec succès dans hackathon');//_test !');
        return Command::SUCCESS;
    }
}

