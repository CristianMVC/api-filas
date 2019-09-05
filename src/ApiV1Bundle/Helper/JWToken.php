<?php
namespace ApiV1Bundle\Helper;

use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Parser;
use Lcobucci\JWT\ValidationData;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Symfony\Component\DependencyInjection\Container;

/**
 * Class JWToken
 * @package ApiV1Bundle\Helper
 * Docs: https://github.com/lcobucci/jwt/blob/3.2/README.md
 */
class JWToken
{
    private $secret;
    private $builder;
    private $parser;
    private $signer;
    private $validationData;
    private $isValid = false;
    private $roles = null;
    private $ttl = 7200;
    private $domain;

    /**
     * JWToken constructor
     *
     * @param Container $container
     * @param $secret
     * @param Builder $builder
     * @param Parser $parser
     * @param ValidationData $validationData
     */
    public function __construct(
        Container $container,
        $secret,
        Builder $builder,
        Parser $parser,
        ValidationData $validationData
    ) {
        $this->secret = $secret;
        $this->token = $builder;
        $this->parser = $parser;
        $this->validationData = $validationData;
        $this->signer = new Sha256();
        $this->domain = $container->getParameter('jwt_domain');
    }
    /**
     * Generar JWToken
     *
     * @return \Lcobucci\JWT\Builder
     */
    public function getToken($uid, $username, $role)
    {
        $token = $this->token;
        $token->setIssuer($this->getDomain());
        $token->setAudience($this->getDomain());
        $token->setIssuedAt(time());
        $token->setExpiration(time() + $this->ttl);
        $token->setId(md5($this->secret . $this->getDomain()));
        $token->set('timestamp', time());
        $token->set('uid', $uid);
        $token->set('username', $username);
        $token->set('role', $role);
        $token->sign($this->signer, $this->secret);
        return (string) $token->getToken();
    }

    /**
     * Validar token
     *
     * @param $tokenString
     * @return boolean
     */
    public function validate($tokenString, $tokenCancelado)
    {
        try {
            $token = $this->parseToken($tokenString);
            $isValid = $token->validate($this->validationData());
            if ($isValid && is_null($tokenCancelado)) {
                // verify the token signature
                if ($token->verify($this->signer, $this->secret)) {
                    $this->isValid = $isValid;
                    $this->role = $token->getClaim('role');
                }
            }
        } catch (\Exception $e) {
            // do nothing
        }
        return $this;
    }

    /**
     * Is token valid
     *
     * @return boolean
     */
    public function isValid()
    {
        return $this->isValid;
    }

    /**
     * Rol saved on the token
     *
     * @return string
     */
    public function getRol()
    {
        return $this->role;
    }

    /**
     * Parsear token
     *
     * @param $token
     * @return \Lcobucci\JWT\Token
     */
    private function parseToken($token)
    {
        return $this->parser->parse((string) $token);
    }

    /**
     * Datos para validar un token
     *
     * @return \Lcobucci\JWT\ValidationData
     */
    private function validationData()
    {
        $this->validationData->setIssuer($this->getDomain());
        $this->validationData->setAudience($this->getDomain());
        $this->validationData->setId(md5($this->secret . $this->getDomain()));
        return $this->validationData;
    }

    /**
     * Obtener el dominio que genera el token
     *
     * @return string
     */
    public function getDomain()
    {
        return $this->domain;
    }

    /**
     * @param string $tokenString
     * @return string mixed
     */
    public function getPayload($tokenString)
    {
        $parts = explode(' ', $tokenString);
        return $parts[1];
    }

    /**
     * @param string $token
     * @return array
     */
    public function getUID($token)
    {
        $token = (new Parser())->parse((string) $token);
        return $token->getClaim('uid'); // Retrieves the token claims
    }

    /**
     * @param string $token
     * @return mixed
     */
    public function getTokenRol($token)
    {
        $token = (new Parser())->parse((string) $token);
        return $token->getClaim('role');
    }

    /**
     * @param string $token
     * @return array
     */
    public function getTokenPuntoAtencion($token)
    {
        $token = (new Parser())->parse((string) $token);
        return $token->getClaim('puntoatencion','-1'); // Retrieves the token claims
    }
}
