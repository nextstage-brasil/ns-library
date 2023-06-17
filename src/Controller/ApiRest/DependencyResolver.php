<?php

namespace NsLibrary\Controller\ApiRest;

use Exception;
use NsUtil\Api;
use ReflectionClass;

class DependencyResolver
{
    private $resolvedInstances = [];

    private $api;

    public function __construct()
    {
        $this->api = new Api();
    }

    public function resolve($dependencyClass)
    {
        if (isset($this->resolvedInstances[$dependencyClass])) {
            return $this->resolvedInstances[$dependencyClass];
        }

        $reflectionClass = new ReflectionClass($dependencyClass);
        $constructor = $reflectionClass->getConstructor();

        if ($constructor === null) {
            $instance = new $dependencyClass();
            $this->resolvedInstances[$dependencyClass] = $instance;
            return $instance;
        }

        $parameters = $constructor->getParameters();
        $dependencies = [];

        foreach ($parameters as $parameter) {
            $dependencyName = $parameter->getName();
            $dependencyClass = $parameter->getType();


            switch (true) {
                case $dependencyClass !== null && !$dependencyClass->isBuiltin():
                    $dependencyClassName = $dependencyClass->getName();
                    $dependencyInstance = $this->resolve($dependencyClassName);
                    $dependencies[] = $dependencyInstance;
                    break;
                case $dependencyName === 'id':
                    $dependencies[] = (int) $this->api->getRest()['id'];
                    break;
                case $dependencyClass->getName() === 'array':
                    $dependencies[] = $this->api->getBody();
                    break;
                case $dependencyClass->isBuiltin():
                    throw new Exception("Dependency '$dependencyName' is builtin and could not be resolved.");
                    break;
                default:
                    throw new Exception("Dependency '$dependencyName' could not be resolved.");
            }
        }

        $instance = $reflectionClass->newInstanceArgs($dependencies);
        $this->resolvedInstances[$dependencyName] = $instance;
        return $instance;
    }
}
