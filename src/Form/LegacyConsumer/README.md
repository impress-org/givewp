# Form Consumer

A consumer of the Fields API for GiveWP Donation Forms.

## Public Fields API

The public Fields API combines the Field Factory and Field Collection methods into a single fluent API.

```php
add_action( 'give_fields_payment_mode_before_gateways', function( $collection ) {

    // Append a required text field with the name myTextField
    $collection->append(
        give_field( 'text', 'myTextField' )
            ->label( 'My Text Field' )
            ->required()
    );
});
```

## Integrating with the Form

The new public Fields API promotes field registration above the form template. Instead of requiring different hooks to render, validate, and persist field data, a single hook unifies field registration that can then be used to render, validate, and persist field data programatically.

```php
// Setup a field register with an empty field collection.
$fieldCollection = new FieldCollection( 'root' );

// New hook to collect field data.
// Uses the same hook for rendering, validating, and persisting field data.
do_action( 'give_fields_payment_mode_before_gateways', $fieldCollection );

// Old hook to render fields at a specific location.
add_action( 'give_payment_mode_before_gateways', function( $formID ) use ( $fieldCollection ) {
    foreach( $fieldCollection->getFields() as $field ) {
        // Render field HTML...
    }
});
```