# Query Builder

A purpose built Query Builder class for selecting peer-to-peer donation data.

_Note: This is not a generalized query builder._

```php
$builder = new \GiveP2P\P2P\QueryBuilder\QueryBuilder();

$builder
    ->from( 'local_give_p2p_donation_source' )
    ->join( 'local_posts', 'donation_id', 'ID' )
    ->where( 'local_posts.ID', '=', '171' )
;
```

## Table Aliases

Table names can be aliased using the `tables()` method, passing key value 
pairs of the alias followed by the fully qualified table name.

```php
$builder = new \GiveP2P\P2P\QueryBuilder\QueryBuilder();

$builder->tables([
    'donations' => 'local_posts',
    'donation_source' => 'local_give_p2p_donation_source'
]);

$builder
    ->from( 'donation_source' )
    ->join( 'donations', 'donation_id', 'ID' )
;
```
