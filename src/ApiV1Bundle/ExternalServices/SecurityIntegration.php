<?php
/**
 * GrupoTramiteIntegration class
 *
 * @author Fausto Carrera <fcarrera@hexacta.com>
 */
namespace ApiV1Bundle\ExternalServices;

use ApiV1Bundle\Entity\Validator\ValidateResultado;
use Symfony\Component\DependencyInjection\Container;
use ApiV1Bundle\ExternalServices\ExternalService;
use ApiV1Bundle\Mocks\SNTExternalServiceMock;
use ApiV1Bundle\Entity\Validator\CommunicationValidator;

class SecurityIntegration extends Integration
{

    /** @var SNTExternalServiceMock $integrationService */
    private $integrationService;

    /** @var CommunicationValidator $communicationValidator */
    private $communicationValidator;

    /**
     * SecurityIntegration constructor.
     * @param Container $container
     * @param \ApiV1Bundle\ExternalServices\ExternalService $integrationService
     * @param SNTExternalServiceMock $integrationMock
     * @param CommunicationValidator $communicationValidator
     */
    public function __construct(
        Container $container,
        ExternalService $integrationService,
        SNTExternalServiceMock $integrationMock,
        CommunicationValidator $communicationValidator
    ) {
        parent::__construct($container);
        $this->integrationService = $integrationService;
        if ($this->getEnvironment() == 'test') {
            $this->integrationService = $integrationMock;
        }
        $this->communicationValidator = $communicationValidator;
    }

    /**
     * Validamos la comunicación POST
     *
     * @param $params
     * @return ValidateResultado
     */
    public function securePostCommunications($params)
    {
        $url = $this->integrationService->getUrl('snt', 'test');
        return $this->integrationService->post($url, $params);
    }
}
