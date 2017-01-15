<?php

use Krak\AutoArgs;

class Invokeable {
    public function __invoke() {}
}

class StaticMethodClass {
    public static function staticMethod() {}
}

describe('Krak AutoArgs', function() {
    describe('AutoArgs', function() {
        beforeEach(function() {
            $this->aa = new AutoArgs\AutoArgs();
        });
        describe('->callableToReflectionFunctionAbstract()', function() {
            $params = [
                ['converts a normal string callable', 'array_push'],
                ['converts a Closure', function() {}],
                ['converts an ($object,$method) tuple', [new SplStack, 'push']],
                ['converts static method', 'StaticMethodClass::staticMethod'],
                ['converts an invokeable object', new Invokeable()],
            ];

            foreach ($params as list($msg, $callable)) {
                it($msg, function() use ($callable) {
                    $rf = $this->aa->callableToReflectionFunctionAbstract($callable);
                    assert($rf instanceof ReflectionFunctionAbstract);
                });
            }
        });
        describe('->resolveArguments()', function() {
            it('returns an array of arguments from the argument resolver', function() {
                $aa = new AutoArgs\AutoArgs(function() {
                    return [1];
                });

                $func = function($a, $b) {
                    assert($a === $b && $a === 1);
                };

                $aa->resolveArguments($func, []);
            });
            it('throws an exception if no args where resolved', function() {
                $aa = new AutoArgs\AutoArgs(function() {
                    return [];
                });

                $func = function($a, $b) {};

                try {
                    $aa->resolveArguments($func, []);
                    assert(false);
                } catch (RuntimeException $e) {
                    assert(true);
                }
            });
            it('resolves arguments with context', function() {
                $context = [
                    'vars' => ['a' => 1, 'stack' => 2],
                    'objects' => [new SplStack()],
                    'pimple' => new \Pimple\Container([
                        AutoArgs\AutoArgs::class => function() {
                            return $this->aa;
                        }
                    ])
                ];

                $func = function($a, SplDoublyLinkedList $stack, \Pimple\Container $container, AutoArgs\AutoArgs $aa, $b = 1) {
                    assert($a == 1 && $b === 1);
                };

                $this->aa->invoke($func, $context);
            });
        });
    });
});
