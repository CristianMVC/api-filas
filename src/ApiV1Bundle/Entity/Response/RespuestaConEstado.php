<?php
/**
 * Created by PhpStorm.
 * User: jtibi
 * Date: 19/10/2017
 * Time: 2:52 PM
 */

namespace ApiV1Bundle\Entity\Response;

use Symfony\Component\HttpFoundation\Response;

/**
 * Class RespuestaConEstado
 * @package ApiV1Bundle\Entity
 */
class RespuestaConEstado extends Response
{

    const STATUS_SUCCESS = 'SUCCESS';
    const STATUS_BAD_REQUEST = 'BAD REQUEST';
    const STATUS_NOT_FOUND = 'NOT FOUND';
    const STATUS_FATAL = 'FATAL';
    const STATUS_FORBIDDEN = 'FORBIDDEN';
    const CODE_SUCCESS = parent::HTTP_OK;
    const CODE_BAD_REQUEST = parent::HTTP_BAD_REQUEST;
    const CODE_NOT_FOUND = parent::HTTP_NOT_FOUND;
    const CODE_FATAL = parent::HTTP_BAD_REQUEST;
    const CODE_FORBIDDEN = parent::HTTP_FORBIDDEN;

    /**
     * RespuestaConEstado constructor.
     * @param mixed|string $statusMessage
     * @param int $statusCode
     * @param array $userMsg
     * @param string $devMsg
     * @param string $additional
     */
    public function __construct($statusMessage, $statusCode, $userMsg, $devMsg = '', $additional = '')
    {
        parent::__construct(
            json_encode([
                'code' => $statusCode,
                'status' => $statusMessage,
                'userMessage' => $userMsg,
                'devMessage' => $devMsg,
                'additional' => $additional
            ]),
            $statusCode
        );
    }
}