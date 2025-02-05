<?php

namespace Vanacode\Resource;

use Illuminate\Support\Arr;

class RequestHelper
{
    public static function queryAlias(?string $param = null, array|string|null $default = null): array|string
    {
        return config('vn_resource.query_alias'.($param ? '.'.$param : ''), $default ?? $param);
    }

    public static function queryValue(string $param, array|string|null $default = null): array|string|null
    {
        return request()->query(self::queryAlias($param), $default); // support also dot notation
    }

    public static function addRequestAttribute(string|array $attributes, mixed $value = null): void
    {
        if (!is_array($attributes)) {
            $attributes = [
                $attributes => $value
            ];
        }
        request()->attributes->add($attributes);
    }

    public static function getRequestAttribute(string $key = '', array|string|null $default = null)
    {
        $params = request()->attributes->all();
        if (empty($key)) {
            return $params;
        }

        if (stristr($key, '.')) {
            return Arr::get($params, $key, $default);
        }

        return $params[$key] ?? $default;
    }
}
