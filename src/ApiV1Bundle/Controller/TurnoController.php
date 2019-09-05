<?php
namespace ApiV1Bundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\Post;
use FOS\RestBundle\Controller\Annotations\Put;
use FOS\RestBundle\Controller\Annotations\Delete;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;

/**
 * Class TurnoController
 * @package ApiV1Bundle\ApplicationServices
 */
class TurnoController extends ApiController
{
    /** @var \ApiV1Bundle\ApplicationServices\TurnoServices $turnoServices */
    private $turnoServices;

    /**
     * Método de integración para guardar un turno
     * @ApiDoc(section="Turnos")
     * @param Request $request
     * @return mixed
     * @throws \Exception
     * @Post("/integration/turnos")
     */
    public function integrationPostAction(Request $request)
    {
        $params = $request->request->all();
        $this->turnoServices = $this->getTurnoServices();

        return $this->turnoServices->integrationCreate(
            $params,
            function ($turno) {
                return $this->respuestaOk('Turno guardado con éxito', [
                    'id' => $turno->getId()
                ]);
            },
            function ($err) {
                return $this->respuestaError($err);
            }
        );
    }

    /**
     * Guarda un Turno
     * @ApiDoc(section="Turnos")
     * @param Request $request Se envian los datos para guardar el turno
     * @return mixed
     * @throws \Exception
     * @Post("/turnos")
     */
    public function postAction(Request $request)
    {
        $params = $request->request->all();
        $this->turnoServices = $this->getTurnoServices();

        return $this->turnoServices->create(
            $params,
            function ($usuario) {
                return $this->respuestaOk('Turno guardado con éxito', [
                    'id' => $usuario->getId()
                ]);
            },
            function ($err) {
                return $this->respuestaError($err);
            }
        );
    }

    /**
     * Cambiar estado Turno
     * @ApiDoc(section="Turnos")
     * @param Request $request Se envian los datos para cambiar el estado del turno
     * @return mixed
     * @throws \Exception
     * @Post("/turnos/estado")
     */
    public function cambiarEstadoAction(Request $request)
    {
        $params = $request->request->all();
        $this->turnoServices = $this->getTurnoServices();

        return $this->turnoServices->changeStatus(
            $params,
            function ($usuario) {
                return $this->respuestaOk('Turno modificado con éxito', [
                    'id' => $usuario->getId()
                ]);
            },
            function ($err) {
                return $this->respuestaError($err);
            }
        );
    }

    /**
     * Listado de turnos del Sistema Nacional de Turnos
     * @ApiDoc(section="Turnos")
     * @param Request $request Espera el resultado de una petición como parámetro
     * @return mixed
     * @Get("/snt/turnos")
     */
    public function getTurnosSNTAction(Request $request)
    {
        $params = $request->query->all();
        $params['offset'] = (int) $request->get('offset', 0);
        $params['limit'] = (int) $request->get('limit', 10);
        $this->turnoServices = $this->getTurnoServices();
        return $this->turnoServices->getListTurnosSNT(
            $params,
            function ($err) {
                return $this->respuestaError($err);
            }
        );
    }

    /**
     * Busqueda de turnos por código en el Sistema Nacional de Turnos
     * @ApiDoc(section="Turnos")
     * @param Request $request
     * @return mixed
     * @Get("/snt/turnos/buscar")
     */
    public function searchTurnosSNTAction(Request $request)
    {
        $params = $request->query->all();
        $params['offset'] = (int) $request->get('offset', 0);
        $params['limit'] = (int) $request->get('limit', 10);
        $this->turnoServices = $this->getTurnoServices();
        return $this->turnoServices->searchTurnoSNT(
            $params,
            function ($response) {
                return $response;
            },
            function ($error) {
                return $this->respuestaError($error);
            }
        );
    }

    /**
     * Obtener un turno por ID
     * @ApiDoc(section="Turnos")
     * @param integer $id Identificador único del turno
     * @return mixed
     * @Get("/snt/turnos/{id}")
     */
    public function getTurnoSNTAction($id)
    {
        $this->turnoServices = $this->getTurnoServices();
        return $this->turnoServices->getItemTurnoSNT($id);
    }

    /**
     * Listado de turnos recepcionados por ventanilla y punto de atención
     * @ApiDoc(section="Turnos")
     * @param Request $request Espera el resultado de una petición como parámetro
     * @return mixed
     * @throws \Doctrine\ORM\ORMException
     * @Get("/turnos")
     */
    public function getListTurnosRecepcionadosAction(Request $request)
    {
        $params = $request->query->all();
        $params['offset'] = (int) $request->get('offset', 0);
        $params['limit'] = (int) $request->get('limit', 10);
        $authorization = $request->headers->get('authorization', null);
        $this->turnoServices = $this->getTurnoServices();
        return $this->turnoServices->findAllPaginate(
            $params,
            $authorization,
            function ($err) {
                return $this->respuestaError($err);
            }
        );
    }

    /**
     * Obtiene próximo turno
     * @ApiDoc(section="Turnos")
     * @param Request $request Espera el resultado de una petición como parámetro
     * @return mixed
     * @throws \Doctrine\ORM\ORMException
     * @Get("/turnos/proximo")
     */
    public function nextAction(Request $request)
    {
        $authorization = $request->headers->get('Authorization', null);
        $params = $request->query->all();
        $this->turnoServices = $this->getTurnoServices();

        return $this->turnoServices->getProximoTurno(
            $params,
            $authorization,
            function ($err) {
                return $this->respuestaError($err);
            }
        );
    }

    /**
     * Obtiene la posición de la cola de un Turno
     * @ApiDoc(section="Turnos")
     * @param integer $id Identificador único del turno
     * @return mixed
     * @Get("/turnos/{id}/posicion")
     * @throws \Exception
     */
    public function getPositionAction($id)
    {
        $this->turnoServices = $this->getTurnoServices();
        return $this->turnoServices->getPosicionTurno(
            $id,
            function ($err) {
                return $this->respuestaError($err);
            }
        );
    }
}
