<?php
namespace ApiV1Bundle\ApplicationServices;

use ApiV1Bundle\Entity\User;
use ApiV1Bundle\Entity\UsuarioStrategy;
use ApiV1Bundle\Entity\Validator\TokenValidator;
use ApiV1Bundle\Entity\Validator\ValidateResultado;
use ApiV1Bundle\Repository\AdminRepository;
use ApiV1Bundle\Repository\AgenteRepository;
use ApiV1Bundle\Repository\ResponsableRepository;
use ApiV1Bundle\Repository\UsuarioRepository;
use Symfony\Component\DependencyInjection\Container;
use ApiV1Bundle\Repository\UserRepository;
use ApiV1Bundle\Helper\JWToken;
use ApiV1Bundle\Entity\Validator\UserValidator;
use ApiV1Bundle\Repository\TokenRepository;
use ApiV1Bundle\Entity\Factory\TokenFactory;
use ApiV1Bundle\ExternalServices\SecurityIntegration;
use ApiV1Bundle\Entity\Validator\CommunicationValidator;

/**
 * Class SecurityServices
 * @package ApiV1Bundle\ApplicationServices
 */
class SecurityServices extends SNCServices
{
    /** @var \ApiV1Bundle\Repository\UserRepository $userRepository */
    private $userRepository;

    /** @var \ApiV1Bundle\Repository\TokenRepository $tokenRepository */
    private $tokenRepository;

    /** @var JWToken $jwtoken JWToken */
    private $jwtoken;

    /** @var \ApiV1Bundle\Entity\Validator\UserValidator $userValidator */
    private $userValidator;

    /** @var \ApiV1Bundle\Repository\UserRepository $usuarioRepository */
    private $usuarioRepository;

    /** @var \ApiV1Bundle\ApplicationServices\AgenteServices $agenteService */
    private $agenteService;
    private $securityIntegration;
    private $communicationValidator;
    private $tokenValidator;
    private $agenteRepository;
    private $adminRepository;
    private $responsableRepository;

    /**
     * SecurityServices constructor
     *
     * @param Container $container
     * @param UserRepository $userRepository
     * @param TokenRepository $tokenRepository
     * @param JWToken $jwtoken
     * @param UserValidator $userValidator
     * @param UsuarioRepository $usuarioRepository
     * @param AgenteServices $agenteServices
     * @param SecurityIntegration $securityIntegration
     * @param CommunicationValidator $communicationValidator
     * @param TokenValidator $tokenValidator
     * @param AgenteRepository $agenteRepository
     * @param AdminRepository $adminRepository
     * @param ResponsableRepository $responsableRepository
     */
    public function __construct(
        Container $container,
        UserRepository $userRepository,
        TokenRepository $tokenRepository,
        JWToken $jwtoken,
        UserValidator $userValidator,
        UsuarioRepository $usuarioRepository,
        AgenteServices $agenteServices,
        SecurityIntegration $securityIntegration,
        CommunicationValidator $communicationValidator,
        TokenValidator $tokenValidator,
        AgenteRepository $agenteRepository,
        AdminRepository $adminRepository,
        ResponsableRepository $responsableRepository
    ) {
        parent::__construct($container);
        $this->userRepository = $userRepository;
        $this->tokenRepository = $tokenRepository;
        $this->jwtoken = $jwtoken;
        $this->userValidator = $userValidator;
        $this->usuarioRepository = $usuarioRepository;
        $this->agenteService = $agenteServices;
        $this->securityIntegration = $securityIntegration;
        $this->communicationValidator = $communicationValidator;
        $this->tokenValidator = $tokenValidator;
        $this->agenteRepository = $agenteRepository;
        $this->adminRepository = $adminRepository;
        $this->responsableRepository = $responsableRepository;
    }

    /**
     * User login
     * @param array $params Arreglo con los datos para hacer login
     * @param callback $onError Callback para devolver respuesta fallida
     * @return mixed
     * @throws \Doctrine\ORM\ORMException
     * @throws \Exception
     */
    public function login($params, $onError)
    {
        $username = isset($params['username']) ? $params['username'] : null;
        $user = $this->userRepository->findOneByUsername($username);
        $validateResult = $this->userValidator->validarParamsLogin($params, $user);
        if (! $validateResult->hasError()) {
            $validateResult = $this->userValidator->validarLogin($user, $params['password']);
            if (! $validateResult->hasError()) {
                $usuarioRepository = new UsuarioStrategy(
                    $this->agenteRepository,
                    $this->responsableRepository,
                    $this->adminRepository
                );
                $usuario = $usuarioRepository->getUser($user);
                $usuario['rol_id'] = $user->getRol();
                $usuario['rol'] = $user->getRoles();
                $usuario['token'] = $this->jwtoken->getToken($user->getId(), $user->getUsername(), $user->getRoles());
                return $usuario;
            }
        }
        return $this->processResult(
            $validateResult,
            function ($result) {
                return $result;
            },
            $onError
        );
    }

