<?php

namespace App\Models;

abstract class BaseModel
{
    /**
     * Convert model to array
     */
    public function toArray(): array
{
    $reflection = new \ReflectionObject($this);

    $data = [];

    foreach ($reflection->getProperties() as $property) {

        $property->setAccessible(true);

        $data[$property->getName()] = $property->getValue($this);

    }

    return $data;
}

    /**
     * Hydrate model from database row
     */
    public function fill(array $data): static
    {
        $reflection = new \ReflectionObject($this);

        foreach ($data as $key => $value) {
            $property = lcfirst(str_replace(' ', '', ucwords(str_replace('_', ' ', $key))));

            if ($reflection->hasProperty($property)) {
                $prop = $reflection->getProperty($property);
                $prop->setAccessible(true);
                $prop->setValue($this, $value);
            }
        }

        return $this;
    }
}