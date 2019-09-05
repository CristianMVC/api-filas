<?php
namespace ApiV1Bundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use ApiV1Bundle\Entity\Cola;
use ApiV1Bundle\Entity\PuntoAtencion;

/**
 * Class QA_SncSeederFilaCommand
 * @package ApiV1Bundle\ApplicationServices
 *
 *
 * Seeder que crea una cola / fila
 */
class QA_SncSeederFilaCommand extends ContainerAwareCommand
{
    /**
     * Configura el comando
     */
    protected function configure(){
        $this->setName('snc:seeder:qa:fila');
        $this->setDescription('Seeder que crea una cola / fila');
        $this->setHelp('Crea una cola / fila para QA');
        $this->addArgument('nombreCola', InputArgument::REQUIRED);
        $this->addArgument('nombrePda', InputArgument::REQUIRED);
        $this->addArgument('tipo', InputArgument::REQUIRED);
    }

    /**
     * Ejecuta el comando
     *
     * @param InputInterface $input
     * @param OutputInterface $output

        TIPO_GRUPO_TRAMITE = grupotramite
        TIPO_POSTA = posta

     */
    protected function execute(InputInterface $input, OutputInterface $output){

        $nombreCola = $input->getArgument('nombreCola');
        $nombrePda = $input->getArgument('nombrePda');
        $tipo = $input->getArgument('tipo');

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

        $filaExiste = false;
        $colaRepository = $em->getRepository('ApiV1Bundle:Cola');
        foreach ($colaRepository->findAll() as $colaData) {
            if($colaData->getNombre() == $nombreCola){
                $filaExiste = $colaData;
            }
        }

        if($puntoAtencion && !$filaExiste){
            $fila = new Cola($nombreCola, $puntoAtencion, $this->getTipo($tipo));
            $em->persist($fila);

            $io->text('Se genero: FILA');
            $io->text('     Nombre: '.$nombreCola);
            $io->text('     PuntoAtencion: '.$nombrePda);
            $io->text('     Tipo: '.$tipo);
            $io->text('');
            
            $em->flush();
        }
        else{
            if(!$puntoAtencion){
                $io->text('No Se encuentra el Punto de Atencion:  '.$nombrePda);
                $io->text('     Para la creacion de la Fila: '.$nombreCola);
                $io->text('');
            }
            if($filaExiste){
                $io->text('La FILA:  '.$nombreCola);
                $io->text('     Ya existe!');
                $io->text('');
            }
        }
    }


    private function getTipo($tipo){

        return $tipo == "recepcion" ? 1 : 2;
    }
}