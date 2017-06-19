<?php

namespace Greg\Framework;

use Greg\Support\Str;

trait BootingTrait
{
    private $booted = [];

    public function booted(): array
    {
        return $this->booted;
    }

    protected function bootstrap()
    {
        // Call all methods which starts with "boot"
        foreach (get_class_methods($this) as $method) {
            if ($method === 'boot') {
                continue;
            }

            if (Str::startsWith($method, 'boot') and mb_strtoupper($method[4]) === $method[4]) {
                $dependency = lcfirst(mb_substr($method, 4));

                if (in_array($dependency, $this->booted)) {
                    continue;
                }

                $this->booting($method);

                $this->booted[] = $dependency;
            }
        }

        return $this;
    }

    public function dependsOn(string ...$dependencies)
    {
        foreach ($dependencies as $dependency) {
            if (in_array($dependency, $this->booted)) {
                continue;
            }

            if (!method_exists($this, $method = 'boot' . ucfirst($dependency))) {
                throw new \Exception('Bootable dependency `' . $dependency . '` does not exists.');
            }

            $this->booting($method);

            $this->booted[] = $dependency;
        }

        return $this;
    }

    abstract protected function booting(string $method);
}
