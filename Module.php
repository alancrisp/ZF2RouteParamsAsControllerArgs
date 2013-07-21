<?php
namespace RouteParamsAsControllerArgs;

use Zend\Mvc\Exception;
use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;

class Module
{
    public function onBootstrap(MvcEvent $e)
    {
        $serviceManager     = $e->getApplication()->getServiceManager();
        $sharedEventManager = $serviceManager->get('SharedEventManager');
        $sharedEventManager->attach('Zend\Mvc\Controller\AbstractController', MvcEvent::EVENT_DISPATCH, array($this, 'onDispatch'), 100);
    }

    public function onDispatch(MvcEvent $e)
    {
        $routeMatch = $e->getRouteMatch();
        if (!$routeMatch) {
            throw new Exception\DomainException('Missing route matches; unsure how to retrieve action');
        }

        $controller = $e->getTarget();
        $action     = $routeMatch->getParam('action', 'not-found');
        $method     = $controller::getMethodFromAction($action);

        if (!method_exists($controller, $method)) {
            $method = 'notFoundAction';
        }

        $methodArgs  = array();
        $routeParams = $routeMatch->getParams();

        $reflectionClass  = new \ReflectionClass(get_class($controller));
        $reflectionMethod = $reflectionClass->getMethod($method);

        foreach ($reflectionMethod->getParameters() as $param) {
            $paramName = $param->getName();
            if (array_key_exists($paramName, $routeParams)) {
                $argValue = $routeParams[$paramName];
            } elseif ($param->isOptional()) {
                $argValue = $param->getDefaultValue();
            } else {
                throw new Exception\DomainException('Mandatory action parameter \'' . $paramName . '\' not found in matched route');
            }

            $methodArgs[] = $argValue;
        }

        $actionResponse = call_user_func_array(array($controller, $method), $methodArgs);

        $e->setResult($actionResponse);

        return $actionResponse;
    }

    public function getConfig()
    {
        return array();
    }

    public function getAutoloaderConfig()
    {
        return array();
    }
}
