<?php
declare(strict_types=1);

namespace Core;

class ServiceContainer {
    private $services = [];

    public function registerServices(array $definitions): void {
        foreach ($definitions as $id => $factory) {
            $this->services[$id] = $factory($this);
        }
    }

    public function get(string $id) {
        return $this->services[$id] ?? null;
    }
}