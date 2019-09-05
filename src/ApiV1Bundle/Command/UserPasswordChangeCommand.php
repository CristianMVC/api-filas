<?php
namespace ApiV1Bundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class UserPasswordChangeCommand
 * @package ApiV1Bundle\ApplicationServices
 */
class UserPasswordChangeCommand extends ContainerAwareCommand
{
    /**
     * Configura el comando
     */
    protected function configure()
    {
        $this->setName('snc:user:password');
        $this->setDescription('Cambio de contraseña de un usuario');
        $this->setHelp('Comando para cambiar la contraseña de un usuario');
        $this->addArgument('username', InputArgument::REQUIRED, 'Usuario que queremos cambiar la contraseña');
        $this->addArgument('password', InputArgument::OPTIONAL, 'Nueva contraseña');
    }

    /**
     * Ejecuta el comando
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $container = $this->getContainer();
        $encoder = $container->get('security.password_encoder');
        $em = $container->get('doctrine')->getManager();
        $userRepository = $em->getRepository('ApiV1Bundle:User');
        // username
        $username = $input->getArgument('username');
        // password
        $password = $input->getArgument('password');
        if (! $password) {
            $password = $this->randomPassword(12);
        }
        // find the user
        $user = $userRepository->findOneByUsername($username);
        if (! $user) {
            $output->writeln("El usuario {$username} no existe.");
            exit(1);
        }
        $output->writeln("Cambiar la contraseña de {$username}");
        // encode and update the password
        $user->setPassword($encoder->encodePassword($user, $password));
        $em->flush();
        $output->writeln("El nuevo password es {$password}");
    }

    /**
     * Generate a random password
     *
     * @param integer $len
     * @return string
     */
    private function randomPassword($len = 8)
    {
        $alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
        $pass = [];
        $alphaLength = strlen($alphabet) - 1;
        for ($i = 0; $i < $len; $i ++) {
            $n = rand(0, $alphaLength);
            $pass[] = $alphabet[$n];
        }
        return implode($pass);
    }
}