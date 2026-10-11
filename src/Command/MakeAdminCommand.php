<?php

namespace App\Command;

use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Donne le rôle administrateur à un compte existant.
 * Usage : php bin/console app:make-admin adresse@exemple.fr
 */
#[AsCommand(name: 'app:make-admin', description: 'Donne le rôle administrateur à un utilisateur existant')]
final class MakeAdminCommand extends Command
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly EntityManagerInterface $entityManager,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('email', InputArgument::REQUIRED, "Adresse e-mail du compte");
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $email = (string) $input->getArgument('email');

        $user = $this->userRepository->findOneBy(['email' => $email]);
        if (null === $user) {
            $io->error(sprintf('Aucun compte avec l\'adresse %s. Inscrivez-vous d\'abord sur le site.', $email));

            return Command::FAILURE;
        }

        $user->setRoles(array_merge($user->getRoles(), ['ROLE_ADMIN']));
        $user->setIsActive(true);
        $this->entityManager->flush();

        $io->success(sprintf('%s est maintenant administrateur.', $email));

        return Command::SUCCESS;
    }
}
