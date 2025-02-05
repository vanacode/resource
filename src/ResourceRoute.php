<?php

namespace Vanacode\Resource;

use Illuminate\Routing\Route as RouteInstance;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

class ResourceRoute
{
    private static array $cached = [];

    private static array $cachedCoreRouteParams = [];

    private static array $cachedRouteInstances = [];

    public static function routeEndUrl(string $action, ?string $from = null): string
    {
        $route = self::routeEnd($action, $from);

        return route($route, Route::current()->parameters());
    }

    public static function routeEnd(string $action, ?string $from = null): string
    {
        $from = $from ?? Str::afterLast(Route::currentRouteName(), '.');

        return Str::replaceLast($from, $action, Route::currentRouteName());
    }

    public static function prefixedRouteUrl(string $action, ...$args): string
    {
        $routeName = self::prefixedRoute($action);
        return $routeName ? self::routeUrl($routeName, ...$args) : '';
    }

    public static function prefixedRoute(string $action): ?string
    {
        if (array_key_exists($action, self::$cached)) {
            return self::$cached[$action];
        }

        foreach (config('vn_resource.route.prefix_names') as $name) {
            if (Str::startsWith(Route::currentRouteName(), $name)) {
                $routeName = Str::finish($name, '.').$action;
                $routeInstance = self::getRouteInstance($routeName);
                if ($routeInstance) {
                    return self::$cached[$action] = $routeName;
                }
            }
        }

        return self::$cached[$action] = null;
    }

    public static function resourceUrl(string $resource, string $action, ...$args): string
    {
        $route = ResourceRoute::resourceRoute($resource, $action);

        return $route ? self::routeUrl($route, ...$args) : '';
    }

    public static function resourceRoute(string $resource, string $action): ?string
    {
        $action = Str::finish($resource, '.')  . $action;

        return self::prefixedRoute($action);
    }

    public static function routeUrl(string $route, ...$params): string
    {
        $params = self::mergeParameters($route, ...$params);

        return route($route, $params);
    }

    public static function matchParameters(string $route): array
    {
        if (key_exists($route, self::$cachedCoreRouteParams)) {
            return self::$cachedCoreRouteParams[$route];
        }
        $routeInstance = self::getRouteInstance($route);
        if (empty($routeInstance)) {
            return self::$cachedCoreRouteParams[$route] = [];
        }

        $current = Route::current()->parameters();
        $parameterNames = $routeInstance->parameterNames();
        foreach (array_keys($current) as $parameter) {
            if (! in_array($parameter, $parameterNames)) {
                unset($current[$parameter]);
            }
        }

        return self::$cachedCoreRouteParams[$route] = $current;
    }

    protected static function mergeParameters(string $route, ...$params): array
    {
        $current = self::matchParameters($route);
        if (empty($params)) {
            return $current;
        }
        $routeInstance = Route::getRoutes()->getByName($route);
        $parameterNames = $routeInstance->parameterNames();

        $params = is_array($params[0]) ? $params[0] : $params;
        $numericParams = [];
        foreach ($params as $key => $value) {
            if (is_numeric($key)) {
                $numericParams[] = $value;
                unset($params[$key]);
            } elseif (in_array($key, $parameterNames)) {
                unset($current[$key]);
                $parameterNames = array_diff($parameterNames, [$key]);
            }
        }

        if (count($numericParams) > count($parameterNames)) {
            $current = [];
        } else {
            $diff = count($parameterNames) - count($numericParams);
            $current = array_slice($current, 0, $diff);
        }

        return array_merge($current, $numericParams, $params);
    }

    protected static function getRouteInstance(? string $route): ?RouteInstance
    {
        if (empty($route)) {
            return null;
        }
        if (! isset(self::$cachedRouteInstances[$route])) {
            self::$cachedRouteInstances[$route] = Route::getRoutes()->getByName($route);
        }

        return self::$cachedRouteInstances[$route];
    }
}
