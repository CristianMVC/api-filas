<?php
namespace ApiV1Bundle\Controller;

use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\Post;
use FOS\RestBundle\Controller\Annotations\Put;
use FOS\RestBundle\Controller\Annotations\Delete;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;

/**
 * Class ColaController
 * @package ApiV1Bundle\ApplicationServices
 */
class PuntoAtencionController extends ApiController
{
    /** @var \ApiV1Bundle\ApplicationServices\PuntoAtencionServices $puntoAtencionServices */
    private $puntoAtencionServices;

    /**
     * Listado paginado de puntos de atencion
     * @ApiDoc(section="Punto Atencion")
     * @param Request $request
     * @return mixed
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @Get("/puntoatencion")
     */
    public function getListAction(Request $request)
    {
        $offset = $request->get('offset', 0);
        $limit = $request->get('limit', 10);
        $this->puntoAtencionServices = $this->getPuntosAtencionService();
        return $this->puntoAtencionServices->findAllPaginate((int) $limit, (int) $offset);
    }

    /**
     * Crea un punto de atencion
     * @ApiDoc(section="Punto Atencion")
     * @param Request $request Se envian los datos para crear el punto de atencion
     * @return mixed Respuesta con estado
     * @Post("/integracion/puntosatencion")
     */
    public function postAction(Request $request)
    {
        $params = $request->request->all();
        $puntoAtencionServices = $this->getPuntoAtencionServices();
        return $puntoAtencionServices->create(
            $params,
            function ($puntoAtencion) {
                return $this->respuestaOk('Punto Atencion creado con éxito', [
                    'id' => $puntoAtencion->getId()
                ]);
            },
            function ($err) {
                return $this->respuestaError($err);
            }
        );
    }

    /**
     * Edita un punto de atencion
     * @ApiDoc(section="Punto Atencion")
     * @param Request $request Se envian los datos para modificar el punto de atencion
     * @param integer $id identificar único del punto de atención
     * @return mixed
     * @Put("/integracion/puntosatencion/{id}")
     */
    public function putAction(Request $request, $id)
    {
        $params = $request->request->all();
        $puntoAtencionServices = $this->getPuntoAtencionServices();
        return $puntoAtencionServices->edit(
            $id,
            $params,
            function ($puntoAtencion) {
                return $this->respuestaOk('Punto de Atencion editado con éxito', []);
            },
            function ($err) {
                return $this->respuestaError($err);
            }
        );
    }

    /**
     * Elimina un punto de atencion
     * @ApiDoc(section="Punto Atencion")
     * @param Request $request
     * @param integer $id Identificador único de Punto de Atencion
     * @return mixed
     * @Delete("/integracion/puntosatencion/{id}")
     */
    public function deleteAction(Request $request, $id)
    {
        $params = $request->query->all();
        $puntoAtencionServices = $this->getPuntoAtencionServices();
        return $puntoAtencionServices->delete(
            $id,
            $params,
            function ($puntoAtencion) {
                return $this->respuestaOk('Punto de atención eliminado con éxito');
            },
            function ($err) {
                return $this->respuestaError($err);
            }
        );
    }
}
