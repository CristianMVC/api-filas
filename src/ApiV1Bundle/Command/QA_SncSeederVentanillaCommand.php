<?php
namespace ApiV1Bundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use ApiV1Bundle\Entity\Cola;
use ApiV1Bundle\Entity\PuntoAtencion;
use ApiV1Bundle\Entity\Ventanilla;

/**
 * Class QA_SncSeederVentanillaCommand
 * @package ApiV1Bundle\ApplicationServices
 * 
 * Seeder que crea una ventanilla
 * 
 */
class QA_SncSeederVentanillaCommand extends ContainerAwareCommand
{
    /**
     * Configura el comando
     */
    protected function configure(){
        $this->setName('snc:seeder:qa:ventanilla');
        $this->setDescription('Seeder que crea una ventanilla');
        $this->setHelp('Crea una ventanilla para QA');
        $this->addArgument('nombreVentanilla', InputArgument::REQUIRED);
        $this->addArgument('nombrePda', InputArgument::REQUIRED);
        $this->addArgument('nombreFila', InputArgument::REQUIRED);
    }

    /**
     * Ejecuta el comando
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output){

        $nombreVentanilla = $input->getArgument('nombreVentanilla');
        $nombrePda = $input->getArgument('nombrePda');
        $nombreFila = $input->getArgument('nombreFila');

        $io = new SymfonyStyle($input, $output);
        $doctrine = $this->getContainer()->get('doctrine');
        $em = $doctrine->getManager();

        
        $puntoAtencion = false;
        $pdaRepository = $em->getRepository('ApiV1Bundle:PuntoAtencion');
        foreach ($pdaRepository->findAll() as $pdaData) {
            if($pdaData->getNombre() == $nombrePda){
                $puntoAtencion = $pdaData;
            }
        }

        $fila = false;
        $colaRepository = $em->getRepository('ApiV1Bundle:Cola');
        foreach ($colaRepository->findAll() as $colaData) {
            if($colaData->getNombre() == $nombreFila){
                $fila = $colaData;
            }
        }

        $ventanillaExiste = false;
        $ventanillaRepository = $em->getRepository('ApiV1Bundle:Ventanilla');
        foreach ($ventanillaRepository->findAll() as $ventanillaData) {
            if($ventanillaData->getIdentificador() == $nombreVentanilla){
                $ventanillaExiste = $ventanillaData;
            }
        }

        // validacion para la ventanilla borrada
        $crearVentanilla = !$ventanillaExiste || $ventanillaExiste->getFechaBorrado() !== NULL;

        if($puntoAtencion && $crearVentanilla && $fila){
                
            $ventanilla = new Ventanilla($nombreVentanilla, $puntoAtencion);
            $ventanilla->addCola($fila);
            $em->persist($ventanilla);

            $io->text('Se genero: VENTANILLA');
            $io->text('     Nombre: '.$nombreVentanilla);
            $io->text('     PuntoAtencion: '.$nombrePda);
            $io->text('     Cola / Fila: '.$fila->getNombre());
            $io->text('');
            
            $em->flush();
        }
        else{
            if(!$puntoAtencion){
                $io->text('No Se encuentra el Punto de Atencion:  '.$nombrePda);
                $io->text('     Para la creacion de la VENTANILLA: '.$nombreVentanilla);
                $io->text('');
            }
            if(!$fila){
                $io->text('La FILA:  '.$nombreFila);
                $io->text('     No existe!');
                $io->text('');
            }
            if($ventanillaExiste){
                $io->text('La VENTANILLA:  '.$nombreVentanilla);
                $io->text('     Ya existe!');
                $io->text('');
            }
        }
    }

    private function getTipo($tipo){

        return $tipo == "recepcion" ? 1 : 2;
    }
}