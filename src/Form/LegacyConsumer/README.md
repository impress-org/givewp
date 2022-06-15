# Form Consumer

A consumer of the Fields API for GiveWP Donation Forms.

## Public Fields API

The public Fields API combines the Field Factory and Field Collection methods into a single fluent API.

Note: Currently supports text, textarea, select, and checkbox field types.

```php
add_action( 'give_fields_payment_mode_before_gateways', function( $collection ) {

    // Append a required text field with the name myTextField
    $collection->append(
        give_field( 'text', 'myTextField' )
            ->label( 'My Text Field' )
            ->required() // Could instead be marked as readOnly() (optional)
            ->helpText( __( 'This is my custom text field.' ) )
    );
});
```

```php
add_action( 'give_fields_payment_mode_before_gateways', function( $collection ) {

    // Show in the Donation Receipt (for the legacy template uses the donation confirmation).
    $collection->append(
        give_field( 'text', 'myTextField' )
            ->label( 'My Text Field' )
            ->showInReceipt()
    );
});
```

```php
add_action( 'give_fields_payment_mode_before_gateways', function( $collection ) {

    // Store a field value as Donor Meta (instead of Donation Meta).
    $collection->append(
        give_field( 'text', 'myTextField' )
            ->label( 'My Text Field' )
            ->storeAsDonorMeta() // Otherwise store as Donation Meta (default)
            ->showInReceipt() // When stored as Donor meta it uses the Donor section of the receipt.
    );
});
```

```php
add_action( 'give_fields_payment_mode_before_gateways', function( $collection ) {

    // Add support for an email tag.
    $collection->append(
        give_field( 'text', 'myTextField' )
            ->label( 'My Text Field' )
            ->emailtag( 'my-text-field' )
    );
});
```

```php
add_action( 'give_fields_payment_mode_before_gateways', function( $collection ) {
    $collection->append(

        // Select field with options.
        give_field( 'select', 'mySelectField' )
            ->label( 'My Select Field' )
            ->options([
                'aye' => __( 'Aye' ),
                'bee' => __( 'Bee' ),
            ])

    );
});
```



## Integrating with the Form

The new public Fields API promotes field registration above the form template. Instead of requiring different hooks to render, validate, and persist field data, a single hook unifies field registration that can then be used to render, validate, and persist field data programmatically.

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
