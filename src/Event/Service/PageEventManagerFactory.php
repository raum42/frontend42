<?php
namespace Frontend42\Event\Service;

use Frontend42\Event\PageEvent;
use Frontend42\PageType\Provider\PageTypeProvider;
use Interop\Container\ContainerInterface;
use Interop\Container\Exception\ContainerException;
use Zend\EventManager\EventManager;
use Zend\ServiceManager\Exception\ServiceNotCreatedException;
use Zend\ServiceManager\Exception\ServiceNotFoundException;
use Zend\ServiceManager\Factory\FactoryInterface;

class PageEventManagerFactory implements FactoryInterface
{
    /**
     * Create an object
     *
     * @param  ContainerInterface $container
     * @param  string $requestedName
     * @param  null|array $options
     * @return object
     * @throws ServiceNotFoundException if unable to resolve the service.
     * @throws ServiceNotCreatedException if an exception is raised when
     *     creating a service.
     * @throws ContainerException if any other error occurs
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $eventManager = new EventManager();
        $eventManager->setEventPrototype(new PageEvent($container->get(PageTypeProvider::class)));

        return $eventManager;
    }
}
