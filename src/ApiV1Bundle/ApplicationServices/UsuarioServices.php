<?php
namespace ApiV1Bundle\ApplicationServices;

use ApiV1Bundle\Entity\Factory\TokenFactory;
use ApiV1Bundle\Entity\Factory\UsuarioFactoryStrategy;
use ApiV1Bundle\Entity\Sync\UsuarioSyncStrategy;
use ApiV1Bundle\Entity\UsuarioStrategy;
use ApiV1Bundle\Entity\Validator\AdminValidator;
use ApiV1Bundle\Entity\Validator\AgenteValidator;
use ApiV1Bundle\Entity\Validator\ResponsableValidator;
use ApiV1Bundle\Entity\Validator\UserValidator;
use ApiV1Bundle\ExternalServices\NotificationsExternalService;
use ApiV1Bundle\Repository\AdminRepository;
use ApiV1Bundle\Repository\AgenteRepository;
use ApiV1Bundle\Repository\PuntoAtencionRepository;
use ApiV1Bundle\Repository\ResponsableRepository;
use ApiV1Bundle\Repository\TokenRepository;
use ApiV1Bundle\Repository\UserRepository;
use ApiV1Bundle\Repository\UsuarioRepository;
use ApiV1Bundle\Repository\VentanillaRepository;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoder;
use ApiV1Bundle\Helper\ServicesHelper;

/**
 * Class UsuarioServices
 * @package ApiV1Bundle\ApplicationServices
 */
class UsuarioServices extends SNCServices
{
    /** @var UserPasswordEncoder $encoder */
    private $encoder;

    /** @var \ApiV1Bundle\Entity\Validator\UserValidator $userValidator */
    private $userValidator;

    /** @var \ApiV1Bundle\Entity\Validator\AdminValidator $adminValidator  */
    private $adminValidator;

    /** @var \ApiV1Bundle\Entity\Validator\ResponsableValidator $responsableValidator */
    private $responsableValidator;

    /** @var \ApiV1Bundle\Repository\AgenteRepository $agenteRepository */
    private $agenteRepository;

    /** @var \ApiV1Bundle\Repository\ResponsableRepository $responsableRepository */
    private $responsableRepository;

    /** @var \ApiV1Bundle\Repository\AdminRepository $adminRepository */
    private $adminRepository;

    /** @var \ApiV1Bundle\Repository\UserRepository $userRepository */
    private $userRepository;

    /** @var \ApiV1Bundle\Repository\VentanillaRepository $ventanillaRepository */
    private $ventanillaRepository;

    /** @var \ApiV1Bundle\Entity\Validator\AgenteValidator $agenteValidator */
    private $agenteValidator;

    /** @var \ApiV1Bundle\Repository\PuntoAtencionRepository $puntoAtencionRepository */
    private $puntoAtencionRepository;

    /** @var \ApiV1Bundle\Repository\UsuarioRepository $usuarioRepository */
    private $usuarioRepository;

    /** @var \ApiV1Bundle\ExternalServices\NotificationsExternalService $notificationsService */
    private $notificationsService;

    /** @var \ApiV1Bundle\ApplicationServices\SecurityServices $securityService */
    private $securityService;

    /** @var \ApiV1Bundle\Repository\TokenRepository $tokenRepository */
    private $tokenRepository;

    /** @var string $environment */
    private $environment;

    /**
     * UsuarioServices constructor.
     *
     * @param Container $container
     * @param UserPasswordEncoder $encoder
     * @param UserValidator $userValidator
     * @param AdminValidator $adminValidator
     * @param ResponsableValidator $responsableValidator
     * @param AgenteValidator $agenteValidator
     * @param AgenteRepository $agenteRepository
     * @param AdminRepository $adminRepository
     * @param ResponsableRepository $responsableRepository
     * @param UserRepository $userRepository
     * @param VentanillaRepository $ventanillaRepository
     * @param PuntoAtencionRepository $puntoAtencionRepository
     * @param UsuarioRepository $usuarioRepository
     * @param NotificationsExternalService $notificationsService
     * @param SecurityServices $securityServices
     * @param TokenRepository $tokenRepository
     * @throws \Exception
     */
    public function __construct(
        Container $container,
        UserPasswordEncoder $encoder,
        UserValidator $userValidator,
        AdminValidator $adminValidator,
        ResponsableValidator $responsableValidator,
        AgenteValidator $agenteValidator,
        AgenteRepository $agenteRepository,
        AdminRepository $adminRepository,
        ResponsableRepository $responsableRepository,
        UserRepository $userRepository,
        VentanillaRepository $ventanillaRepository,
        PuntoAtencionRepository $puntoAtencionRepository,
        UsuarioRepository $usuarioRepository,
        NotificationsExternalService $notificationsService,
        SecurityServices $securityServices,
        TokenRepository $tokenRepository
    ) {
        parent::__construct($container);
        $this->encoder = $encoder;
        $this->userValidator = $userValidator;
        $this->adminValidator = $adminValidator;
        $this->responsableValidator = $responsableValidator;
        $this->agenteValidator = $agenteValidator;
        $this->agenteRepository = $agenteRepository;
        $this->adminRepository = $adminRepository;
        $this->responsableRepository = $responsableRepository;
        $this->userRepository = $userRepository;
        $this->ventanillaRepository = $ventanillaRepository;
        $this->puntoAtencionRepository = $puntoAtencionRepository;
        $this->usuarioRepository = $usuarioRepository;
        $this->notificationsService = $notificationsService;
        $this->securityService = $securityServices;
        $this->tokenRepository = $tokenRepository;
        $this->environment = $this->getEnvironment();
    }

