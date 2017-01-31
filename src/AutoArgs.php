<?php

namespace Krak\AutoArgs;

use Krak\Mw;

class AutoArgs
{
    private $resolve_arg;

    public function __construct(callable $resolve_arg = null) {
        $this->resolve_arg = $resolve_arg ?: self::createStack()->compose(function() {
            return [];
        });
    }

    public function invoke(callable $callable, array $context) {
        $args = $this->resolveArguments($callable, $context);
        return call_user_func_array($callable, $args);
    }

    /** resolves the arguments for the callable and returns the array of args */
    public function resolveArguments($callable, array $context) {
        $resolve_arg = $this->resolve_arg;

        $rf = $this->callableToReflectionFunctionAbstract($callable);

        $args = [];
        foreach ($rf->getParameters() as $i => $arg_meta) {
            $arg = $resolve_arg($arg_meta, $context);
            if (!count($arg) && $arg_meta->isOptional()) {
                continue;
            } else if (!count($arg)) {
                throw new \RuntimeException(sprintf('Action argument %d is unable to be resolved.', $i));
            }

            $args[] = $arg[0];
        }

        return $args;
    }

    public function callableToReflectionFunctionAbstract($callable) {
        if (is_array($callable)) {
            return new \ReflectionMethod($callable[0], $callable[1]);
        } else if (is_object($callable) && method_exists($callable, '__invoke')) {
            return new \ReflectionMethod($callable, '__invoke');
        } else if (is_string($callable) && strpos($callable, '::') !== false) {
            list($class, $method) = explode('::', $callable);
            return new \ReflectionMethod($class, $method);
        } else {
            return new \ReflectionFunction($callable);
        }
    }

    public static function createStack() {
        return mw\stack('Resolve Argument')
            ->push(defaultValueResolveArgument(), -1, 'defaultValue')
            ->push(varNameResolveArgument(), 0, 'varName')
            ->push(subclassOfResolveArgument(), 0, 'subclassOf')
            ->push(containerResolveArgument(), 0, 'container');
    }
}
