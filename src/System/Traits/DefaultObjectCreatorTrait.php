<?php

namespace Discord\Bot\System\Traits;

trait DefaultObjectCreatorTrait
{
    protected array $data = [];

    public static function create(array $data = []): static
    {
        $obj = new static();

        foreach ($data as $property => $value) {
            $method = 'set' . ucfirst($property);
            if (method_exists($obj, $method)) {
                $obj->{$method}($value);
            } elseif (property_exists($obj, $property)) {
                $obj->{$property} = $value;
            } else {
                $obj->addToData($property, $value);
            }
        }

        return $obj;
    }

    public function addToData(string $propertyName, mixed $value): static
    {
        $this->data[$propertyName] = $value;

        return $this;
    }
}
