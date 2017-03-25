<?php

namespace Krak\AutoArgs;

use Krak\Mw;

class AutoArgs
{
    private $resolve_arg;

    public function __construct(callable $resolve_arg = null) {
        $this->resolve_arg = $resolve_arg ?: Mw\compose([
            function() { return []; },
            self::createStack()
        ]);
    }

    public function invoke(callable $callable, array $context) {
        $args = $this->resolveArguments($callable, $context);
        return call_user_func_array($callable, $args);
    }

    public function construct($class, array $context) {
        $rc = new \ReflectionClass($class);
        $constructor = $rc->getConstructor();
        if (!$constructor) {
            return $rc->newInstance();
        }
        $args = $this->resolveArguments($constructor, $context);
        return $rc->newInstance(...$args);
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
                throw new Exception\ResolutionException($arg_meta);
            }

            $args[] = $arg[0];
        }

        return $args;
    }

    public function callableToReflectionFunctionAbstract($callable) {
        if ($callable instanceof \ReflectionFunctionAbstract) {
            return $callable;
        } else if (is_array($callable)) {
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
        return mw\stack()
            ->push(defaultValueResolveArgument(), -1, 'defaultValue')
            ->push(varNameResolveArgument(), 0, 'varName')
            ->push(containerResolveArgument(), 0, 'container')
            ->push(subclassOfResolveArgument(), 0, 'subclassOf');
    }
}
