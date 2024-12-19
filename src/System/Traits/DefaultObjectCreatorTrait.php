<?php

namespace Discord\Bot\System\Traits;

trait DefaultObjectCreatorTrait
{
    public static function create(array $data = []): static
    {
        $obj = new static();

        foreach ($data as $property => $value) {
            $method = 'set' . ucfirst($property);
            if (method_exists($obj, $method)) {
                $obj->{$method}($value);
            } elseif (property_exists($obj, $property)) {
                $obj->{$property} = $value;
            }
        }

        return $obj;
    }
}
