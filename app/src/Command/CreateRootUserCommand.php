<?php

namespace App\Command;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:create-root',
    description: 'Add a short description for your command. php bin/console app:add-user <mot_de_passe>',
)]
class CreateRootUserCommand extends Command
{
    private UserPasswordHasherInterface $userPasswordHasher;
    private EntityManagerInterface $em;

    public function __construct(UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $em)
    {
        parent::__construct();
        $this->userPasswordHasher = $userPasswordHasher;
        $this->em = $em;
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Crée un utilisateur administrateur root avec un mot de passe fourni en argument.')
            ->addArgument('password', InputArgument::REQUIRED, 'Mot de passe du compte root')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $password = $input->getArgument('password');

        $user = new User();
        $user->setUsername('admin')
             ->setEmail('admin@admin.com')
             ->setPassword($this->userPasswordHasher->hashPassword($user, $password))
             ->setRoles(['ROLE_ADMIN']);

        $this->em->persist($user);
        $this->em->flush();

        $io->success('Utilisateur administrateur root créé avec succès !');
        return Command::SUCCESS;
    }
}