    /**
     * Crea un usuario
     *
     * @param array $params Array con los datos a crear
     * @param callback $sucess Callback para devolver respuesta exitosa
     * @param callback $error Callback para devolver respuesta fallida
     * @return mixed
     * @throws \Exception
     */
    public function create($params, $sucess, $error)
    {
        $validateResult = $this->userValidator->validarCreate($params);
        $repository = null;
        $userData = null;

        if (! $validateResult->hasError()) {
            $usuarioFactory = new UsuarioFactoryStrategy(
                $this->userValidator,
                $this->ventanillaRepository,
                $this->puntoAtencionRepository,
                $this->adminRepository,
                $this->agenteRepository,
                $this->responsableRepository,
                $params['rol']
            );
            $repository = $usuarioFactory->getRepository();
            $validateResult = $usuarioFactory->create($params);

            $userData = [
                'title' => '¡Usuario creado con éxito!',
                'email' => null,
                'password' => null,
                'base_url' => $this->getParameter('usuarios_url')
            ];

            // securizar contraseña
            if (! $validateResult->hasError()) {
                $user = $validateResult->getEntity()->getUser();
                // user data
                $userData['email'] = $user->getUsername();
                $userData['password'] = $user->getPassword();
                // make the password secure
                $usuarioFactory->securityPassword($user, $this->getSecurityPassword());
            }
        }

        return $this->processResult(
            $validateResult,
            function ($entity) use ($sucess, $repository, $userData) {
                return call_user_func_array($sucess, [$repository->save($entity), $userData]);
            },
            $error
        );
    }

    /**
     * Listado de todos los usuarios
     *
     * @param integer $limit Cantidad máxima de registros a retornar
     * @param integer $offset Cantidad de registros a saltar
     * @return object
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findAllPaginate($limit, $offset)
    {
        $usuarios = $this->usuarioRepository->findAllPaginate($offset, $limit);
        $result = [];
        foreach ($usuarios as $usuario) {
            $result[] = [
                'id' => $usuario->getUser()->getId(),
                'nombre' => $usuario->getNombre(),
                'apellido' => $usuario->getApellido(),
                'rol' => $usuario->getUser()->getRol(),
                'username' => $usuario->getUser()->getUsername(),
                'puntoAtencion' => [
                    'id' => $usuario->getPuntoAtencionId(),
                    'nombre' => $usuario->getNombrePuntoAtencion()
                ]
            ];
        }
        $resultset = [
            'resultset' => [
                'count' => $this->usuarioRepository->getTotal(),
                'offset' => $offset,
                'limit' => $limit
            ]
        ];

        return $this->respuestaData($resultset, $result);
    }

    /**
     * Obtiene un usuario
     *
     * @param integer $id Identificador único de Usuario
     * @param callback onError Callback para devolver respuesta fallida
     * @return object
     */
    public function get($id, $onError)
    {
        $result = [];
        $user = $this->userRepository->find($id);
        $validateResultado = $this->userValidator->validarUser($user);

        if (! $validateResultado->hasError()) {
            $usuarioRepository = new UsuarioStrategy(
                $this->agenteRepository,
                $this->responsableRepository,
                $this->adminRepository
            );
            $result = $usuarioRepository->getUser($user);
        }

        return $this->processError(
            $validateResultado,
            function () use ($result) {
                return $this->respuestaData([], $result);
            },
            $onError
        );
    }


    /**
     * Edita un usuario
     *
     * @param array $params Arreglo con los datos a modificar
     * @param integer $idUser Identificador único del usuario a modificar
     * @param callback $sucess Callback para devolver respuesta exitosa
     * @param callback $error Callback para devolver respuesta fallida
     * @return mixed
     * @throws \Exception
     */
    public function edit($params, $idUser, $sucess, $error)
    {
        $user = $this->userRepository->find($idUser);
        $validateResult = $this->userValidator->validarUsuario($user);
        $repository = null;
        if (! $validateResult->hasError()) {
            $userSync = new UsuarioSyncStrategy(
                $this->userValidator,
                $this->adminRepository,
                $this->adminValidator,
                $this->agenteRepository,
                $this->agenteValidator,
                $this->responsableRepository,
                $this->responsableValidator,
                $this->ventanillaRepository,
                $this->puntoAtencionRepository,
                $user->getRol()
            );
            $repository = $userSync->getRepository();
            $validateResult = $userSync->edit($idUser, $params);
        }

        return $this->processResult(
            $validateResult,
            function () use ($sucess, $repository) {
                return call_user_func($sucess, $repository->flush());
            },
            $error
        );
    }

