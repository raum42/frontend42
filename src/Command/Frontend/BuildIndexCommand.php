<?php
namespace Frontend42\Command\Frontend;

use Core42\Command\AbstractCommand;
use Core42\I18n\Localization\Localization;
use Frontend42\Model\Page;
use Frontend42\Model\Sitemap;
use Frontend42\Page\Data\Data;
use Frontend42\Selector\SitemapSelector;

class BuildIndexCommand extends AbstractCommand
{
    /**
     * @var boolean
     */
    protected $caching = true;

    /**
     * @var string
     */
    protected $routePrefix = "frontend";

    /**
     * @var Data
     */
    protected $data;

    /**
     * @var bool
     */
    protected $transaction = false;

    /**
     * @var bool
     */
    protected $enableResult =  false;

    protected $result = [
        'navigation' => [],
        'routing' => [],
        'page' => [],
        'sitemap' => [],
        'pageRoute' => [],
        'localMapping' => [],
        'handleMapping' => [],
    ];

    /**
     * @param boolean $caching
     * @return $this
     */
    public function setCaching($caching)
    {
        $this->caching = $caching;

        return $this;
    }

    /**
     * @param $enableResult
     * @return $this
     */
    public function enableResult($enableResult)
    {
        $this->enableResult = $enableResult;

        return $this;
    }

    /**
     * @param $routePrefix
     * @return $this
     */
    public function setRoutePrefix($routePrefix)
    {
        $this->routePrefix = $routePrefix;

        return $this;
    }

    protected function preExecute()
    {
        $this->data = $this->getServiceManager()->get(Data::class);
    }

    /**
     * @return mixed
     */
    protected function execute()
    {
        $routes = [];
        $navigation = [];

        foreach ($this->getServiceManager()->get(Localization::class)->getAvailableLocales() as $locale) {
            $tree = $this
                ->getSelector(SitemapSelector::class)
                ->setLocale($locale)
                ->getResult();

            $index = $this->buildRecursive($tree, $locale, $this->routePrefix);
            $routes = array_merge($index['routing'], $routes);
            $navigation[$locale] = $index['navigation'];

            if ($this->caching === true) {
                $this->data->writeNavigation($navigation[$locale], $locale);
            }
        }

        if ($this->caching === true) {
            $this->data->writeRouting($routes);
        }

        if ($this->enableResult === true) {
            $this->result['navigation'] = $navigation;
            $this->result['routing'] = $routes;
        }

        return $this->result;
    }

    protected function buildRecursive($tree, $locale, $routePrefix)
    {
        $navigation = [];
        $routing = [];

        foreach ($tree as $item) {
            /** @var Page $page */
            $page = $item['page'];

            if ($this->caching === true) {
                $this->data->writePage($page);
            }
            if ($this->enableResult === true) {
                $this->result['page'][$page->getId()] = $page;
            }

            /** @var Sitemap $sitemap */
            $sitemap = $item['sitemap'];
            if ($this->caching === true) {
                $this->data->writeSitemap($sitemap);
            }
            if ($this->enableResult === true) {
                $this->result['page'][$page->getId()] = $page;
            }

            $route = $routePrefix . '/' . $page->getId();

            $routing[$page->getId()] = $page->getRoute();

            $navPage = [
                'options' => [
                    'label'     => $page->getName(),
                    'pageId'    => $page->getId(),
                    'sitemapId' => $sitemap->getId(),
                    'order'     => $sitemap->getOrderNr(),
                    'route'     => $route
                ]
            ];

            if ($this->caching === true) {
                $this->data->writePageRoute($page->getId(), $route);
            }
            if ($this->enableResult === true) {
                $this->result['pageRoute'][$page->getId()] = $route;
            }

            if ($this->caching === true) {
                $this->data->writeLocaleMapping($sitemap->getId(), $page->getLocale(), $page->getId());
            }
            if ($this->enableResult === true) {
                $this->result['localMapping'][$sitemap->getId()][$page->getLocale()] = $page->getId();
            }

            if (strlen($sitemap->getHandle()) && $this->caching === true) {
                $this->data->writeHandleMapping($sitemap->getHandle(), $page->getLocale(), $page->getId());
            }
            if (strlen($sitemap->getHandle()) && $this->enableResult === true) {
                $this->result['handleMapping'][$sitemap->getHandle()][$page->getLocale()] = $page->getId();
            }

            if (!empty($item['children'])) {
                $childrenItems = $this->buildRecursive($item['children'], $locale, $route);

                if (!empty($childrenItems['routing']) && $sitemap->getExclude() !== true) {
                    $routing[$route]['child_routes'] = $childrenItems['routing'];
                }

                if (!empty($childrenItems['navigation'])) {
                    $navPage['pages'] = $childrenItems['navigation'];
                }
            }

            $navigation[] = $navPage;
        }

        return [
            'navigation' => $navigation,
            'routing' => $routing,
        ];
    }
}
