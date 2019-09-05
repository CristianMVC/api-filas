<?php
namespace ApiV1Bundle\Controller;

use FOS\RestBundle\Controller\Annotations\Route;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\Post;
use Symfony\Component\HttpFoundation\Request;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;

/**
 * Class SecurityController
 * @package ApiV1Bundle\ApplicationServices
 */
class SecurityController extends ApiController
{
    /** @var \ApiV1Bundle\ApplicationServices\SecurityServices $securityServices */
    private $securityServices;

    /** @var \ApiV1Bundle\ApplicationServices\UsuarioServices $usuarioServices */
    private $usuarioServices;

    /**
     * User login
     * @ApiDoc(section="Seguridad")
     * @param Request $request Se envian los datos para hacer login
     * @return mixed
     * @throws \Doctrine\ORM\ORMException
     * @Post("/auth/login")
     */
    public function login(Request $request)
    {
        $params = $request->request->all();
        $this->securityServices = $this->getSecurityServices();
        return $this->securityServices->login(
            $params,
            function ($err) {
                return $this->respuestaForbiddenRequest($err);
            }
        );
    }

    /**
     * User logout
     * @ApiDoc(section="Seguridad")
     * @param Request $request Se envian los datos para hacer logout
     * @return mixed
     * @throws \Doctrine\ORM\ORMException
     * @Post("/auth/logout")
     */
    public function logout(Request $request)
    {
        $token = $request->headers->get('authorization', null);
        $this->securityServices = $this->getSecurityServices();
        return $this->securityServices->logout(
            $token,
            function ($token) {
                return $this->respuestaOk('Sesion terminada');
            },
            function ($error) {
                return $this->respuestaError($error);
            }
        );
    }

    /**
     * Modifica la contraseña del usuario
     * @ApiDoc(section="Seguridad")
     * @param Request $request Se envian los datos para modificar la contraseña
     * @return mixed
     * @throws \Exception
     * @Post("auth/modificar")
     */
    public function modificarPassword(Request $request)
    {
        $token = $request->headers->get('authorization', null);
        $params = $request->request->all();
        $this->usuarioServices = $this->getUsuarioServices();
        return $this->usuarioServices->modificarPassword(
            $params,
            $token,
            function ($flush, $token, $userData) {
                return $this->respuestaOk(
                    'Contraseña modificada con éxito'
                );
            },
            function ($err) {
                return $this->respuestaError($err);
            }
        );
    }

    /**
     * Recupera la contraseña del usuario
     * @ApiDoc(section="Seguridad")
     * @param Request $request Se envian los datos para recuperar la contraseña del usuario
     * @return mixed
     * @throws \Doctrine\ORM\ORMException
     * @Post("auth/reset")
     */
    public function envioRecuperarPassword(Request $request)
    {
        $params = $request->request->all();
        $this->usuarioServices = $this->getUsuarioServices();

        return $this->usuarioServices->envioRecuperarPassword(
            $params,
            function ($response) {
                return $this->respuestaOk(
                    'Se ha enviado un email a su casilla para recuperar la contraseña',
                    ['response' => $response]
                );
            },
            function ($err) {
                return $this->respuestaError($err);
            }
        );
    }

    /**
     * POST Test comunicación segura entre API's
     * @ApiDoc(section="Seguridad")
     * @param Request $request
     * @Post("/integration/secure/request")
     * @return mixed
     */
    public function validateRerquestApiCommunication(Request $request)
    {
        $params = $request->request->all();
        $this->securityServices = $this->getSecurityServices();
        return $this->securityServices->sendSecurePostCommunication($params);
    }

    /**
     * POST Test comunicación segura entre API's
     * @ApiDoc(section="Seguridad")
     * @param Request $request
     * @Post("/integration/secure/response")
     * @return mixed
     * @throws \Exception
     */
    public function validateResponseApiCommunication(Request $request)
    {
        $params = $request->request->all();
        $this->securityServices = $this->getSecurityServices();
        return $this->securityServices->validateSNTCommunication(
            $params,
            function ($response) {
                return $this->respuestaOk('secure communication ok', $response);
            },
            function ($error) {
                return $this->respuestaError($error);
            }
        );
    }

    /**
     * Valida si un token es valido o no
     * @ApiDoc(section="Seguridad")
     * @param Request $request
     * @return mixed
     * @Post("auth/validar")
     * @throws \Exception
     */
    public function validarTokenAction(Request $request)
    {
        $token = $request->headers->get('authorization', null);
        $this->securityServices = $this->getSecurityServices();
        return $this->securityServices->isTokenValid(
            $token,
            function ($user) {
                return $this->respuestaOk(
                    'Token valido',
                    ['username' => $user->getUsername()]
                );
            },
            function ($error) {
                return $this->respuestaError($error);
            }
        );
    }
}