    /**
     * Elimina un usuario
     *
     * @param integer $id Identificador único del área
     * @param callback $success Callback para devolver respuesta exitosa
     * @param callback $error Callback para devolver respuesta fallida
     * @return mixed
     * @throws \Exception
     */
    public function delete($id, $success, $error)
    {
        $user = $this->userRepository->find($id);
        $validateResult = $this->userValidator->validarUsuario($user);
        $repository = null;
        if (! $validateResult->hasError()) {
            $userSync = new UsuarioSyncStrategy(
                $this->userValidator,
                $this->adminRepository,
                $this->adminValidator,
                $this->agenteRepository,
                $this->agenteValidator,
                $this->responsableRepository,
                $this->responsableValidator,
                $this->ventanillaRepository,
                $this->puntoAtencionRepository,
                $user->getRol()
            );
            $repository = $userSync->getRepository();
            $validateResult = $userSync->delete($id);
        }

        return $this->processResult(
            $validateResult,
            function ($entity) use ($success, $repository) {
                return call_user_func($success, $repository->remove($entity));
            },
            $error
        );
    }

    /**
     * Modifica la contraseña de un Usuario
     *
     * @param array $params arreglo con los datos para modificar la contraseña
     * @param string $authorization token del usuario logueado
     * @param callback $onSuccess Callback para devolver respuesta exitosa
     * @param callback $onError Callback para devolver respuesta fallida
     * @return mixed
     * @throws \Exception
     */
    public function modificarPassword($params, $authorization, $onSuccess, $onError)
    {
        $userData =[];
        $repository = $this->userRepository;
        $token = md5($authorization);

        $validateResult = $this->userValidator->validarModificarPassword($params);

        if (! $validateResult->hasError()) {

            $user = $validateResult->getEntity();

            if (isset($params['password'])) {
                $validateResult = $this->userValidator->validarModificarContrasena($user, $params['password']);
            }

            if(! $validateResult->hasError()) {
                $validateResult->setEntity($user);
                // make the password secure
                $user->setPassword($this->encoder->encodePassword($user, $params['nuevoPassword']));
            }
            $tokenFactory = new TokenFactory($this->tokenRepository);
            $validateToken = $tokenFactory->insert($token);
            $token = $validateToken->getEntity();
        }

        return $this->processResult(
            $validateResult,
            function () use ($token, $onSuccess, $repository, $userData) {
                return call_user_func_array($onSuccess, [$repository->flush(), $this->tokenRepository->save($token), $userData]);
            },
            $onError
        );
    }

    /**
     * Envía un mail para recuperar contraseña
     *
     * @param array $params arreglo con los datos para recuperar la contraseña
     * @param callback $onSuccess Indica si tuvo éxito o no
     * @param string $onError Mensaje con el error
     * @return mixed
     * @throws \Doctrine\ORM\ORMException
     * @throws \Exception
     */
    public function envioRecuperarPassword($params, $onSuccess, $onError)
    {
        $response = null;
        $user = $this->userRepository->findOneByUsername($params['username']);
        $validateResult = $this->userValidator->validarUsuario($user);

        if (! $validateResult->hasError()) {
            $usuario = $this->usuarioRepository->findOneByUser($user->getId());
            $token = $this->securityService->getTokenMail($user);

            $userData = [
                'url' => $this->getParameter('usuarios_url') . '/reset/' . $token,
                'nombre' => $usuario->getNombre(),
                'email' => $user->getUsername()
            ];

            $response = $this->enviarEmailUsuario($userData, 'password');

            // agregamos el token a la respuesta solo si estamos en dev
            if ($this->environment == 'dev') {
                $response['token'] = $token;
            }
        }

        return $this->processResult(
            $validateResult,
            function () use ($onSuccess, $response) {
                return call_user_func($onSuccess, $response);
            },
            $onError
        );
    }

    /**
     * Envía mail al usuario
     *
     * @param array $userData Arreglo con los datos del usuario
     * @param $template | La plantilla del email a enviar
     * @return mixed|array|NULL|string
     */
    public function enviarEmailUsuario($userData, $template)
    {
        try {
            return $this->notificationsService->enviarNotificacion(
                $this->notificationsService->getEmailTemplate($template),
                $userData['email'],
                '20359715286',
                $userData
            );
        } catch (\Exception $exception) {

        }
    }
}
