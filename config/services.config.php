<?php
namespace Frontend42;

use Frontend42\Event\PageEventListener;
use Frontend42\Event\Service\PageEventListenerFactory;
use Frontend42\Event\Service\PageEventManagerFactory;
use Frontend42\Middleware\FrontendMiddleware;
use Frontend42\Middleware\Service\FrontendMiddlewareFactory;
use Frontend42\PageType\Service\PageTypePluginManager;
use Frontend42\PageType\Service\PageTypePluginManagerFactory;
use Frontend42\Router\Service\HttpRouterFactory;
use Frontend42\View\Helper\Service\CurrentPageFactory;
use Frontend42\View\Helper\Service\CurrentSitemapFactory;
use Frontend42\View\Helper\Service\PageFactory;
use Frontend42\View\Helper\Service\SitemapFactory;
use Zend\Router\Http\TreeRouteStack;

return [
    'service_manager' => [
        'factories' => [
            PageTypePluginManager::class        => PageTypePluginManagerFactory::class,
            PageEventListener::class            => PageEventListenerFactory::class,
            TreeRouteStack::class               => HttpRouterFactory::class,
            FrontendMiddleware::class           => FrontendMiddlewareFactory::class,
            'Frontend42\Page\EventManager'      => PageEventManagerFactory::class,
        ],
    ],
    'view_helpers' => [
        'factories'  => [
            'currentPage'                       => CurrentPageFactory::class,
            'currentSitemap'                    => CurrentSitemapFactory::class,
            'page'                              => PageFactory::class,
            'sitemap'                           => SitemapFactory::class,
        ],
    ],
];
