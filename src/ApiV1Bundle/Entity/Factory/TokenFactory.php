<?php
namespace ApiV1Bundle\Entity\Factory;

use ApiV1Bundle\Entity\Validator\ValidateResultado;
use ApiV1Bundle\Entity\Token;
use ApiV1Bundle\Repository\TokenRepository;

/**
 * Class TokenFactory
 * @package ApiV1Bundle\Entity\Factory
 */
class TokenFactory
{
    /** @var \ApiV1Bundle\Repository\TokenRepository TokenRepository  */
    private $tokenRepository;

    /**
     *  TokenFactory construct
     *
     * @param tokenRepository $tokenRepository
     */
    public function __construct(TokenRepository $tokenRepository)
    {
        $this->tokenRepository = $tokenRepository;
    }

    /**
     * Agrega un token al listado de tokens invalidados
     *
     * @param string $token
     * @return ValidateResultado
     */
    public function insert($token)
    {
        $invalidToken = new Token($token);
        return new ValidateResultado($invalidToken, []);
    }
}
