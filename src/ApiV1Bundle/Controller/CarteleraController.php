<?php
namespace ApiV1Bundle\Controller;

use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\Post;
use FOS\RestBundle\Controller\Annotations\Put;
use FOS\RestBundle\Controller\Annotations\Delete;
use FOS\RestBundle\Controller\Annotations\Route;
use Symfony\Component\HttpFoundation\Request;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;

/**
 * Class CarteleraController
 * @package ApiV1Bundle\Controller
 */

class CarteleraController extends ApiController
{
    /** @var \ApiV1Bundle\ApplicationServices\CarteleraServices $carteleraServices */
    private $carteleraServices;

    /**
     * Listado paginado de carteleras
     * @ApiDoc(section="Cartelera")
     * @param Request $request
     * @return mixed
     * @Get("/carteleras")
     * @throws \Doctrine\ORM\ORMException
     */
    public function getListAction(Request $request)
    {
        $authorization = $request->headers->get('Authorization', null);
        $offset = $request->get('offset', 0);
        $limit = $request->get('limit', 10);
        $this->carteleraServices = $this->getCarteleraServices();
        return $this->carteleraServices->findAllPaginate(
            $authorization,
            (int) $limit,
            (int) $offset,
            function ($err) {
               return $this->respuestaError($err);
            }
        );
    }

    /**
     * Obtiene una Cartelera
     * @ApiDoc(section="Cartelera")
     * @param Request $request
     * @param integer $id Identificador único de la cartelera
     * @return mixed
     * @throws \Doctrine\ORM\ORMException
     * @Get("/carteleras/{id}")
     */
    public function getItemAction(Request $request, $id)
    {
        $authorization = $request->headers->get('Authorization', null);
        $this->carteleraServices = $this->getCarteleraServices();
        return $this->carteleraServices->get(
            $id,
            $authorization,
            function ($err) {
                if (array_key_exists('403',$err['errors'])){
                    return $this->respuestaForbiddenRequest($err['errors']['403']);
                }
                return $this->respuestaError($err);
            }
        );
    }

    /**
     * Crea una Cartelera
     * @ApiDoc(section="Cartelera")
     * @param Request $request
     * @return mixed
     * @Post("/carteleras")
     */
    public function postAction(Request $request)
    {
        $authorization = $request->headers->get('Authorization', null);
        $params = $request->request->all();
        $carteleraServices = $this->getCarteleraServices();
        return $carteleraServices->create(
            $params,
            $authorization,
            function ($cartelera) use ($carteleraServices) {
                return $this->respuestaOk('Cartelera creada con éxito', [
                    'id' => $cartelera->getId()
                ]);
            },
            function ($err) {
                return $this->respuestaError($err);
            }
        );
    }

    /**
     * Modificar una Cartelera
     * @ApiDoc(section="Cartelera")
     * @param Request $request Espera el resultado de una petición como parámetro
     * @param integer $id Identificador único de la cartelera
     * @return mixed
     * @throws \Doctrine\ORM\ORMException
     * @Put("/carteleras/{id}")
     */
    public function putAction(Request $request, $id)
    {
        $authorization = $request->headers->get('Authorization', null);
        $params = $request->request->all();
        $this->carteleraServices = $this->getCarteleraServices();
        return $this->carteleraServices->edit(
            $params,
            $id,
            $authorization,
            function () {
                return $this->respuestaOk('Cartelera modificada con éxito');
            },
            function ($err) {
                if (array_key_exists('403',$err['errors'])){
                    return $this->respuestaForbiddenRequest($err['errors']['403']);
                }
                return $this->respuestaError($err);
            }
        );
    }

    /**
     * Eliminar una Cartelera
     * @ApiDoc(section="Cartelera")
     * @param Request $request
     * @param integer $id Identificador único de la cartelera
     * @return mixed
     * @throws \Doctrine\ORM\ORMException
     * @Delete("/carteleras/{id}")
     */
    public function deleteAction(Request $request, $id)
    {
        $authorization = $request->headers->get('Authorization', null);
        $this->carteleraServices = $this->getCarteleraServices();
        return $this->carteleraServices->delete(
            $id,
            $authorization,
            function () {
                return $this->respuestaOk('Cartelera eliminada con éxito');
            },
            function ($err) {
                if (array_key_exists('403',$err['errors'])){
                    return $this->respuestaForbiddenRequest($err['errors']['403']);
                }
                return $this->respuestaError($err);
            }
        );
    }

}
