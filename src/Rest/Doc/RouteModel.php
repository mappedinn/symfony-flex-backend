<?php
declare(strict_types=1);
/**
 * /src/Rest/Doc/RouteModel.php
 *
 * @author  TLe, Tarmo Leppänen <tarmo.leppanen@protacon.com>
 */
namespace App\Rest\Doc;

use Symfony\Component\Routing\Route;

/**
 * Class RouteModel
 *
 * @package App\Rest\Doc
 * @author  TLe, Tarmo Leppänen <tarmo.leppanen@protacon.com>
 */
class RouteModel
{
    /**
     * @var string
     */
    private $controller;

    /**
     * @var string
     */
    private $method;

    /**
     * @var string
     */
    private $httpMethod;

    /**
     * @var string
     */
    private $baseRoute;

    /**
     * @var Route
     */
    private $route;

    /**
     * @var array
     */
    private $methodAnnotations;

    /**
     * @var array
     */
    private $controllerAnnotations;

    /**
     * @return string
     */
    public function getController(): string
    {
        return $this->controller;
    }

    /**
     * @param string $controller
     *
     * @return RouteModel
     */
    public function setController(string $controller): RouteModel
    {
        $this->controller = $controller;

        return $this;
    }

    /**
     * @return string
     */
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * @param string $method
     *
     * @return RouteModel
     */
    public function setMethod(string $method): RouteModel
    {
        $this->method = $method;

        return $this;
    }

    /**
     * @return string
     */
    public function getHttpMethod(): string
    {
        return $this->httpMethod;
    }

    /**
     * @param string $httpMethod
     *
     * @return RouteModel
     */
    public function setHttpMethod(string $httpMethod): RouteModel
    {
        $this->httpMethod = $httpMethod;

        return $this;
    }

    /**
     * @return string
     */
    public function getBaseRoute(): string
    {
        return $this->baseRoute;
    }

    /**
     * @param string $baseRoute
     *
     * @return RouteModel
     */
    public function setBaseRoute(string $baseRoute): RouteModel
    {
        $this->baseRoute = $baseRoute;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getRoute(): Route
    {
        return $this->route;
    }

    /**
     * @param Route $route
     *
     * @return RouteModel
     */
    public function setRoute(Route $route): RouteModel
    {
        $this->route = $route;

        return $this;
    }

    /**
     * @return array
     */
    public function getMethodAnnotations(): array
    {
        return $this->methodAnnotations;
    }

    /**
     * @param array $methodAnnotations
     *
     * @return RouteModel
     */
    public function setMethodAnnotations(array $methodAnnotations): RouteModel
    {
        $this->methodAnnotations = $methodAnnotations;

        return $this;
    }

    /**
     * @return array
     */
    public function getControllerAnnotations(): array
    {
        return $this->controllerAnnotations;
    }

    /**
     * @param array $controllerAnnotations
     *
     * @return RouteModel
     */
    public function setControllerAnnotations(array $controllerAnnotations): RouteModel
    {
        $this->controllerAnnotations = $controllerAnnotations;

        return $this;
    }
}
