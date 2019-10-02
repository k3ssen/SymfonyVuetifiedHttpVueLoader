<?php
declare(strict_types=1);

namespace App\Router;

use App\Entity\Interfaces\IdentifiableInterface;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Router as BaseRouter;
use Symfony\Component\Routing\RouterInterface;

class Router implements RouterInterface
{
    /** @var BaseRouter */
    protected $router;

    public function __construct(BaseRouter $router)
    {
        $this->router = $router;
    }

    public static function create(BaseRouter $router) {
        return new Router($router);
    }

    public function generate($name, $parameters = array(), $referenceType = self::ABSOLUTE_PATH)
    {
        if (is_object($parameters)) {
            $idName = 'id';
            if ($parameters instanceof IdentifiableInterface) {
                $idName = $parameters::getIdName();
            }
            $getter = 'get'.ucfirst($idName);
            if (method_exists($parameters, $getter)) {
                $parameters = [
                    $idName => $parameters->$getter(),
                ];
            }
        }
        return $this->router->generate($name, $parameters, $referenceType);
    }

    public function setContext(RequestContext $context)
    {
        $this->router->setContext($context);
    }

    public function getContext()
    {
        return $this->router->getContext();
    }

    public function getRouteCollection()
    {
        return $this->router->getRouteCollection();
    }

    public function match($pathinfo)
    {
        $pathinfo = str_replace('.vue', '', $pathinfo);
        return $this->router->match($pathinfo);
    }
}