<?php

namespace Krak\AutoArgs;

use Krak\Mw,
    ReflectionParameter;

/** array resolveArgument(ReflectionParameter, array);
    array resolveArgumentMiddleware(ReflectParameter, array, callable $next)
*/

function _isSubclassOf($parent, $child) {
    return $parent == $child || is_subclass_of($child, $parent);
}

function hasKeyResolveArgument($key, $mw) {
    return mw\filter($mw, function(ReflectionParameter $arg_meta, array $context) use ($key) {
        return array_key_exists($key, $context);
    });
}

/** Resolves an argument if the context contains a key that matches the var name */
function varNameResolveArgument($key = 'vars') {
    return hasKeyResolveArgument($key, function(ReflectionParameter $arg_meta, array $context, $next) use ($key) {
        $vars = $context[$key];

        if (array_key_exists($arg_meta->getName(), $vars)) {
            return [$vars[$arg_meta->getName()]];
        }

        return $next($arg_meta, $context);
    });
}

/** Resolves the argument if is a subclass of */
function subclassOfResolveArgument($key = 'objects') {
    return hasKeyResolveArgument($key, function(ReflectionParameter $arg_meta, array $context, $next) use ($key) {
        if (!$arg_meta->getClass()) {
            return $next($arg_meta, $context);
        }

        $class = $arg_meta->getClass();
        $objects = $context[$key];

        foreach ($objects as $obj) {
            if (_isSubclassOf($arg_meta->getClass()->getName(), get_class($obj))) {
                return [$obj];
            }
        }

        return $next($arg_meta, $context);
    });
}

/** Resolves the argument as the given default value if supplied */
function defaultValueResolveArgument() {
    return function(ReflectionParameter $arg_meta, array $context, $next) {
        if (!$arg_meta->isOptional()) {
            return $next($arg_meta, $context);
        }
        if (!$arg_meta->isDefaultValueAvailable()) {
            return [];
        }

        return [$arg_meta->getDefaultValue()];
    };
}

function containerResolveArgument($key = 'container') {
    return hasKeyResolveArgument($key, function(ReflectionParameter $arg_meta, array $context, $next) use ($key) {
        if (!$arg_meta->getClass()) {
            return $next($arg_meta, $context);
        }

        $class = $arg_meta->getClass();
        $container = $context[$key];

        if (!$container instanceof \Interop\Container\ContainerInterface) {
            throw new \LogicException('Expected Interop\Container\ContainerInterface instance in context');
        }

        if ($contianer->has($class->getName())) {
            return [$container->get($class->getName())];
        }

        if (_isSubclassOf(\Interop\Container\ContainerInterface::class, $class->getName())) {
            return [$container];
        }

        return $next($arg_meta, $context);
    });
}
