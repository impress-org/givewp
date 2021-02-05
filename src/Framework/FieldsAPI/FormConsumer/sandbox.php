<?php

add_action( 'give_fields_payment_mode_before_gateways', function( $collection ) {

    $collection->append(
        give_field( 'text', 'myTextField' )
            ->label( 'My Text Field' )
            ->required()
    );

    $collection->append(
        give_field( 'select', 'mySelectField' )
            ->label( 'Select Field' )
            ->addOptions([
                'foo' => 'Foo',
                'bar' => 'Bar',
            ])
            ->addOption( 'baz', 'Baz' )
            ->required()
    );

    $collection->insertAfter( 'myTextField',
        give_field( 'textarea', 'myTextareaField' )
            ->label( 'Textarea Field' )
            ->required()
    );

    $collection->append(
        give_field( 'checkbox', 'myCheckboxField' )
            ->label( 'My Checkbox' )
    );
});
