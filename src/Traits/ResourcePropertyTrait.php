<?php

namespace Vanacode\Resource\Traits;

use Illuminate\Support\Str;

/**
 * use with Vanacode\Support\Traits\DynamicClassTrait
 */
trait ResourcePropertyTrait
{
    /**
     * Class basename without suffix with slug case
     * can use for dynamically make routes, resource path, translations
     */
    protected string $resource;

    protected string $snakeResource;

    /**
     * Class relative name to class root without suffix with slug case
     * can use for dynamically make routes, resource path
     */
    protected string $fullResource;

    protected bool $resourceIsPlural = true;

    /**
     * make resource and full resource
     */
    protected function makeResource(): self
    {
        $subFolders = $this->getClassSubFolders();
        $subFolders[] = $this->getClassNameWithoutSuffix();
        foreach ($subFolders as $key => $folder) {
            $subFolders[$key] = Str::kebab($folder);
        }
        $fullResource = implode('.', $subFolders);
        $fullResource = $this->resourceIsPlural ? Str::plural($fullResource) : $fullResource;

        return $this->setFullResource($fullResource);
    }

    /**
     * Set Resource
     */
    public function setResource(string $resource): self
    {
        $this->resource = $resource;

        return $this;
    }

    /**
     * Get Resource
     */
    public function getResource(): string
    {
        return $this->resource;
    }

    /**
     * Get Snake Resource
     */
    public function getSnakeResource(): string
    {
        if (! isset($this->snakeResource)) {
            $this->snakeResource = str_replace('-', '_', $this->resource);
        }

        return $this->snakeResource;
    }

    /**
     * Set full Resource
     */
    public function setFullResource(string $resource): self
    {
        $this->fullResource = $resource;
        $this->setResource(Str::afterLast($resource, '.'));

        return $this;
    }

    /**
     * Get full Resource
     */
    public function getFullResource(): string
    {
        return $this->fullResource;
    }

    /**
     * process resource path
     * default process with slug case
     */
    protected function processResourcePart(string $folder)
    {
        return Str::slug($folder);
    }
}
