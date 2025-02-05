<?php

namespace Vanacode\Resource\Translator;

use Illuminate\Contracts\Translation\Loader;
use Illuminate\Support\Str;
use Illuminate\Translation\Translator as LaravelTranslator;
use Vanacode\Support\VnStr;

/**
 * TODO later make cusomizable translation for all cases.
 * first check like
 *
 * $resource.$action
 * 'resource'.$action
 */
class Translator extends LaravelTranslator
{
    protected array $defaultReplacements = [];

    protected string $resource = 'common.resources.';

    protected string $common = 'common.';

    public function __construct(Loader $loader, $locale)
    {
        parent::__construct($loader, $locale);
        // TODO later initialize by config $resourceFileName, $commonFileName
    }

    public function resourcePlural(string $resource, array $replace = [], $locale = null): string
    {
        $resource = VnStr::forceSnake($resource);

        return $this->get($this->resource.Str::plural($resource), $replace, $locale);
    }

    public function resourceSingular(string $resource, array $replace = [], $locale = null): string
    {
        $resource = VnStr::forceSnake($resource);

        return $this->get($this->resource.Str::singular($resource), $replace, $locale);
    }

    public function commonResource(string $resource, string $key, array $replace = [], $locale = null): array|string
    {
        $replace['resources'] = $replace['resources'] ?? $this->resourcePlural($resource, $replace, $locale);
        $replace['resource'] = $replace['resource'] ?? $this->resourceSingular($resource, $replace, $locale);

        return $this->common($key, $replace, $locale);
    }

    public function actionResource(string $resource, string $key, array $replace = [], $locale = null): array|string
    {
        $replace['resources'] = $replace['resources'] ?? $this->resourcePlural($resource, $replace, $locale);
        $replace['resource'] = $replace['resource'] ?? $this->resourceSingular($resource, $replace, $locale);
        $key = VnStr::forceSnake($key);
        return $this->common('action.'.$key, $replace, $locale);
    }

    public function eventResource(string $resource, string $key, array $replace = [], $locale = null): array|string
    {
        $replace['resources'] = $replace['resources'] ?? $this->resourcePlural($resource, $replace, $locale);
        $replace['resource'] = $replace['resource'] ?? $this->resourceSingular($resource, $replace, $locale);

        return $this->common('event.'.$key, $replace, $locale);
    }

    public function createResource(string $resource, array $replace = [], $locale = null): array|string
    {
        return $this->actionResource($resource, 'create', $replace, $locale);
    }

    public function editResource(string $resource, array $replace = [], $locale = null): string
    {
        return $this->actionResource($resource, 'edit', $replace, $locale);
    }

    public function updateResource(string $resource, array $replace = [], $locale = null): string
    {
        return $this->actionResource($resource, 'update', $replace, $locale);
    }

    public function common(string $key, array $replace = [], $locale = null): array|string
    {
        return $this->get($this->common.$key, $replace, $locale);
    }

    public function commonHas($key, $locale = null, $fallback = true)
    {
        return $this->has($this->common.$key, $locale, $fallback);
    }

    public function notFoundResource(string $resource, array $replace = [], $locale = null): string
    {
        return $this->commonResource($resource, 'not_found', $replace, $locale);
    }

    public function notFoundListResource(string $resource, array $replace = [], $locale = null): string
    {
        return $this->commonResource($resource, 'not_found_list', $replace, $locale);
    }

    public function attribute(string $attribute, ?string $resource = null)
    {
        if ($this->has('validation.attributes.'.$attribute)) {
            return $this->get('validation.attributes.'.$attribute);
        }
        if (Str::contains($attribute, '.')) {
            return $this->attribute(Str::after($attribute, '.'), $resource);
        }

        return $this->get('validation.attributes.'.$attribute);
    }

    public function boolean(?bool $bool): ?string
    {
        if (is_null($bool)) {
            return null;
        }

        return $bool ? $this->common('boolean.true') : $this->common('boolean.false');
    }

    /**
     * Make the place-holder replacements on a line.
     *
     * @param  string  $line
     * @return string
     */
    protected function makeReplacements($line, array $replace)
    {
        $replace = array_merge($this->getDefaultReplacements(), $replace);

        return parent::makeReplacements($line, $replace);
    }

    public function setReplacement(string $key, string $replacement): void
    {
        $this->defaultReplacements[$key] = $replacement;
    }

    public function getDefaultReplacements(): array
    {
        return $this->defaultReplacements;
    }
}
