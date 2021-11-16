# Fields API

## Inserting New Fields

Fields can be inserted at a specific index or before/after another field (or collection) by name.

```php
$collection->insertAtIndex( 1, new FormField( 'text', 'my-second-text-field' );
$collection->insertAfter( 'my-text-field', new FormField( 'text', 'my-second-text-field' ) );
$collection->insertBefore( 'my-text-field', new FormField( 'text', 'my-second-text-field' ) );
```

## Moving Existing Fields

Existing fields can be moved before/after another field (or collection) by name.

```php
$collection->move( 'my-second-text-field' )->after( 'my-text-field' );
$collection->move( 'my-second-text-field' )->before( 'my-text-field' );
```

## Removing Existing Fields

Existing fields (or collections) can be removed by name.

```php
$collection->remove( 'my-second-text-field' );
$collection->remove( 'my-nested-collection' );
```

## Walking Fields in a Collection

A field collection can be walked with a provided callback for each field in the collection.

```php
$collection->walk( function( Node $field ) {
    if( 'my-text-field' === $field->getName() ) {
        // ...
    }
});
```
