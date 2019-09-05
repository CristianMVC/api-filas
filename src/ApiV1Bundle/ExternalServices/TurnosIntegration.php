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
class TurnosIntegration extends Integration
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
     * Obtiene la lista de turnos
     *
     * @param array $params Array con los datos
     * @param string $codigoTurnos Código de un turno (opcional)
     * @return ValidateResultado
     */
    public function getListTurnos($params, $codigoTurnos)
    {
        $parameters = [
            'puntoatencion' => (int)$params['puntoatencion'],
            'fecha' => $params['fecha'],
            'offset' => $params['offset'],
            'limit' => $params['limit'],
            'codigos' => $codigoTurnos,
            'estado'=>(isset($params['estado']))?$params['estado']:null
        ];
        $url = $this->integrationService->getUrl('snt', 'turnos.fecha');
        return $this->integrationService->post($url, $parameters);
    }

    /**
     * Obtiene los datos de un turno de SNT
     *
     * @param integer $id Identificador del turno
     * @return array
     */
    public function getItemTurnoSNT($id)
    {
        $url = $this->integrationService->getUrl('snt', 'turnos');
        $validateResultado =  $this->integrationService->post($url, ['turno_id' => $id]);

        if (! $validateResultado->hasError()) {
            return $validateResultado->getEntity()['result'];
        }
        return [];
    }

    /**
     * Busqueda de turnos en el Sistema Nacional de Turnos
     *
     * @param $params
     * @return ValidateResultado
     */
    public function searchTurnoSNT($params)
    {
        $url = $this->integrationService->getUrl('snt', 'turnos.buscar');
        return  $this->integrationService->post($url, $params);
    }

}