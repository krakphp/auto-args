<?php

use Krak\AutoArgs,
    Krak\Cargo;

class Invokeable {
    public function __invoke() {}
}

class StaticMethodClass {
    public static function staticMethod() {}
}

class StaticClass {
    public $a;
    public $b;
    public function __construct($a, $b) {
        $this->a = $a;
        $this->b = $b;
    }
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
                    'container' => Cargo\container([
                        AutoArgs\AutoArgs::class => function() {
                            return $this->aa;
                        }
                    ])->toInterop(),
                ];

                $func = function($a, SplDoublyLinkedList $stack, Psr\Container\ContainerInterface $container, AutoArgs\AutoArgs $aa, $b = 1) {
                    assert($a == 1 && $b === 1);
                };

                $this->aa->invoke($func, $context);
            });
        });
        describe('->construct', function() {
            it('constructs a class instance', function() {
                $instance = $this->aa->construct(StaticClass::class, [
                    'vars' => ['a' => 1, 'b' => 2]
                ]);

                assert($instance instanceof StaticClass && $instance->b == 2);
            });
            it('constructs a class without constructor', function() {
                $instance = $this->aa->construct(SplStack::class, []);
                assert($instance instanceof SplStack);
            });
        });
    });
});
