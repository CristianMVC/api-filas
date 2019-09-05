<?php
namespace ApiV1Bundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use ApiV1Bundle\Entity\PuntoAtencion;
use ApiV1Bundle\Entity\User;
use ApiV1Bundle\Entity\Agente;


/**
 * Class SncSeederUserAgenteCommand
 * @package ApiV1Bundle\ApplicationServices
 *
 * Seeder que crea un usuario agente
 *
 */
class SncSeederUserAgenteCommand extends ContainerAwareCommand
{
    private $nombrePda; 
    private $puntoAtencion = false;
    /**
     * Configura el comando
     */
    protected function configure(){
        $this->setName('snc:seeder:user:agente');
        $this->setDescription('Seeder que crea un usuario agente');
        $this->setHelp('Crea usuario agente');
        $this->addArgument('usuario', InputArgument::REQUIRED);
        $this->addArgument('pass', InputArgument::REQUIRED);
        $this->addArgument('nombre', InputArgument::REQUIRED);
        $this->addArgument('apellido', InputArgument::REQUIRED);
        $this->addArgument('nombrePda', InputArgument::REQUIRED);
    }

    /**
     * Ejecuta el comando
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output){
        $usuario = $input->getArgument('usuario');
        $pass = $input->getArgument('pass');
        $nombre = $input->getArgument('nombre');
        $apellido = $input->getArgument('apellido');
        $this->nombrePda = $input->getArgument('nombrePda');

        $io = new SymfonyStyle($input, $output);
        $doctrine = $this->getContainer()->get('doctrine');
        $em = $doctrine->getManager();

        $container = $this->getContainer();
        $encoder = $container->get('security.password_encoder');

        $rol = USER::ROL_AGENTE;

        $pdaRepository = $em->getRepository('ApiV1Bundle:PuntoAtencion');
        foreach ($pdaRepository->findAll() as $pdaData) {
            if($pdaData->getNombre() == $this->nombrePda){
                $this->puntoAtencion = $pdaData;
            }
        }

        if (!$em->getRepository('ApiV1Bundle:User')->findOneByUsername($usuario) && $this->puntoAtencion){

            $user = new User($usuario, $rol);
            $user->setPassword($encoder->encodePassword($user, $pass));
            $em->persist($user);

            $userRol = new Agente($nombre, $apellido, $this->puntoAtencion, $user);
            $em->persist($userRol);

            $this->printUserCreate($io, $usuario, $pass, false);
        }
        else{
            $this->printUserCreate($io, $usuario, $pass, true);
        }

        $em->flush();
    }

    protected function printUserCreate($io, $user, $pass, $exist){

        
        if($exist){
            $io->text('El USUARIO: '.$user.' ya existe.');
            $io->text('     rol: ROL_AGENTE');
        }
        else{
            $io->text('Se genero: USUARIO ROL_AGENTE');
            $io->text('     usuario: '.$user);
        }
        $io->text('     password: '.$pass);
        $io->text('');
        if(!$this->puntoAtencion){
            $io->text('El PUNTO DE ATENCION NO EXISTE: '.$this->nombrePda.' NO existe.');
            $io->text('     rol: ROL_AGENTE');
        }
    }
}
