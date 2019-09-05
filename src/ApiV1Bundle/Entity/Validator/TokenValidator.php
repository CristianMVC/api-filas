<?php
/**
 * Created by PhpStorm.
 * User: jtibi
 * Date: 18/1/2018
 * Time: 11:33 AM
 */

namespace ApiV1Bundle\Entity\Validator;


use ApiV1Bundle\Helper\JWToken;
use ApiV1Bundle\Repository\TokenRepository;
use ApiV1Bundle\Repository\UserRepository;

/**
 * Class TokenValidator
 * @package ApiV1Bundle\Entity\Validator
 */
class TokenValidator extends SNCValidator
{
    private $userRepository;
    private $tokenRepository;
    private $jwtoken;


    /**
     * TokenValidator constructor.
     * @param UserRepository $userRepository
     * @param TokenRepository $tokenRepository
     * @param JWToken $jwtoken
     */
    public function __construct(
        UserRepository $userRepository,
        TokenRepository $tokenRepository,
        JWToken $jwtoken
    )
    {
        $this->userRepository = $userRepository;
        $this->tokenRepository = $tokenRepository;
        $this->jwtoken = $jwtoken;
    }


    /**
     * Valida que el token valido de un usuario existente
     * @param $authorization
     * @return ValidateResultado
     * @throws \Doctrine\ORM\ORMException
     */
    public function validarToken($authorization)
    {
        $errors = [];
        $dataToken = $this->validarAuthorization($authorization);

        if (! $dataToken->isValid()) {
            $errors[] = 'Token invalido';
            return  new ValidateResultado(null, $errors);
        }

        $token = preg_split('/\\s+/', $authorization);

        $userID = $this->jwtoken->getUid($token[1]);

        if (isset($userID)) {
            $user = $this->userRepository->find($userID);
        } else {
            $errors[] = 'El Token no es de un usuario valido.';
            return  new ValidateResultado(null, $errors);
        }

        return new ValidateResultado($user, []);
    }

    /**
     * Validar authorization
     *
     * @param string $authorization token del usuario logueado
     * @return mixed
     * @throws \Doctrine\ORM\ORMException
     */
    public function validarAuthorization($authorization)
    {
        $token = md5($authorization);
        $tokenCancelado = $this->tokenRepository->findOneByToken($token);
        if ($authorization) {
            list($bearer, $token) = explode(' ', $authorization);
            $token = str_replace('"', '', $token);
        }
        return $this->jwtoken->validate($token, $tokenCancelado);
    }
}
