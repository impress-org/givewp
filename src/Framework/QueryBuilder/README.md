# Query Builder

Query Builder helper class is used to help write SQL queries

```php
$builder = new \Give\Framework\QueryBuilder\QueryBuilder();

$builder
    ->select(
        ['posts.ID', 'id'],
        ['posts.post_date', 'createdAt'],
        ['posts.post_modified', 'updatedAt'],
        ['posts.post_status', 'status'],
        ['posts.post_parent', 'parentId']
    )
    ->attachMeta($this->donationMetaTable, 'posts.ID', 'donation_id',
        ['_give_payment_total', 'amount'],
        ['_give_payment_currency', 'paymentCurrency'],
        ['_give_payment_gateway', 'paymentGateway'],
        ['_give_payment_donor_id', 'donorId'],
        ['_give_donor_billing_first_name', 'firstName'],
        ['_give_donor_billing_last_name', 'lastName'],
        ['_give_payment_donor_email', 'donorEmail']
    )
    ->from($this->postsTable, 'posts')
    ->leftJoin($this->donationMetaTable, 'posts.ID', 'donation_id', 'donationMeta')
    ->where('posts.post_type', 'give_payment')
    ->where('posts.post_status', 'give_subscription')
    ->where('donationMeta.meta_key', 'subscription_id')
    ->where('donationMeta.meta_value', $subscriptionId)
    ->orderBy('posts.post_date', 'DESC');

return DB::get_row($builder->getSQL());
```

### Nested WHERE statements

```php
$builder = new \Give\Framework\QueryBuilder\QueryBuilder();

$builder
    ->select( '*' )
    ->from( $this->postsTable )
    ->where( 'post_status', 'subscription')
    ->where( function(QueryBuilder $builder){
        $builder
            ->where( 'post_status', 'give_subscription')
            ->orWhere( 'post_status', 'give_donation');
    });

return DB::get_results($builder->getSQL());
```

Generated SQL:

```sql
SELECT *
FROM wp_posts
WHERE post_status = 'subscription'
AND (post_status = 'give_subscription' OR post_status = 'give_donation')
```

### Sub-select within the query

```php
$builder = new \Give\Framework\QueryBuilder\QueryBuilder();

$builder
    ->select('*')
    ->from($this->postsTable)
    ->where('post_status', 'subscription')
    ->whereIn('ID', function (QueryBuilder $builder) {
        $builder
            ->select(['meta_value', 'donation_id'])
            ->from($this->donationMetaTable)
            ->where('meta_key', 'donation_id');
    });

return DB::get_results($builder->getSQL());
```

Generated SQL:

```sql
SELECT *
FROM wp_posts
WHERE post_status = 'subscription'
AND ID IN (SELECT meta_value AS donation_id FROM wp_give_donationmeta WHERE meta_key = 'donation_id')
```
