<?php

namespace Discord\Bot\Components\Stat;

use Discord\Bot\Components\AbstractComponent;
use Discord\Bot\Components\Stat\Entity\StatEntity;

class StatComponent extends AbstractComponent
{
    public function __construct( $service)
    {
        parent::__construct($service);
    }

    public function getService(): ?StatEntity
    {
        return $this->service;
    }
}