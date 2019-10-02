<?php
declare(strict_types = 1);

namespace App\Twig;

use Doctrine\Common\Inflector\Inflector;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouterInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class ComponentsExtension extends AbstractExtension
{
    /**
     * @var Route
     */
    protected $router;

    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }
    public function getTokenParsers()
    {
        return [
            new EmbedParser(),
        ];
    }

    public function getFilters()
    {
        return [
            new TwigFilter('tableize', [$this, 'tableize']),
        ];
    }

    public function getFunctions()
    {
        return [
            new TwigFunction('class_name', [$this, 'getClassShortName']),
            new TwigFunction('index_routes', [$this, 'getIndexRoutes']),
            new TwigFunction('static_field_row', [$this, 'getStaticFieldRow'], ['needs_environment' => true, 'is_safe' => ['html']]),
        ];
    }

    public function tableize($word)
    {
        return Inflector::tableize($word);
    }

    public function getClassShortName($object)
    {
        return (new \ReflectionClass($object))->getShortName();
    }

    /**
     * @return array|Route[]
     */
    public function getIndexRoutes()
    {
        $indexRoutes = [];
        foreach ($this->router->getRouteCollection() as $routeName => $route) {
            if (stripos($routeName, '_index') !== false) {
                $indexRoutes[str_replace('_index', '', $routeName)] = $route;
            }
        }
        return $indexRoutes;
    }

    public function getStaticFieldRow(\Twig_Environment $environment, $label, $value)
    {
        return $environment->render('components/static_field_row.html.twig', [
            'label' => $label,
            'value' => $value,
        ]);
    }
}
