# Query Builder

Query Builder helper class is used to write SQL queries

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
```

Generated SQL:

```sql
SELECT *
FROM wp_posts
WHERE post_status = 'subscription'
  AND ID IN (SELECT meta_value AS donation_id FROM wp_give_donationmeta WHERE meta_key = 'donation_id')
```

### Joins

For simple JOIN clauses, you can use ```leftJoin```, ```rightJoin```, and ```innerJoin``` methods.

```php
$builder = new \Give\Framework\QueryBuilder\QueryBuilder();

$builder
    ->select('*')
    ->from($this->postsTable, 'posts')
    ->leftJoin($this->donationMetaTable, 'posts.ID', 'donationMeta.donation_id', 'donationMeta')
    ->where('posts.post_type', 'give_payment')
    ->where('posts.post_status', 'give_subscription')
    ->where('donationMeta.meta_key', 'subscription_id')
    ->where('donationMeta.meta_value', $subscriptionId)
    ->orderBy('posts.post_date', 'DESC');
```

Generated SQL:

```sql
SELECT *
FROM wp_posts AS posts
         LEFT JOIN wp_give_donationmeta donationMeta ON posts.ID = donationMeta.donation_id
WHERE posts.post_type = 'give_payment'
  AND posts.post_status = 'give_subscription'
  AND donationMeta.meta_key = 'subscription_id'
  AND donationMeta.meta_value = '1'
ORDER BY posts.post_date DESC
```

For complex JOIN clauses, you can use ```join``` method and pass a Closure for ```$condition``` parameter which will then pass back a new QueryBuilder instance to the Closure.
Note: methods ```joinOn```, ```joinAnd```, and ```joinOr``` should be used only inside the Closure.

```php
$builder
    ->from('tableOne')
    ->join(
        'tableTwo',
        JoinType::LEFT,
        function (QueryBuilder $builder) {
            $builder
                ->joinOn('tableOne.foreignKey', '=', 'tableTwoAlias.primaryKey')
                ->joinAnd('tableTwoAlias.meta_key', '=', 'subscription_id', $escape = true);
        },
        'tableTwoAlias'
    );
```

Generated SQL

```sql
SELECT * FROM tableOne
    LEFT JOIN tableTwo tableTwoAlias
        ON tableOne.foreignKey = tableTwoAlias.primaryKey
       AND tableTwoAlias.meta_key = 'subscription_id'
```

### Special method for working with meta tables (attachMeta)

Under the hood ```attachMeta``` will add join clause for each defined ```meta_key``` column. And each column will be
added in select statement as well, which means the meta columns will be returned in query result. Aliasing meta columns
is recommended when using ```attachMeta``` method.

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
        ['_give_payment_donor_email', 'donorEmail'],
        ['subscription_id', 'subscriptionId']
    )
    ->from($this->postsTable, 'posts')
    ->leftJoin($this->donationMetaTable, 'posts.ID', 'donationMeta.donation_id', 'donationMeta')
    ->where('posts.post_type', 'give_payment')
    ->where('posts.post_status', 'give_subscription')
    ->where('donationMeta.meta_key', 'subscription_id')
    ->where('donationMeta.meta_value', 1)
    ->orderBy('posts.post_date', 'DESC');
```

Generated SQL:

```sql
SELECT posts.ID                                      AS id,
       posts.post_date                               AS createdAt,
       posts.post_modified                           AS updatedAt,
       posts.post_status                             AS status,
       posts.post_parent                             AS parentId,
       wp_give_donationmeta_attach_meta_0.meta_value AS amount,
       wp_give_donationmeta_attach_meta_1.meta_value AS paymentCurrency,
       wp_give_donationmeta_attach_meta_2.meta_value AS paymentGateway,
       wp_give_donationmeta_attach_meta_3.meta_value AS donorId,
       wp_give_donationmeta_attach_meta_4.meta_value AS firstName,
       wp_give_donationmeta_attach_meta_5.meta_value AS lastName,
       wp_give_donationmeta_attach_meta_6.meta_value AS donorEmail,
       wp_give_donationmeta_attach_meta_7.meta_value AS subscriptionId
FROM wp_posts AS posts
         LEFT JOIN wp_give_donationmeta wp_give_donationmeta_attach_meta_0
                   ON posts.ID = wp_give_donationmeta_attach_meta_0.donation_id AND
                      wp_give_donationmeta_attach_meta_0.meta_key = '_give_payment_total'
         LEFT JOIN wp_give_donationmeta wp_give_donationmeta_attach_meta_1
                   ON posts.ID = wp_give_donationmeta_attach_meta_1.donation_id AND
                      wp_give_donationmeta_attach_meta_1.meta_key = '_give_payment_currency'
         LEFT JOIN wp_give_donationmeta wp_give_donationmeta_attach_meta_2
                   ON posts.ID = wp_give_donationmeta_attach_meta_2.donation_id AND
                      wp_give_donationmeta_attach_meta_2.meta_key = '_give_payment_gateway'
         LEFT JOIN wp_give_donationmeta wp_give_donationmeta_attach_meta_3
                   ON posts.ID = wp_give_donationmeta_attach_meta_3.donation_id AND
                      wp_give_donationmeta_attach_meta_3.meta_key = '_give_payment_donor_id'
         LEFT JOIN wp_give_donationmeta wp_give_donationmeta_attach_meta_4
                   ON posts.ID = wp_give_donationmeta_attach_meta_4.donation_id AND
                      wp_give_donationmeta_attach_meta_4.meta_key = '_give_donor_billing_first_name'
         LEFT JOIN wp_give_donationmeta wp_give_donationmeta_attach_meta_5
                   ON posts.ID = wp_give_donationmeta_attach_meta_5.donation_id AND
                      wp_give_donationmeta_attach_meta_5.meta_key = '_give_donor_billing_last_name'
         LEFT JOIN wp_give_donationmeta wp_give_donationmeta_attach_meta_6
                   ON posts.ID = wp_give_donationmeta_attach_meta_6.donation_id AND
                      wp_give_donationmeta_attach_meta_6.meta_key = '_give_payment_donor_email'
         LEFT JOIN wp_give_donationmeta wp_give_donationmeta_attach_meta_7
                   ON posts.ID = wp_give_donationmeta_attach_meta_7.donation_id AND
                      wp_give_donationmeta_attach_meta_7.meta_key = 'subscription_id'
         LEFT JOIN wp_give_donationmeta donationMeta ON posts.ID = donationMeta.donation_id
WHERE posts.post_type = 'give_payment'
  AND posts.post_status = 'give_subscription'
  AND donationMeta.meta_key = 'subscription_id'
  AND donationMeta.meta_value = '1'
ORDER BY posts.post_date DESC
```

Returned result when using

```php
DB::get_row($builder->getSQL());
```

```php
stdClass Object
(
    [id] => 93
    [createdAt] => 2022-02-21 00:00:00
    [updatedAt] => 2022-01-21 11:08:09
    [status] => give_subscription
    [parentId] => 92
    [amount] => 100.000000
    [paymentCurrency] => USD
    [paymentGateway] => manual
    [donorId] => 1
    [firstName] => Ante
    [lastName] => Laca
    [donorEmail] => dev-email@flywheel.local
)
```
