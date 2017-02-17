# Auto Args

Auto Args provides a system for automatically resolving arguments for any callable. This can also be referred to as Auto wiring.

## Installation

Install with composer at `krak/auto-args`.

## Usage

```php
<?php

use Krak\AutoArgs;

$args = new AutoArgs();

$context = [
    'vars' => ['a' => 1],
    'objects' => [new SplStack()],
];

$func = function($a, SplDoublyLinkedList $stack, $b = 1) {
    assert($a == 1 && $b === 1);
};

$args->invoke($func, $context);
```

### Container Integration

```php
<?php

use Krak\AutoArgs,
    Krak\Cargo,
    Interop\Container\ContainerInterface;

$args = new AutoArgs();
$c = Cargo\container();
$c[SplStack::class] = function() {
    return new SplStack();
};

$context = [
    'container' => $c->toInterop()
];

$func = function(ContainerInterface $container, SplStack $stack) {

};

$args->invoke($func, $context);
```

## API

### Class AutoArgs

#### \_\_construct($resolve_arg = null)

Accepts an argument resolver which will accept Argument metadata and context and return the proper argument for it. If none is supplied, the default stack is created and composed instead.

#### mixed invoke(callable $callable, array $context)

Invokes a callable and resolves the arguments from the argument resolver and given context.

#### mixed construct($class_name, array $context)

Constructs a an object from the class name and resolves the arguments for the constructor

#### array resolveArguments(callable $callable, array $context)

Returns the array of resolved arguments for the given callable. An exception will be thrown if no argument was able to be resolved.

#### Krak\\Mw\\MwStack ::createStack()

Returns a configured instance of an mw stack.
