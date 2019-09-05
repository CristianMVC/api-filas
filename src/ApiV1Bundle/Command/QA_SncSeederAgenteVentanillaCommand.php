<?php
namespace ApiV1Bundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use ApiV1Bundle\Entity\Agente;
use ApiV1Bundle\Entity\Ventanilla;

/**
 * Class QA_SncSeederAgenteVentanillaCommand
 * @package ApiV1Bundle\ApplicationServices
 *
 * Seeder que asigna a un agente una ventanilla
 *
 */
class QA_SncSeederAgenteVentanillaCommand extends ContainerAwareCommand
{
    /**
     * Configura el comando
     */
    protected function configure(){
        $this->setName('snc:seeder:agente:ventanilla');
        $this->setDescription('Seeder que asigna a un agente una ventanilla');
        $this->setHelp('Asigna a un agente una ventanilla');
        $this->addArgument('nombreAgente', InputArgument::REQUIRED);
        $this->addArgument('nombreVentanilla', InputArgument::REQUIRED);
    }

    /**
     * Ejecuta el comando
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output){
        $nombreAgente = $input->getArgument('nombreAgente');
        $nombreVentanilla = $input->getArgument('nombreVentanilla');

        $io = new SymfonyStyle($input, $output);
        $doctrine = $this->getContainer()->get('doctrine');
        $em = $doctrine->getManager();

        $agente = false;
        $agenteRepository = $em->getRepository('ApiV1Bundle:Agente');
        foreach ($agenteRepository->findAll() as $agenteData) {
            if($agenteData->getUsername() == $nombreAgente){
                $agente = $agenteData;
            }
        }
        
        $ventanilla = false;
        $ventanillaRepository = $em->getRepository('ApiV1Bundle:Ventanilla');
        foreach ($ventanillaRepository->findAll() as $ventanillaData) {
            if($ventanillaData->getIdentificador() == $nombreVentanilla){
                $ventanilla = $ventanillaData;
            }
        }

        if($agente && $ventanilla){

            $ventanillaAsiganada = false;
            foreach ($agente->getVentanillas() as $ventanillaAgente) {
                if($ventanillaAgente->getIdentificador() == $nombreVentanilla){
                    $ventanillaAsiganada = true;
                }  
            }

            if(!$ventanillaAsiganada){

                $agente->addVentanilla($ventanilla);
                $em->persist($agente);
                
                $io->text('Se relaciono: AGENTE con VENTANILLA');
                $io->text('     Nombre Agente: '.$agente->getNombre());
                $io->text('     Ventanilla: '.$ventanilla->getIdentificador());
                $io->text('');
                $em->flush();
            }
            else{
                $io->text('La VENTANILLA ya esta asignada al AGENTE:');
                $io->text('     Nombre Agente: '.$nombreAgente);
                $io->text('     Nombre Ventanilla: '.$nombreVentanilla);
                $io->text('');
            }

        }
        else{
            if(!$agente){
                $io->text('No se encuentra AGENTE:');
                $io->text('     Nombre Agente: '.$nombreAgente);
                $io->text('');
            }
            if(!$ventanilla){
                $io->text('No se encuentra VENTANILLA:');
                $io->text('     Nombre Ventanilla: '.$nombreVentanilla);
                $io->text('');
            }
        }
    }
}
