# Laravel Gantt Chart


## Installation

Require this package with composer:

```shell
composer require michelmelo/laravel-gantt
```

After updating composer, add the ServiceProvider to the providers array in config/app.php

```php
MichelMelo\LaravelGantt\GanttServiceProvider::class,
```

Copy the package css file to your local css with the publish command:

```shell
php artisan vendor:publish --tag="gantt"
```

## Usage

The model to display in the Gantt Chart will need to have properties of `label`, `start` and `end` at minimum.

* `label` is the string to display for the item
* `start` is a date or datetime (will need to pass this as a YYYY-MM-DD format)
* `end` is a date or datetime (will need to pass this as a YYYY-MM-DD format)

```php

/**
 *  You'll pass data as an array in this format:
    $test_array = [
                      [
                        'label' => 'The item title',
                          'date' => [
                             [
                                 'start' => '2016-10-08',
                                 'end'   => '2016-10-14',
                                 'class' => '',
                             ],
                             [
                                 'start' => '2016-10-16',
                                 'end'   => '2016-10-19',
                                 'class' => '',
                             ]
                         ]
 
                     ]
                 ];
 */
 
$gantt = new MichelMelo\LaravelGantt\Gantt($test_array, array(
    'title'      => 'Demo',
    'cellwidth'  => 25,
    'cellheight' => 35
));

return view('gantt')->with([ 'gantt' => $gantt ]);
```

### Display in your view

In your view, add the `gantt.css` file:

```html
<link href="/vendor/michelmelo/gantt/css/gantt.css" rel="stylesheet" type="text/css">
```

And then output the gantt HTML:

```html
{!! $gantt !!}
```


