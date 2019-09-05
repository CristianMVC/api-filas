<?php

namespace ApiV1Bundle\Controller;

use FOS\RestBundle\Controller\Annotations\Get;
use ApiV1Bundle\Entity\Response\Respuesta;

/**
 * Class DefaultController
 * @package ApiV1Bundle\Controller
 *
 * @author Fausto Carrera <fcarrera@hexacta.com>
 */

class DefaultController extends ApiController
{

    /**
     * Controller por defecto
     *
     * @return Respuesta
     * @Get("/", name="api_index")
     */
    public function indexAction()
    {
        return $this->respuestaData(null, []);
    }

    /**
     * Version de la API
     *
     * @return Respuesta
     * @Get("/version", name="api_version")
     */
    public function versionAction()
    {
        return $this->respuestaData(null, [
            'API' => $this->container->get('kernel')->getEnvironment() == 'prod' ? $this->container->getParameter('api_prod_name') : $this->container->getParameter('api_name'),
            'version' => $this->container->getParameter('api_version'),
            'ambiente' => $this->container->get('kernel')->getEnvironment()
        ]);
    }
}
