RouteParamsAsControllerArgs
===========================

Allows route parameters to be passed directly to controller action methods as arguments.

Example
-------

Configure a route normally.

```php
'user' => array(
    'type'    => 'literal',
    'options' => array(
        'route'    => '/user',
        'defaults' => array(
            '__NAMESPACE__' => 'Application\Controller',
            'controller'    => 'User',
        ),
    ),
    'may_terminate' => false,
    'child_routes'  => array(
        'hello' => array(
            'type'    => 'segment',
            'options' => array(
                'route'    => '/hello/:firstName[/:lastName]',
                'defaults' => array(
                    'action' => 'hello',
                    // Assign any parameter defaults as normal
                ),
            ),
        ),
    ),
),
```

Add desired route parameters to the controller action method.  Arguments are matched on parameter name and so can be declared in any order.

```php
public function helloAction($firstName, $lastName = null)
{
    // ...
}
```
