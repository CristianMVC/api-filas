<?php
namespace ApiV1Bundle\Controller;

use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\Post;
use FOS\RestBundle\Controller\Annotations\Put;
use FOS\RestBundle\Controller\Annotations\Delete;
use Symfony\Component\HttpFoundation\Request;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;

/**
 * Class AgenteController
 * @package ApiV1Bundle\ApplicationServices
 */
class AgenteController extends ApiController
{
    /** @var \ApiV1Bundle\ApplicationServices\AgenteServices $agenteServices */
    private $agenteServices;

    /**
     * Listado de agentes
     * @ApiDoc(section="Agente")
     * @param Request $request Espera el resultado de una petición como parámetro
     * @return mixed
     * @Get("/agentes")
     * @throws \Doctrine\ORM\ORMException
     */
    public function getListAction(Request $request)
    {
        $puntoAtencionId = $request->get('puntoatencion', null);
        $offset = $request->get('offset', 0);
        $limit = $request->get('limit', 10);
        $params = $request->query->all();
        $this->agenteServices = $this->getAgenteServices();
        return $this->agenteServices->findAllPaginate($puntoAtencionId, (int) $limit, (int) $offset,$params);
    }

    /**
     * Obtiene un agente
     * @ApiDoc(section="Agente")
     * @param integer $id Identificador único de agente
     * @return mixed
     * @Get("/agentes/{id}")
     */
    public function getItemAction($id)
    {
        $this->agenteServices = $this->getAgenteServices();
        return $this->agenteServices->get(
            $id,
            function ($err) {
                return $this->respuestaError($err);
            });
    }

    /**
     * obtener la ventanilla actual del agente logeado
     * @ApiDoc(section="Agente")
     * @param Request $request
     * Espera el resultado de una petición como parámetro
     * @return mixed
     * @Get("/ventanillas/actual")
     */
    public function getVentanillaActual(Request $request)
    {
        $authorization = $request->headers->get('Authorization', null);
        $this->agenteServices = $this->getAgenteServices();
        return $this->agenteServices->findVentanillaActual($authorization,
            function ($err) {
                return $this->respuestaError($err);
            }
        );
    }

    /**
     * Muestra el listado de ventanillas por usuario que no están asignadas
     * @ApiDoc(section="Agente")
     * @param integer $id Identificador único de agente
     * @return mixed
     * @Get("/agentes/{id}/ventanillas")
     * @throws \Doctrine\ORM\ORMException
     */
    public function getVentanillasByAgente($id)
    {
        $this->agenteServices = $this->getAgenteServices();
        return $this->agenteServices->findVentanillasAgente($id);
    }

    /**
     * Asigna una ventanilla a un Agente
     * @ApiDoc(section="Agente")
     * @param integer $idUsuario Identificador único de agente
     * @param integer $idVentanilla Identificador único de ventanilla
     * @return mixed
     * @Post("/agentes/{idUsuario}/ventanilla/{idVentanilla}")
     * @throws \Exception
     */
    public function asignarVentanillaAction($idUsuario, $idVentanilla)
    {
        $this->agenteServices = $this->getAgenteServices();
        return $this->agenteServices->asignarVentanilla(
            $idUsuario,
            $idVentanilla,
            function () {
                return $this->respuestaOk('Agente asignado a la ventanilla con éxito');
            },
            function ($err) {
                return $this->respuestaError($err);
            }
        );
    }

    /**
     * Desasigna una ventanilla a un Agente
     * @ApiDoc(section="Agente")
     * @param integer $idUser Identificador único de agente
     * @return mixed
     * @Post("/agentes/{idUser}/desasignar")
     * @throws \Doctrine\ORM\ORMException
     * @throws \Exception
     */
    public function desasignarVentanillaAction($idUser)
    {
        $this->agenteServices = $this->getAgenteServices();
        return $this->agenteServices->desasignarVentanilla(
            $idUser,
            function () {
                return $this->respuestaOk('Agente desasignado de la ventanilla con éxito');
            },
            function ($err) {
                return $this->respuestaError($err);
            }
        );
    }

    /**
     * Listado de usuarios
     * @ApiDoc(section="Agente")
     * @param Request $request Espera el resultado de una petición como parámetro
     * @return mixed
     * @Post("/integracion/agentes")
     */
    public function getAllAction(Request $request)
    {
        $params = $request->request->all();
        $authorization = $request->headers->get('Authorization', null);
        $this->agenteServices = $this->getAgenteServices();
        return $this->agenteServices->findAllAgentes(
            $authorization,
            $params,
            function ($err) {
                return $this->respuestaError($err);
            }
        );
    }
}
