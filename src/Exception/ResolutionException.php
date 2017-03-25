<?php

namespace Krak\AutoArgs\Exception;

use ReflectionParameter;
use ReflectionMethod;
use ReflectionClass;

class ResolutionException extends \RuntimeException
{
    public function __construct(ReflectionParameter $parameter) {
        $name = $parameter->getName();
        $pos = $parameter->getPosition();
        $type = "";
        if ($parameter->isArray()) {
            $type = 'array ';
        } else if ($parameter->isCallable()) {
            $type = 'callable ';
        } else if ($parameter->getClass()) {
            $type = $parameter->getClass()->getName() . ' ';
        }

        $func = $parameter->getDeclaringFunction();
        if ($func instanceof ReflectionMethod) {
            if ($func->isConstructor()) {
                $func_name = 'class ' . $func->getDeclaringClass()->getName();
            } else  {
                $func_name = 'method ' . $func->getDeclaringClass()->getName() . '::' . $func->getName();
            }
        } else {
            $func_name = 'function ' . $func->getName();
        }

        $message = sprintf("Unable to resolve argument #%d (%s\$%s) for %s", $pos, $type, $name, $func_name);
        parent::__construct($message);
    }
}
