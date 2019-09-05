<?php
namespace ApiV1Bundle\EventListener;

use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class DoctrineExtensionListener
 * @package ApiV1Bundle\EventListener
 */
class DoctrineExtensionListener implements ContainerAwareInterface
{

    protected $container;

    /**
     * Asigna el container
     *
     * @param ContainerInterface|null $container
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * @param GetResponseEvent $event
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        $em = $this->container->get('doctrine')->getManager();
        $em->getConfiguration()->addFilter('soft-deleteable', 'Gedmo\SoftDeleteable\Filter\SoftDeleteableFilter');
        $em->getFilters()->enable('soft-deleteable');
    }
}
