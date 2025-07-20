<?php
namespace Core;

use ReflectionClass;

class Container
{
    protected array $bindings = [];
    protected array $instances = [];
    protected array $parameters = []; // <-- added for primitive values

    public function bind(string $abstract, callable $resolver)
    {
        $this->bindings[$abstract] = $resolver;
    }

    public function singleton(string $abstract, callable $resolver)
    {
        $this->bindings[$abstract] = function ($container) use ($resolver, $abstract) {
            if (!isset($container->instances[$abstract])) {
                $container->instances[$abstract] = $resolver($container);
            }
            return $container->instances[$abstract];
        };
    }

    public function setParameter(string $key, mixed $value): void
    {
        $this->parameters[$key] = $value;
    }

    public function getParameter(string $key): mixed
    {
        return $this->parameters[$key] ?? null;
    }

    public function make(string $class)
    {
        // If explicitly bound
        if (isset($this->bindings[$class])) {
            return $this->bindings[$class]($this);
        }

        $reflection = new ReflectionClass($class);
        if (!$reflection->isInstantiable()) {
            throw new \Exception("Class $class is not instantiable.");
        }

        $constructor = $reflection->getConstructor();
        if (!$constructor) {
            return new $class;
        }

        $params = $constructor->getParameters();
        $dependencies = [];

        foreach ($params as $param) {
            $type = $param->getType();

            if ($type && !$type->isBuiltin()) {
                $dependencies[] = $this->make($type->getName());
            } elseif (array_key_exists($param->getName(), $this->parameters)) {
                $dependencies[] = $this->parameters[$param->getName()];
            } elseif ($param->isDefaultValueAvailable()) {
                $dependencies[] = $param->getDefaultValue();
            } else {
                throw new \Exception("Cannot resolve class dependency \${$param->getName()} in {$class}");
            }
        }

        return $reflection->newInstanceArgs($dependencies);
    }
}
