<?php

namespace ApiV1Bundle\Controller;

use ApiV1Bundle\Entity\Agente;
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
 * Class VentanillaController
 * @package ApiV1Bundle\ApplicationServices
 */
class VentanillaController extends ApiController
{
    /** @var \ApiV1Bundle\ApplicationServices\VentanillaServices ventanillaServices*/
    private $ventanillaServices;

    /**
     * Listado de ventanillas
     *
     * @ApiDoc(section="Ventanilla")
     * @param Request $request Espera el resultado de una petición como parámetro
     * @return mixed
     * @throws \Doctrine\ORM\NoResultExceptiona
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @Get("/ventanillas")
     */
    public function getListAction(Request $request)
    {
        $puntoAtencionId = $request->get('puntoatencion', null);
        $offset = $request->get('offset', 0);
        $limit = $request->get('limit', 10);
        $this->ventanillaServices = $this->getVentanillaServices();
        return $this->ventanillaServices->findAllPaginate($puntoAtencionId, (int) $limit, (int) $offset);
    }

    /**
     * Obtiene una ventanilla
     * @ApiDoc(section="Ventanilla")
     * @param integer $id Identificador único de la ventanilla
     * @return mixed
     * @Get("/ventanillas/{id}")
     */
    public function getItemAction($id)
    {
        $this->ventanillaServices = $this->getVentanillaServices();
        return $this->ventanillaServices->get(
            $id,
            function ($err) {
                return $this->respuestaError($err);
            }
        );
    }

    /**
     * Crear una ventanilla
     * @ApiDoc(section="Ventanilla")
     * @param Request $request Se envian los datos para crear la ventanilla
     * @return mixed
     * @throws \Exception
     * @Post("/ventanillas")
     */
    public function postAction(Request $request)
    {
        $params = $request->request->all();
        $this->ventanillaServices = $this->getVentanillaServices();

        return $this->ventanillaServices->create(
            $params,
            function ($ventanilla) {
                return $this->respuestaOk('Ventanilla creada con éxito', [
                    'id' => $ventanilla->getId()
                ]);
            },
            function ($err) {
                return $this->respuestaError($err);
            }
        );
    }

    /**
     * Modifica una ventanilla
     * @ApiDoc(section="Ventanilla")
     * @param Request $request Se envian los datos para modificar la ventanilla
     * @param integer $id Identificador único de la ventanilla
     * @return mixed
     * @throws \Exception
     * @Put("/ventanillas/{id}")
     */
    public function putAction(Request $request, $id)
    {
        $params = $request->request->all();
        $this->ventanillaServices = $this->getVentanillaServices();

        return $this->ventanillaServices->edit(
            $params,
            $id,
            function () {
                return $this->respuestaOk('Ventanilla modificada con éxito');
            },
            function ($err) {
                return $this->respuestaError($err);
            }
        );
    }

    /**
     * Elimina una ventanilla
     * @ApiDoc(section="Ventanilla")
     * @param integer $id Identificador único de la ventanilla
     * @return mixed
     * @throws \Exception
     * @Delete("/ventanillas/{id}")
     */
    public function deleteAction($id)
    {
        $this->ventanillaServices = $this->getVentanillaServices();
        return $this->ventanillaServices->delete(
            $id,
            function () {
                return $this->respuestaOk('Ventanilla eliminada con éxito');
            },
            function ($err) {
                return $this->respuestaError($err);
            }
        );
    }
}
