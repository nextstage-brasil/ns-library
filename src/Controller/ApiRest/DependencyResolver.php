<?php

namespace NsLibrary\Controller\ApiRest;

use Exception;
use NsUtil\Api;
use ReflectionClass;
use stdClass;

class DependencyResolver
{
    private $resolvedInstances = [];

    private $api;

    /**
     * @var array $bind
     * A private associative array that holds the mappings between class
     */
    public static array $bind = [];

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
                    $dependencyClassName = self::$bind[$dependencyClass->getName()] ?? $dependencyClass->getName();
                    $dependencyInstance = $this->resolve($dependencyClassName);
                    $dependencies[] = $dependencyInstance;
                    break;
                case $dependencyName === 'id':
                    $dependencies[] = (int) $this->api->getRest()['id'];
                    break;
                case $dependencyName === 'nsModelObject':
                    $dependencies[] = new stdClass();
                    break;
                case $dependencyName === 'dd':
                case $dependencyName === 'data':
                case $dependencyName === 'dados':
                    $dependencies[] = $this->api->getBody();
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
