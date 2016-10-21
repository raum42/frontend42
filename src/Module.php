<?php
namespace Frontend42;

use Admin42\ModuleManager\Feature\AdminAwareModuleInterface;
use Admin42\ModuleManager\GetAdminConfigTrait;
use Core42\ModuleManager\Feature\CliConfigProviderInterface;
use Core42\ModuleManager\GetConfigTrait;
use Frontend42\Event\PageEventListener;
use Zend\EventManager\EventInterface;
use Zend\ModuleManager\Feature\BootstrapListenerInterface;
use Zend\ModuleManager\Feature\ConfigProviderInterface;
use Zend\ModuleManager\Feature\InitProviderInterface;
use Zend\ModuleManager\ModuleEvent;
use Zend\ModuleManager\ModuleManagerInterface;
use Zend\Stdlib\ArrayUtils;
use Zend\Stdlib\Glob;

class Module implements
    ConfigProviderInterface,
    AdminAwareModuleInterface,
    CliConfigProviderInterface,
    BootstrapListenerInterface,
    InitProviderInterface
{
    use GetConfigTrait;
    use GetAdminConfigTrait;

    /**
     * @return array
     */
    public function getCliConfig()
    {
        $config = [];
        $configPath = dirname((new \ReflectionClass($this))->getFileName()) . '/../config/cli/*.config.php';

        $entries = Glob::glob($configPath);
        foreach ($entries as $file) {
            $config = ArrayUtils::merge($config, include_once $file);
        }

        return $config;
    }

    /**
     * Initialize workflow
     *
     * @param  ModuleManagerInterface $manager
     * @return void
     */
    public function init(ModuleManagerInterface $manager)
    {
        $events = $manager->getEventManager();
        $events->attach(ModuleEvent::EVENT_MERGE_CONFIG, array($this, 'addPageTypes'));
        $events->attach(ModuleEvent::EVENT_MERGE_CONFIG, array($this, 'addBlocks'));
    }

    /**
     * @param ModuleEvent $e
     */
    public function addBlocks(ModuleEvent $e)
    {
        $configListener = $e->getConfigListener();
        $config         = $configListener->getMergedConfig(false);

        if (empty($config['blocks']['search_path']) || !is_array($config['blocks']['search_path'])) {
            return;
        }

        foreach ($config['blocks']['search_path'] as $path) {
            $entries = Glob::glob($path);
            foreach ($entries as $file) {
                $block = require_once $file;

                if (empty($block['name'])) {
                    continue;
                }

                $config['blocks']['blocks'][$block['name']] = $block;
            }
        }

        $configListener->setMergedConfig($config);
    }

    /**
     * @param ModuleEvent $e
     */
    public function addPageTypes(ModuleEvent $e)
    {
        $configListener = $e->getConfigListener();
        $config         = $configListener->getMergedConfig(false);

        if (empty($config['page_types']['search_path']) || !is_array($config['page_types']['search_path'])) {
            return;
        }

        foreach ($config['page_types']['search_path'] as $path) {
            $entries = Glob::glob($path);
            foreach ($entries as $file) {
                $pageType = require_once $file;

                if (empty($pageType['name'])) {
                    continue;
                }

                $config['page_types']['page_types'][$pageType['name']] = $pageType;
            }
        }

        $configListener->setMergedConfig($config);
    }

    /**
     * Listen to the bootstrap event
     *
     * @param EventInterface $e
     * @return array
     */
    public function onBootstrap(EventInterface $e)
    {
        $e->getApplication()
            ->getServiceManager()
            ->get(PageEventListener::class)
            ->attach($e->getApplication()->getServiceManager()->get('Frontend42\Page\EventManager'));
    }
}
