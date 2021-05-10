# Fast, powerful, scalable & customizable php template engine
Syntax close to php for easy learning and management.

[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/teodoroleckie/template/badges/quality-score.png?b=main)](https://scrutinizer-ci.com/g/teodoroleckie/template/?branch=main)
[![Code Intelligence Status](https://scrutinizer-ci.com/g/teodoroleckie/template/badges/code-intelligence.svg?b=main)](https://scrutinizer-ci.com/code-intelligence)
[![Build Status](https://scrutinizer-ci.com/g/teodoroleckie/template/badges/build.png?b=main)](https://scrutinizer-ci.com/g/teodoroleckie/template/build-status/main)

## Installation

You can install the package via composer:

```bash
composer require tleckie/template
```

### Printing variable:
```php
{{$name}}
```
Flexibility of spaces in variables:
```php
<p>{{$name}}</p>

<p>{{   $user->getName()   }}</p>

<p>
    {{
    $user->getName()
    }}
</p>
```

### Printing constant:
```php
{{CONSTANT}}
```

### Set variables:
```php
{set $variable = '355'}
```
### Dump:
```php
{dump $users}
```
### Comments:
```txt
{# {dump $users} #}
```


### Extends template:
```txt
<html>
<head></head>
<body>

    {extends Common/Header.html}
    
    <p>List</p>
        
    {foreach $users as $user}
        <p>{{$user->getName()}}</p>
    {endforeach}

    {extends Common/Footer.html}

</body>
</html>
```
If you add changes to the included templates as extensions, the compiler will not show these changes.
To remove the compiled files you have the "flushCompiled()" method

```php
$tpl = new Template(__DIR__.'/tpl/', '/var/www/cache/compiled/');
$tpl->flushCompiled();
```

You can enable development mode so that templates are always compiled.
```php
use Tleckie\Template\Template;

$tpl = new Template(
    __DIR__.'/tpl/', 
    '/var/www/cache/compiled/', 
    null, 
    true // development mode
);
```

### Conditionals if, else & elseif:
```txt
{if $title === 'title'}
    <div>{{$title}}</div>
{else}
    It is not a headline!
{endif}
```
```txt
{if $type === 'Orange'}
    <div>{{$type}}</div>
{elseif $title !== 'Apple'}
    It is not an Apple!
{else}
    Other fruit!
{endif}
```

```txt
{if $age >= 18}
    <div>Yes!</div>
{else}
    Ups :)
{endif}
```
```txt
{if $age !== 18}
    <div>Yes!</div>
{else}
    Ups :)
{endif}
```

### Nested loops:
Foreach and for supported.
```txt
<html>
<head></head>
<body>

    {foreach $data as $key => $persons}
        {foreach $persons as $id => $name}
            <div><string>{{$name}}:</string>{{ $key }}</div>
        {endforeach}
    {endforeach}
    
</body>
</html>
```

### Helpers:
Create your own helpers to invoke on the template.
```php
{{$this->arrayHelper::last($persons)}}
```

### Create the template instance:
```php
<?php

require_once "vendor/autoload.php";

use Tleckie\Template\Template;

$tpl = new Template(__DIR__ . '/tpl/', '/var/www/cache/compiled/');

$data = [
    'names' => ['Marcos', 'John', 'Pedro', 'Marta'],
    'title' => 'User list',
    'users' => [
        new User('Marcos'), new User('John'), new User('Pedro')
    ]
];

$tpl->render('List/Users.html', $data);
```

Template List/Users.html
```txt
<html>
<head></head>
<body>
    {foreach $users as $user}
        <p>{{$user->getName()}}</p>
    {endforeach}
</body>
</html>
```
### Rules:
The Tleckie\Template\Compiler\Parser\Rules class establishes the rules for handling templates.
You can create new rules to enrich your templating engine through the RuleInterface interface.

```php
use Tleckie\Template\Template;
use Tleckie\Template\Compiler\Compiler;
use Tleckie\Template\Compiler\Parser\Rules;

$rules = [
    new Rules(),
    new MyOwnRules()
];

$compiler = new Compiler($rules);

$tpl = new Template(
    __DIR__.'/tpl/', 
    '/var/www/cache/compiled/',
     $compiler
);
```

### Helpers:
You can create your own helpers to be invoked from templates to do complex tasks, manipulate objects or strings.

The Template class has a method for adding helpers. Note that by this mechanism you can also add your dependency injector.


```php
<?php

require_once "vendor/autoload.php";

use Tleckie\Template\Template;

$tpl = new Template(
    __DIR__ . '/tpl/', 
    '/var/www/cache/compiled/'
);

$tpl->registerHelper(
    'arrayHelper', 
    new \MyNamespace\Infrastructure\Helpers\ArrayHelper()
);
```

Each helper added to the template object must have an alias to be invoked through it.

```php
{{$this->arrayHelper::last($persons)}}
```

Helper example:

```php
<?php

namespace Tleckie\Template;

use function end;

/**
 * Class ArrayHelper
 * @package MyNamespace\Infrastructure\Helpers
 * @author Teodoro Leckie Westberg <teodoroleckie@gmail.com>
 */
class ArrayHelper
{
    public static function last(array $array)
    {
        return end($array);
    }
}
```

That's all! I hope this helps you ;)