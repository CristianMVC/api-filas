<?php

namespace ApiV1Bundle\Controller;

use ApiV1Bundle\ApplicationServices\ColaServices;
use ApiV1Bundle\Entity\Cola;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\Post;
use FOS\RestBundle\Controller\Annotations\Put;
use FOS\RestBundle\Controller\Annotations\Delete;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;

/**
 * Class ColaController
 * @package ApiV1Bundle\ApplicationServices
 */
class ColaController extends ApiController
{
    /** @var ColaServices $colaServices */
    private $colaServices;

    /**
     * Listado de colas
     * @ApiDoc(section="Colas")
     * @param Request $request Espera el resultado de una petición como parámetro
     * @return mixed
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\ORMException
     * @Get("/colas")
     */
    public function getListAction(Request $request)
    {
        $puntoAtencionId = $request->get('puntoatencion');
        $offset = $request->get('offset', 0);
        $limit = $request->get('limit', 10);
        $this->colaServices = $this->getColasServices();
        return $this->colaServices->findAllPaginate(
            $puntoAtencionId,
            (int) $limit,
            (int) $offset
        );
    }

    /**
     * Listado de postas
     * @ApiDoc(section="Colas")
     * @param Request $request
     * @return mixed
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\ORMException
     * @Get("/colas/postas")
     */
    public function getListPostaAction(Request $request)
    {
        $puntoAtencionId = $request->get('puntoatencion');
        $offset = $request->get('offset', 0);
        $limit = $request->get('limit', 10);
        $this->colaServices = $this->getColasServices();
        return $this->colaServices->findAllPaginate(
            $puntoAtencionId,
            (int) $limit,
            (int) $offset,
            Cola::TIPO_POSTA
        );
    }

    /**
     * Listado de postas asignadas
     * @ApiDoc(section="Colas")
     * @param Request $request
     * @return mixed
     * @Get("/colas/postas/asignadas")
     * @throws \Doctrine\ORM\ORMException
     */
    public function getListPostaAsignadaAction(Request $request)
    {
        $puntoAtencionId = $request->get('puntoatencion');
        $this->colaServices = $this->getColasServices();
        return $this->colaServices->findAllPostas($puntoAtencionId);
    }

    /**
     * Obtiene una cola
     * @ApiDoc(section="Colas")
     * @param integer $id Identificador único de cola
     * @return mixed
     * @Get("/colas/{id}")
     */
    public function getItemAction($id)
    {
        $this->colaServices = $this->getColasServices();
        return $this->colaServices->get(
            $id,
            function ($err){
                return $this->respuestaError($err);
            }
        );
    }

    /**
     * Crea una cola de un grupo de tramite
     * @ApiDoc(section="Colas")
     * @param Request $request Se envian los datos para crear la cola
     * @return mixed
     * @Post("/colas/grupotramite")
     * @throws \Exception
     */
    public function postAction(Request $request)
    {
        $params = $request->request->all();
        $this->colaServices = $this->getColasServices();

        return $this->colaServices->addColaGrupoTramite(
            $params,
            function ($cola) {
                return $this->respuestaOk('Cola agregada con éxito', [
                    'id' => $cola->getId()
                ]);
            },
            function ($err) {
                return $this->respuestaError($err);
            }
        );
    }

    /**
     * Elimina una cola de grupo de tramite
     * @ApiDoc(section="Colas")
     * @param Request $request
     * @param integer $id Identificador único de la ventanilla
     * @return mixed
     * @throws \Exception
     * @Delete("/colas/grupotramite/{id}")
     */
    public function deleteAction(Request $request, $id)
    {
        $params = $request->query->all();
        $this->colaServices = $this->getColasServices();
        return $this->colaServices->removeColaGrupoTramite(
            $id,
            $params,
            function () {
                return $this->respuestaOk('Cola eliminada con éxito');
            },
            function ($err) {
                return $this->respuestaError($err);
            }
        );
    }

    /**
     * Modifica una cola
     * @ApiDoc(section="Colas")
     * @param Request $request Se envian los datos para modificar la cola
     * @param integer $id Identificador único de la cola
     * @return mixed
     * @Put("/colas/grupotramite/{id}")
     * @throws \Exception
     */
    public function putAction(Request $request, $id)
    {
        $params = $request->request->all();
        $this->colaServices = $this->getColasServices();

        return $this->colaServices->editColaGrupoTramite(
            $params,
            $id,
            function () {
                return $this->respuestaOk('Cola modificada con éxito');
            },
            function ($err) {
                return $this->respuestaError($err);
            }
        );
    }

    /**
     * Crea una cola de un grupo de tramite
     * @ApiDoc(section="Colas")
     * @param Request $request Se envian los datos para crear la cola
     * @return mixed
     * @throws \Exception
     * @Post("/colas/postas")
     */
    public function postColaPostaAction(Request $request)
    {
        $params = $request->request->all();
        $this->colaServices = $this->getColasServices();

        return $this->colaServices->addColaPosta(
            $params,
            function ($cola) {
                return $this->respuestaOk('Cola agregada con éxito', [
                    'id' => $cola->getId()
                ]);
            },
            function ($err) {
                return $this->respuestaError($err);
            }
        );
    }

    /**
     * Eliminar una cola de grupo de tramite
     * @ApiDoc(section="Colas")
     * @param integer $id Identificador único de la cola
     * @return mixed
     * @throws \Exception
     * @Delete("/colas/postas/{id}")
     */
    public function deleteColaPostaAction($id)
    {
        $this->colaServices = $this->getColasServices();
        return $this->colaServices->removeColaPosta(
            $id,
            function () {
                return $this->respuestaOk('Cola eliminada con éxito');
            },
            function ($err) {
                return $this->respuestaError($err);
            }
        );
    }

    /**
     * Modificar una cola
     * @ApiDoc(section="Colas")
     * @param Request $request Espera el resultado de una petición como parámetro
     * @param integer $id Identificador único de la cola
     * @return mixed
     * @throws \Exception
     * @Put("/colas/postas/{id}")
     */
    public function putColaPostaAction(Request $request, $id)
    {
        $params = $request->request->all();
        $this->colaServices = $this->getColasServices();

        return $this->colaServices->editColaPosta(
            $params,
            $id,
            function () {
                return $this->respuestaOk('Cola modificada con éxito');
            },
            function ($err) {
                return $this->respuestaError($err);
            }
        );
    }

    /**
     * Obtiene la cantidad de personas delante en una cola por grupotrámiteSNT
     * @ApiDoc(section="Colas")
     * @param Request $request
     * @return mixed
     * @throws \Doctrine\ORM\ORMException
     * @Post("/integration/tramite/delante")
     */
    public function getDelanteAction(Request $request)
    {
        $params = $request->request->all();
        $this->colaServices = $this->getColasServices();
        return $this->colaServices->getDelante(
            $params,
            function ($entity) {
                return $this->respuestaOk('', $entity);
            },
            function ($err) {
                return $this->respuestaError($err);
            });
    }
}
