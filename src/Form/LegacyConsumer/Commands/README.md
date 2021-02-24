# Making sense of the nested callbacks

Why is this so complicated?

For the purpose of backwards compatibility, we need to setup a Field Collection for each of the deprecated template hooks. This allows developers to use the Fields API to manage fields within the context of a template location. Additionally, a form ID is needed for conditionally adding fields to a specific form.

Note: It is important to note that we are re-using the template hooks list outside of the context of the form for the purpose of persisting and displaying stored data, which do not have context of the form template rendering - so we are elevating the new hooks to a higher context so that they can be use for display, persistance, and recall without repeating field registration in each of these separate contexts.

Because the field collection needs context of both the template location and the relevent donation form ID, which are available at different points of time, we are using a series of closures to "wire-up" the field collections with the necessary context as it becomes available.

Additionally, closures are used to avoid convoluted, nested anonymous functions with indentation easily reaching 6 or more levels deep.

## Processing Flow

The general flow is that the Service provider walks the template hooks list and executes a command on each of the hooks. The command then queues up the specific action hook to intialize the field collection at the approproiate time. The action hook callback then walks the fields in the new collection executing a closure to handle each field.

### Service Provider

The service provider executes a command for each template hook.

```php
give( TemplateHooks::class )
    ->walk( give( Commands\Setup::class ) );
```

### Command

The command then queues an action hook to run at a later time.

```php
add_action(
    'give_view_donation_details_billing_after',
    $this->process->with([
        'hook' => $hook,
    ])
);
```

### Action Hook Callback

The action hook callback then executes another callback for each field in the collection.

```php
$fieldCollection = new FieldCollection( 'root' );
do_action( "give_fields_$this->hook", $fieldCollection, $formID );

$fieldCollection->walk(
    $this->output->with([ 'donationID' => $donationID ])
);
```

## Helpers

## The Closure Proxy

The closure proxy is used to bind context to a callback.

A callback that is proxied can pass with itself additional attributes. This is similar to the `use` method in an anonymous function - providing context to the closure.

Additionally, any public properties of the proxied class will be passed as attributes which can be passed further down the line.

The `with()` method provides a fluent API for passing the additional context.

```php

$callback = new ClosureProxy( $callback, $classInstance );
add_action( 'action_hook',
    $callback->with([ 'foo' => 'bar'])
);
```

## The Attribute Bag

The attribute bag is used by the closure proxy to store the attributes passed as context.