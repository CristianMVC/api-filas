<?php

namespace ApiV1Bundle\ExternalServices;

use ApiV1Bundle\Entity\Validator\ValidateResultado;
use Symfony\Component\DependencyInjection\Container;
use ApiV1Bundle\Mocks\SNTExternalServiceMock;
use ApiV1Bundle\ExternalServices\ExternalService;

/**
 * Class TurnosIntegration
 * @package ApiV1Bundle\ExternalServices
 */
class CarteleraIntegration extends Integration
{
    /** @var SNTExternalServiceMock | ExternalService  $integrationService*/
    private $integrationService;

    /**
     * TurnosIntegration constructor
     *
     * @param Container $container
     * @param ExternalService $integrationService
     * @param SNTExternalServiceMock $integrationMock
     */
    public function __construct(
        Container $container,
        ExternalService $integrationService,
        SNTExternalServiceMock $integrationMock
    ) {
        parent::__construct($container);
        $this->integrationService = $integrationService;
        if ($this->getEnvironment() == 'test') {
            $this->integrationService = $integrationMock;
        }
    }

    /**
     * enviar datos del turno y carteleras que lo atienden a NodeJs
     * @param array $params Datos del turno
     * @return array
     */
    public function enviarTurno($params)
    {
        $url = $this->integrationService->getUrl('nodejs', 'turnos.enviar');
        try {
            $response = $this->integrationService->post($url, $params);
            $result['error'] = key_exists('error', $response);
            if ($result['error']) {
                $result['mensaje'] = $response['error'];
            } else {
                $result['mensaje'] = $response;
            }
            return $result;
        } catch (\Exception $exception) {
            return [
                'error' => true,
                'mensaje' => $exception->getMessage()
            ];
        }

    }

}