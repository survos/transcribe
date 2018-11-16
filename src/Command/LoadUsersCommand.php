<?php

namespace App\Command;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class LoadUsersCommand extends Command
{
    protected static $defaultName = 'user:create';

    private $em;
    private $passwordEncoder;

    public function __construct(EntityManagerInterface $em, UserPasswordEncoderInterface $passwordEncoder, ?string $name = null)
    {
        parent::__construct($name);
        $this->em = $em;
        $this->passwordEncoder = $passwordEncoder;
    }

    protected function configure()
    {
        $this
            ->setDescription('Create a new user')
            ->addArgument('username', InputArgument::OPTIONAL, 'Username')
            ->addArgument('password', InputArgument::OPTIONAL, 'Plain Text Password')
            ->addOption('roles', null, InputOption::VALUE_OPTIONAL, 'Roles', 'ROLE_USER')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        if ($username = $input->getArgument('username'))
        {
            // check if it exists

        }

        $user = (new User())
            ->setUsername($username);

        // 3) Encode the password (you could also do this via Doctrine listener)
        $password = $this->passwordEncoder->encodePassword($user, $input->getArgument('password'));
        $user->setPassword($password);

        if ($roles = $input->getOption('roles'))
        {
            $user->setRoles(explode(',', $roles));
        }

        // 4) save the User!
        $entityManager = $this->em;
        $entityManager->persist($user);
        $entityManager->flush();


        $io->success("User $username created.");
    }
}
