<?php

namespace ApiV1Bundle\ApplicationServices;

use ApiV1Bundle\Entity\Validator\ValidateResultado;
use ApiV1Bundle\Entity\Validator\TokenValidator;
use ApiV1Bundle\Helper\JWToken;
use ApiV1Bundle\Repository\UsuarioRepository;
use ApiV1Bundle\Repository\PuntoAtencionRepository;

class RolesServices
{
    /** @var TokenValidator $tokenValidator */
    private $tokenValidator;

    /** @var UsuarioRepository $usuarioRepository */
    private $usuarioRepository;

    /** @var JWToken $jwtoken */
    private $jwtoken;

    /** @var PuntoAtencionRepository $puntoAtencionRepository */
    private $puntoAtencionRepository;

    /**
     * RolesServices constructor.
     * @param TokenValidator $tokenValidator
     * @param UsuarioRepository $usuarioRepository
     * @param JWToken $jwtoken
     * @param PuntoAtencionRepository $puntoAtencionRepository
     */
    public function __construct(
        TokenValidator $tokenValidator,
        UsuarioRepository $usuarioRepository,
        JWToken $jwtoken,
        PuntoAtencionRepository $puntoAtencionRepository
    ) {
        $this->tokenValidator = $tokenValidator;
        $this->usuarioRepository = $usuarioRepository;
        $this->jwtoken = $jwtoken;
        $this->puntoAtencionRepository = $puntoAtencionRepository;
    }

    /**
     * @param string $authorization Token del usuario logueado
     * @return ValidateResultado
     * @throws \Doctrine\ORM\ORMException
     */
    public function getUsuario($authorization)
    {
        $validateResultado = $this->tokenValidator->validarToken($authorization);
        if (! $validateResultado->hasError()) {
            $token = preg_split('/\\s+/', $authorization);
            $userID = $this->jwtoken->getUid($token[1]);
            if (isset($userID)) {
                $usuario = $this->usuarioRepository->findOneBy(['user' => $userID]);
                return new ValidateResultado($usuario, []);
            }
        }
        return $validateResultado;
    }

    /**
     * Obtiene el punto de atención (SNC) del usuario logueado (PDA SNT)
     *
     * @param string $authorization token del usuario logueado
     * @return ValidateResultado
     * @throws \Doctrine\ORM\ORMException
     */
    public function getPuntoAtencion($authorization)
    {
        $validateResultado = $this->tokenValidator->validarToken($authorization);
        if (! $validateResultado->hasError()) {
            $token = preg_split('/\\s+/', $authorization);
            $puntoAtencionIdSNT = $this->jwtoken->getTokenPuntoAtencion($token[1]);
            $puntoAtencion = $this->puntoAtencionRepository->findOneByPuntoAtencionIdSnt($puntoAtencionIdSNT);
            $validateResultado = $this->tokenValidator->validarPuntoAtencion($puntoAtencion);
            if (! $validateResultado->hasError()) {
                return new ValidateResultado($puntoAtencion, []);
            }
        }
        return $validateResultado;
    }
}
