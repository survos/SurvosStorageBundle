<?php

namespace Survos\StorageBundle\Model;

class Adapter
{

    /**
     * @param string $name
     * @param string $class
     * @param string|null $rootLocation
     * @param string|null $bucket
     */
    public function __construct(
        private string  $name,
        private string  $class,
        private ?string $rootLocation = null,
        private ?string $bucket = null
    )
    {
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getClass(): string
    {
        return $this->class;
    }

    public function getRootLocation(): ?string
    {
        return $this->rootLocation;
    }

    public function getBucket(): ?string
    {
        return $this->bucket;
    }

}