    /**
     * User logout
     *
     * @param string $authorization token del usuario logueado
     * @param callback $success Callback para devolver respuesta exitosa
     * @param callback $error Callback para devolver respuesta fallida
     * @return mixed
     * @throws \Doctrine\ORM\ORMException
     * @throws \Exception
     */
    public function logout($authorization, $success, $error)
    {
        // validamos el token
        if ($this->validarToken($authorization)) {
            $token = md5($authorization);

            // agregamos el token a la lista si no existe
            $verificarCancelado = $this->tokenRepository->findOneByToken($token);
            if (! $verificarCancelado) {
                //desasignar usuario de una ventanilla
                $validateResult = $this->desasignarUsuarioVentanilla($authorization);

                if (! $validateResult->hasError()) {
                    $tokenFactory = new TokenFactory($this->tokenRepository);
                    $validateResult = $tokenFactory->insert($token);
                }

                return $this->processResult(
                    $validateResult,
                    function ($entity) use ($success) {
                        return call_user_func($success, $this->tokenRepository->save($entity));
                    },
                    $error
                );
            }
        }
        return call_user_func($success, []);
    }

    /**
     * Desasigna un usario de una ventanilla
     *
     * @param string $authorization token del usuario logueado
     * @return mixed
     * @throws \Exception
     */
    private function desasignarUsuarioVentanilla($authorization)
    {
        $tokenString = $this->jwtoken->getPayload($authorization);
        $uid = $this->jwtoken->getUID($tokenString);
        $rol = $this->jwtoken->getTokenRol($tokenString);
        if ($rol == User::ROL_AGENTE) {
            return $this->agenteService->desasignarVentanilla($uid, $rol);
        } else {
            return new ValidateResultado(null, []);
        }
    }

    /**
     * Valida un token
     *
     * @param string $authorization token del usuario logueado
     * @return array
     * @throws \Doctrine\ORM\ORMException
     */
    public function validarToken($authorization)
    {
        return $this->tokenValidator->validarAuthorization($authorization);
    }

    /**
     * Obtiene TokenMail
     * @param object $user Usuario
     * @return \Lcobucci\JWT\Builder
     */
    public function getTokenMail($user)
    {
        return $this->jwtoken->getToken($user->getId(), $user->getUsername(), $user->getRoles());
    }

    /**
     * Validamos la comunicación entre APIs
     *
     * @param array $params Request
     * @return mixed
     */
    public function sendSecurePostCommunication($params)
    {
        $response = $this->securityIntegration->securePostCommunications($params);
        foreach ($response as $key => $value) {
            if (is_object($value)) {
                $response->{$key} = (array) $value;
            }
        }
        return (array) $response;
    }

    /**
     * Validamos la comunicación con la API del SNC
     *
     * @param array $params Request
     * @param callback $success Callback para devolver respuesta exitosa
     * @param callback $onError Callback para devolver respuesta fallida
     * @return mixed
     * @throws \Exception
     */
    public function validateSNTCommunication($params, $success, $onError)
    {
        $validateResult = $this->communicationValidator->validateSNTRequest($params);
        return $this->processResult(
            $validateResult,
            function () use ($success, $params) {
                return call_user_func($success, $params);
            },
            $onError
        );
    }

    /**
     * Validación de token
     * @param $authorization token del usuario logueado
     * @param callback $success Callback para devolver respuesta exitosa
     * @param callback $onError Callback para devolver respuesta fallida
     * @return ValidateResultado
     * @throws \Exception
     */
    public function isTokenValid($authorization, $success, $onError)
    {
        $validateResult = $this->tokenValidator->validarToken($authorization);
        $user = $validateResult->getEntity();

        return $this->processResult(
            $validateResult,
            function () use ($success, $user) {
                return call_user_func($success, $user);
            },
            $onError
        );
    }

}
