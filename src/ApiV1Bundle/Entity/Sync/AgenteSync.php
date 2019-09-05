<?php
namespace ApiV1Bundle\Entity\Sync;

use ApiV1Bundle\Entity\Agente;
use ApiV1Bundle\Entity\User;
use ApiV1Bundle\Entity\Usuario;
use ApiV1Bundle\Entity\Validator\AgenteValidator;
use ApiV1Bundle\Entity\Validator\ValidateResultado;
use ApiV1Bundle\Repository\AgenteRepository;
use ApiV1Bundle\Repository\PuntoAtencionRepository;
use ApiV1Bundle\Repository\VentanillaRepository;
use ApiV1Bundle\Entity\Interfaces\UsuarioSyncInterface;

/**
 * Class AgenteSync
 * @package ApiV1Bundle\Entity\Sync
 */
class AgenteSync implements UsuarioSyncInterface
{
    /** @var \ApiV1Bundle\Entity\Validator\AgenteValidator $agenteValidator */
    private $agenteValidator;

    /** @var \ApiV1Bundle\Repository\AgenteRepository $agenteRepository  */
    private $agenteRepository;

    /** @var \ApiV1Bundle\Repository\VentanillaRepository $ventanillaRepository  */
    private $ventanillaRepository;

    /** @var \ApiV1Bundle\Repository\PuntoAtencionRepository $puntoAtencionRepository  */
    private $puntoAtencionRepository;

    /**
     * AgenteSync constructor
     *
     * @param AgenteValidator $agenteValidator
     * @param AgenteRepository $agenteRepository
     * @param VentanillaRepository $ventanillaRepository
     * @param PuntoAtencionRepository $puntoAtencionRepository
     */
    public function __construct(
        AgenteValidator $agenteValidator,
        AgenteRepository $agenteRepository,
        VentanillaRepository $ventanillaRepository,
        PuntoAtencionRepository $puntoAtencionRepository
    ) {
        $this->agenteValidator = $agenteValidator;
        $this->agenteRepository = $agenteRepository;
        $this->ventanillaRepository = $ventanillaRepository;
        $this->puntoAtencionRepository = $puntoAtencionRepository;
    }

    /**
     * Editar agente
     *
     * @param integer $id Identificador único del agente
     * @param array $params arreglo con los datos para editar el agente
     * @return ValidateResultado
     * @throws \Doctrine\ORM\ORMException
     */
    public function edit($id, $params)
    {
        $validateResultado = $this->agenteValidator->validarParams($params);
        if (! $validateResultado->hasError()) {
            $agente = $this->agenteRepository->findOneByUser($id);
            $user = $agente->getUser();

            $agente->setNombre($params['nombre']);
            $agente->setApellido($params['apellido']);

            $puntoAtencion = $this->puntoAtencionRepository->findOneByPuntoAtencionIdSnt($params['puntoAtencion']);
            $validateResultadoPA = $this->agenteValidator->validarPuntoAtencion($puntoAtencion);

            if ($validateResultadoPA->hasError()) {
                return $validateResultadoPA;
            }
            $agente->setPuntoAtencion($puntoAtencion);
            $ventanillas = isset($params['ventanillas']) ? $params['ventanillas'] : [];

            $validateVentanilla = $this->agenteValidator->validarVentanillasPuntoAtencion($params, $puntoAtencion);

            if ($validateVentanilla->hasError()) {
                return $validateVentanilla;
            }

            $validateResultado = $this->agenteValidator->validarVentanillasEliminar($ventanillas,$agente);
            if (! $validateResultado->hasError()) {
                $agente->removeAllVentanilla();
                foreach ($ventanillas as $idVentanilla) {
                    $ventanilla = $this->ventanillaRepository->find($idVentanilla);
                    $agente->addVentanilla($ventanilla);
                }
            }


            if (isset($params['username'])) {
                $user->setUsername($params['username']);
            }

            if (isset($params['password'])) {
                $user->setPassword($params['password']);
            }

            $validateResultado->setEntity($agente);
        }
        return $validateResultado;
    }

    /**
     * Borra un agente
     *
     * @param integer $id Identificador único del agente
     * @return ValidateResultado
     * @throws \Doctrine\ORM\ORMException
     */
    public function delete($id)
    {
        $agente = $this->agenteRepository->findOneByUser($id);
        $validateResultado = $this->agenteValidator->validarUsuario($agente);
        if (! $validateResultado->hasError()) {
            $agente->removeAllVentanilla();
            $validateResultado->setEntity($agente);
        }
        return $validateResultado;
    }

    /**
     * Asigna una ventanilla al Agente
     *
     * @param integer $idUser Identificador del usuario
     * @param integer $idVentanilla Identificador de la ventanilla
     * @return ValidateResultado
     * @throws \Doctrine\ORM\ORMException
     */
    public function asignarVentanilla($idUser, $idVentanilla)
    {
        $agente = $this->agenteRepository->findOneByUser($idUser);
        $ventanilla = $this->ventanillaRepository->find($idVentanilla);
        $cantidadAgentes = $this->agenteRepository->getCantidadDeAgentesAsociadosAVentanilla($idVentanilla);
        $validateResultado = $this->agenteValidator->validarAsignarVentanilla(
            $agente,
            $cantidadAgentes,
            $ventanilla
        );
        if (! $validateResultado->hasError()) {
            $agente->setVentanillaActual($ventanilla);
            $validateResultado->setEntity($agente);
            return $validateResultado;
        }
        return $validateResultado;
    }

    /**
     * Desasigna una ventanilla al Agente
     *
     * @param object $usuario
     * @return ValidateResultado
     */
    public function desasignarVentanilla($usuario)
    {
        $validateResultado = $this->agenteValidator->validarAgente(
            $usuario
        );

        if (! $validateResultado->hasError()) {
            if ($usuario->getUser()->getRol() == User::ROL_AGENTE) {
                $usuario->setVentanillaActual(null);
                $validateResultado->setEntity($usuario);
                return $validateResultado;
            }
        }

        return $validateResultado;
    }
}
