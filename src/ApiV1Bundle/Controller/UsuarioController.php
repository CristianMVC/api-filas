<?php
namespace ApiV1Bundle\Controller;

use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\Post;
use FOS\RestBundle\Controller\Annotations\Put;
use FOS\RestBundle\Controller\Annotations\Delete;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;

/**
 * Class UsuarioController
 * @package ApiV1Bundle\ApplicationServices
 */
class UsuarioController extends ApiController
{
    /** @var \ApiV1Bundle\ApplicationServices\UsuarioServices $usuarioServices */
    private $usuarioServices;

    /**
     * Crea un usuario
     * @ApiDoc(section="Usuario")
     * @param Request $request Se envian los datos para crear el usuario
     * @return mixed
     * @Post("/usuarios")
     */
    public function postAction(Request $request)
    {
        $params = $request->request->all();
        $usuarioServices = $this->getUsuarioServices();

        return $usuarioServices->create(
            $params,
            function ($usuario, $userdata) use ($usuarioServices) {
                return $this->respuestaOk('Usuario creado con éxito', [
                    'id' => $usuario->getUser()->getId(),
                    'response' => $usuarioServices->enviarEmailUsuario($userdata, 'usuario')
                ]);
            },
            function ($err) {
                return $this->respuestaError($err);
            }
        );
    }

    /**
     * Edita un usuario
     * @ApiDoc(section="Usuario")
     * @param Request $request Espera el resultado de una petición como parámetro
     * @param integer $idUser Espera el identificador único del usuario
     * @return mixed
     * @throws \Exception
     * @Put("/usuarios/{idUser}")
     */
    public function putAction(Request $request, $idUser)
    {
        $params = $request->request->all();
        $this->usuarioServices = $this->getUsuarioServices();

        return $this->usuarioServices->edit(
            $params,
            $idUser,
            function () {
                return $this->respuestaOk('Usuario modificado con éxito');
            },
            function ($err) {
                return $this->respuestaError($err);
            }
        );
    }

    /**
     * Elimina un agente
     * @ApiDoc(section="Usuario")
     * @param integer $id Identificador único del agente
     * @return mixed
     * @throws \Exception
     * @Delete("/usuarios/{id}")
     */
    public function deleteAction($id)
    {
        $this->usuarioServices = $this->getUsuarioServices();
        return $this->usuarioServices->delete(
            $id,
            function () {
                return $this->respuestaOk('Usuario eliminado con éxito');
            },
            function ($err) {
                return $this->respuestaError($err);
            }
        );
    }

    /**
     * Listado paginado de usuarios
     * @ApiDoc(section="Usuario")
     * @param Request $request Espera el resultado de una petición como parámetro
     * @return mixed
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @Get("/usuarios")
     */
    public function getListAction(Request $request)
    {
        $offset = $request->get('offset', 0);
        $limit = $request->get('limit', 10);
        $this->usuarioServices = $this->getUsuarioServices();
        return $this->usuarioServices->findAllPaginate((int) $limit, (int) $offset);
    }

    /**
     * Obtiene un usuario
     * @ApiDoc(section="Usuario")
     * @param integer $id Identificador único del usuario
     * @return mixed
     * @Get("/usuarios/{id}")
     */
    public function getItemAction($id)
    {
        $this->usuarioServices = $this->getUsuarioServices();
        return $this->usuarioServices->get(
            $id,
            function ($err) {
                return $this->respuestaError($err);
            }
        );
    }
}
