# Query Builder

Query Builder helper class is used to write SQL queries

- [DB](#db)

- [Select statements](#select-statements)

- [From Clause](#from-clause)

- [Joins](#joins)
    - [LEFT Join](#left-join)
    - [RIGHT Join](#right-join)
    - [INNER Join](#inner-join)
    - [Join Raw](#join-raw)
    - [Advanced Join Clauses](#advanced-join-clauses)

- [Unions](#unions)

- [Where Clauses](#where-clauses)
    - [Where](#where-clauses)
    - [Where IN](#where-in-clauses)
    - [Where BETWEEN](#where-between-clauses)
    - [Where LIKE](#where-like-clauses)
    - [Where IS NULL](#where-is-null-clauses)
    - [Where EXISTS](#where-exists-clauses)
    - [Subquery Where Clauses](#subquery-where-clauses)
    - [Nested Where Clauses](#nested-where-clauses)

- [Ordering, Grouping, Limit & Offset](#ordering-grouping-limit--offset)
    - [Ordering](#ordering)
    - [Grouping](#grouping)
    - [Limit & Offset](#limit--offset)

- [Special methods for working with meta tables](#special-methods-for-working-with-meta-tables)
  - [attachMeta](#attachmeta)
  - [configureMetaTable](#configuremetatable)


## DB

`DB` class is a static decorator for the `$wpdb` class, but it has a few methods that are exceptions to that.
Methods `DB::table()` and `DB::raw()`.

`DB::table()` is a static facade for the `QueryBuilder` class, and it accepts two string arguments, `$tableName`
and `$tableAlias`.

Under the hood, `DB::table()` will create a new `QueryBuilder` instance, and it will use `QueryBuilder::from` method to set the table name. Calling `QueryBuilder::from` when using `DB::table` method will return an unexpected result. Basically, we are telling the `QueryBuilder` that we want to select data from two tables.

### Important

When using `DB::table(tableName)` method, the `tableName` is prefixed with `$wpdb->prefix`. To bypass that, you can
use `DB::raw` method which will tell `QueryBuilder` not to prefix the table name.

```php
DB::table(DB::raw('posts'));
```

## Select statements

#### Available methods - select / selectRaw / distinct

By using the `QueryBuilder::select` method, you can specify a custom `SELECT` statement for the query.

```php
DB::table('posts')->select('ID', 'post_title', 'post_date');
```

Generated SQL

```sql
SELECT ID, post_title, post_date FROM wp_posts
```

You can also specify the column alias by providing an array _[column, alias]_ to the `QueryBuilder::select` method.

```php
DB::table('posts')->select(
    ['ID', 'post_id'],
    ['post_status', 'status'],
    ['post_date', 'createdAt']
);
```

Generated SQL:

```sql
SELECT ID AS post_id, post_status AS status, post_date AS createdAt FROM wp_posts
```

The distinct method allows you to force the query to return distinct results:

```php
DB::table('posts')->select('post_status')->distinct();
```

You can also specify a custom `SELECT` statement with `QueryBuilder::selectRaw` method. This method accepts an optional array of
bindings as its second argument.

```php
DB::table('posts')
    ->select('ID')
    ->selectRaw('(SELECT ID from wp_posts WHERE post_status = %s) AS subscriptionId', 'give_subscription');
```

Generated SQL

```sql
SELECT ID, (SELECT ID from wp_posts WHERE post_status = 'give_subscription') AS subscriptionId FROM wp_posts
```

By default, all columns will be selected from a database table.

```php
DB::table('posts');
```

Generated SQL

```sql
SELECT * FROM wp_posts
```

## From clause

By using the `QueryBuilder::from()` method, you can specify a custom `FROM` clause for the query.


```php
$builder = new QueryBuilder();
$builder->from('posts');
```

Set multiple `FROM` clauses

```php
$builder = new QueryBuilder();
$builder->from('posts');
$builder->from('postmeta');
```

Generated SQL

```sql
SELECT * FROM wp_posts, wp_postmeta
```

### Important

Table name is prefixed with `$wpdb->prefix`. To bypass that, you can
use `DB::raw` method which will tell `QueryBuilder` not to prefix the table name.

```php
$builder = new QueryBuilder();
$builder->from(DB::raw('posts'));
```

## Joins

The Query Builder may also be used to add `JOIN` clauses to your queries.

#### Available methods - leftJoin / rightJoin / innerJoin / joinRaw / join

### LEFT Join

`LEFT JOIN` clause.

```php
DB::table('posts', 'donationsTable')
    ->select('donationsTable.*', 'metaTable.*')
    ->leftJoin('give_donationmeta', 'donationsTable.ID', 'metaTable.donation_id', 'metaTable');
```

Generated SQL

```sql
SELECT donationsTable.*, metaTable.* FROM wp_posts AS donationsTable LEFT JOIN wp_give_donationmeta metaTable ON donationsTable.ID = metaTable.donation_id
```

### RIGHT Join

`RIGHT JOIN` clause.

```php
DB::table('posts', 'donationsTable')
    ->select('donationsTable.*', 'metaTable.*')
    ->rightJoin('give_donationmeta', 'donationsTable.ID', 'metaTable.donation_id', 'metaTable');
```

Generated SQL

```sql
SELECT donationsTable.*, metaTable.* FROM wp_posts AS donationsTable RIGHT JOIN wp_give_donationmeta metaTable ON donationsTable.ID = metaTable.donation_id
```

### INNER Join

`INNER JOIN` clause.

```php
DB::table('posts', 'donationsTable')
    ->select('donationsTable.*', 'metaTable.*')
    ->innerJoin('give_donationmeta', 'donationsTable.ID', 'metaTable.donation_id', 'metaTable');
```

Generated SQL

```sql
SELECT donationsTable.*, metaTable.* FROM wp_posts AS donationsTable INNER JOIN wp_give_donationmeta metaTable ON donationsTable.ID = metaTable.donation_id
```

### Join Raw

Insert a raw expression into query.

```php
DB::table('posts', 'donationsTable')
    ->select('donationsTable.*', 'metaTable.*')
    ->joinRaw('LEFT JOIN give_donationmeta metaTable ON donationsTable.ID = metaTable.donation_id');
```

Generated SQL

```sql
SELECT donationsTable.*, metaTable.* FROM wp_posts AS donationsTable LEFT JOIN give_donationmeta metaTable ON donationsTable.ID = metaTable.donation_id
```

### Advanced Join Clauses

**The closure will receive a `Give\Framework\QueryBuilder\JoinQueryBuilder` instance**

```php
DB::table('posts')
    ->select('donationsTable.*', 'metaTable.*')
    ->join(function (JoinQueryBuilder $builder) {
        $builder
            ->leftJoin('give_donationmeta', 'metaTable')
            ->on('donationsTable.ID', 'metaTable.donation_id')
            ->and('metaTable.meta_key', 'some_key', $qoute = true);
    });
```

Generated SQL

```sql
SELECT donationsTable.*, metaTable.* FROM wp_posts LEFT JOIN wp_give_donationmeta metaTable ON donationsTable.ID = metaTable.donation_id AND metaTable.meta_key = 'some_key'
```

## Unions

The Query Builder also provides a convenient method to "union" two or more queries together.

#### Available methods - union / unionAll

### Union

```php
$donations = DB::table('give_donations')->where('author_id', 10);

DB::table('give_subscriptions')
    ->select('ID')
    ->where('ID', 100, '>')
    ->union($donations);
```

Generated SQL:

```sql
SELECT ID FROM wp_give_subscriptions WHERE ID > '100' UNION SELECT * FROM wp_give_donations WHERE author_id = '10'
```

## Where Clauses

You may use the Query Builder's `where` method to add `WHERE` clauses to the query.

### Where

#### Available methods - where / orWhere

```php
DB::table('posts')->where('ID', 5);
```

Generated SQL

```sql
SELECT * FROM wp_posts WHERE ID = '5'
```

Using `where` multiple times.

```php
DB::table('posts')
    ->where('ID', 5)
    ->where('post_author', 10);
```

Generated SQL

```sql
SELECT * FROM wp_posts WHERE ID = '5' AND post_author = '10'
```

### Where IN Clauses

#### Available methods - whereIn / orWhereIn / whereNotIn / orWhereNotIn

The `QueryBuilder::whereIn` method verifies that a given column's value is contained within the given array:

```php
DB::table('posts')->whereIn('ID', [1, 2, 3]);
```

Generated SQL

```sql
SELECT * FROM wp_posts WHERE ID IN ('1','2','3')
```

You can also pass a closure as the second argument which will generate a subquery.

**The closure will receive a `Give\Framework\QueryBuilder\QueryBuilder` instance**

```php
DB::table('posts')
    ->whereIn('ID', function (QueryBuilder $builder) {
        $builder
            ->select(['meta_value', 'donation_id'])
            ->from('give_donationmeta')
            ->where('meta_key', 'donation_id');
    });
```

Generated SQL

```sql
SELECT * FROM wp_posts WHERE ID IN (SELECT meta_value AS donation_id FROM wp_give_donationmeta WHERE meta_key = 'donation_id')
```

### Where BETWEEN Clauses

The `QueryBuilder::whereBetween` method verifies that a column's value is between two values:

#### Available methods - whereBetween / orWhereBetween / whereNotBetween / orWhereNotBetween

```php
DB::table('posts')->whereBetween('ID', 0, 100);
```

Generated SQL

```sql
SELECT * FROM wp_posts WHERE ID BETWEEN '0' AND '100'
```

### Where LIKE Clauses

The `QueryBuilder::whereLike` method searches for a specified pattern in a column.

#### Available methods - whereLike / orWhereLike / whereNotLike / orWhereNotLike

```php
DB::table('posts')->whereLike('post_title', 'Donation');
```

Generated SQL

```sql
SELECT * FROM wp_posts WHERE post_title LIKE '%Donation%'
```

### Where IS NULL Clauses

The `QueryBuilder::whereIsNull` method verifies that a column's value is `NULL`

#### Available methods - whereIsNull / orWhereIsNull / whereIsNotNull / orWhereIsNotNull

```php
DB::table('posts')->whereIsNull('post_author');
```

Generated SQL

```sql
SELECT * FROM wp_posts WHERE post_author IS NULL
```

### Where EXISTS Clauses

The `QueryBuilder::whereExists` method allows you to write `WHERE EXISTS` SQL clauses. The `QueryBuilder::whereExists` method accepts a closure which will receive a `QueryBuilder` instance.

#### Available methods - whereExists / whereNotExists

```php
DB::table('give_donationmeta')
    ->whereExists(function (QueryBuilder $builder) {
        $builder
            ->select(['meta_value', 'donation_id'])
            ->where('meta_key', 'donation_id');
    });
```

Generated SQL

```sql
SELECT * FROM wp_give_donationmeta WHERE EXISTS (SELECT meta_value AS donation_id WHERE meta_key = 'donation_id')
```

### Subquery Where Clauses

Sometimes you may need to construct a `WHERE` clause that compares the results of a subquery to a given value.

```php
DB::table('posts')
    ->where('post_author', function (QueryBuilder $builder) {
        $builder
            ->select(['meta_value', 'author_id'])
            ->from('postmeta')
            ->where('meta_key', 'donation_id')
            ->where('meta_value', 10);
    });
```

Generated SQL

```sql
SELECT * FROM wp_posts WHERE post_author = (SELECT meta_value AS author_id FROM wp_postmeta WHERE meta_key = 'donation_id' AND meta_value = '10')
```

### Nested Where Clauses

Sometimes you may need to construct a `WHERE` clause that has nested WHERE clauses.

**The closure will receive a `Give\Framework\QueryBuilder\WhereQueryBuilder` instance**

```php
DB::table('posts')
    ->where('post_author', 10)
    ->where(function (WhereQueryBuilder $builder) {
        $builder
            ->where('post_status', 'published')
            ->orWhere('post_status', 'donation')
            ->whereIn('ID', [1, 2, 3]);
    });
```

Generated SQL

```sql
SELECT * FROM wp_posts WHERE post_author = '10' AND ( post_status = 'published' OR post_status = 'donation' AND ID IN ('1','2','3'))
```

## Ordering, Grouping, Limit & Offset

### Ordering

The `QueryBuilder::orderBy` method allows you to sort the results of the query by a given column.

```php
DB::table('posts')->orderBy('ID');
```

Generated SQL

```sql
SELECT * FROM wp_posts ORDER BY ID ASC
```

Sorting result by multiple columns

```php
DB::table('posts')
    ->orderBy('ID')
    ->orderBy('post_date', 'DESC');
```

Generated SQL

```sql
SELECT * FROM wp_posts ORDER BY ID ASC, post_date DESC
```

### Grouping

The `QueryBuilder::groupBy` and `QueryBuilder::having*` methods are used to group the query results.

#### Available methods - groupBy / having / orHaving / havingCount / orHavingCount / havingMin / orHavingMin / havingMax / orHavingMax / havingAvg / orHavingAvg / havingSum / orHavingSum / havingRaw

```php
DB::table('posts')
    ->groupBy('id')
    ->having('id', '>', 10);
```

Generated SQL

```sql
SELECT * FROM wp_posts WHERE GROUP BY id HAVING 'id' > '10'
```

### Limit & Offset

Limit the number of results returned from the query.

#### Available methods - limit / offset

```php
DB::table('posts')
    ->limit(10)
    ->offset(20);
```

Generated SQL

```sql
SELECT * FROM wp_posts LIMIT 10 OFFSET 20
```

## Special methods for working with meta tables

Query Builder has a few special methods for abstracting the work with meta tables.


### attachMeta

`attachMeta` is used to include meta table _meta_key_ column values as columns in the `SELECT` statement.

Under the hood `QueryBuilder::attachMeta` will add join clause for each defined `meta_key` column. And each column will be
added in select statement as well, which means the meta columns will be returned in query result. Aliasing meta columns
is recommended when using `QueryBuilder::attachMeta` method.

```php
DB::table('posts')
    ->select(
        ['ID', 'id'],
        ['post_date', 'createdAt'],
        ['post_modified', 'updatedAt'],
        ['post_status', 'status'],
        ['post_parent', 'parentId']
    )
    ->attachMeta('give_donationmeta', 'ID', 'donation_id',
        ['_give_payment_total', 'amount'],
        ['_give_payment_currency', 'paymentCurrency'],
        ['_give_payment_gateway', 'paymentGateway'],
        ['_give_payment_donor_id', 'donorId'],
        ['_give_donor_billing_first_name', 'firstName'],
        ['_give_donor_billing_last_name', 'lastName'],
        ['_give_payment_donor_email', 'donorEmail'],
        ['subscription_id', 'subscriptionId']
    )
    ->leftJoin('give_donationmeta', 'ID', 'donationMeta.donation_id', 'donationMeta')
    ->where('post_type', 'give_payment')
    ->where('post_status', 'give_subscription')
    ->where('donationMeta.meta_key', 'subscription_id')
    ->where('donationMeta.meta_value', 1)
    ->orderBy('post_date', 'DESC');
```

Generated SQL:

```sql
SELECT ID                                         AS id,
       post_date                                  AS createdAt,
       post_modified                              AS updatedAt,
       post_status                                AS status,
       post_parent                                AS parentId,
       give_donationmeta_attach_meta_0.meta_value AS amount,
       give_donationmeta_attach_meta_1.meta_value AS paymentCurrency,
       give_donationmeta_attach_meta_2.meta_value AS paymentGateway,
       give_donationmeta_attach_meta_3.meta_value AS donorId,
       give_donationmeta_attach_meta_4.meta_value AS firstName,
       give_donationmeta_attach_meta_5.meta_value AS lastName,
       give_donationmeta_attach_meta_6.meta_value AS donorEmail,
       give_donationmeta_attach_meta_7.meta_value AS subscriptionId
FROM wp_posts
         LEFT JOIN wp_give_donationmeta give_donationmeta_attach_meta_0
                   ON ID = give_donationmeta_attach_meta_0.donation_id AND
                      give_donationmeta_attach_meta_0.meta_key = '_give_payment_total'
         LEFT JOIN wp_give_donationmeta give_donationmeta_attach_meta_1
                   ON ID = give_donationmeta_attach_meta_1.donation_id AND
                      give_donationmeta_attach_meta_1.meta_key = '_give_payment_currency'
         LEFT JOIN wp_give_donationmeta give_donationmeta_attach_meta_2
                   ON ID = give_donationmeta_attach_meta_2.donation_id AND
                      give_donationmeta_attach_meta_2.meta_key = '_give_payment_gateway'
         LEFT JOIN wp_give_donationmeta give_donationmeta_attach_meta_3
                   ON ID = give_donationmeta_attach_meta_3.donation_id AND
                      give_donationmeta_attach_meta_3.meta_key = '_give_payment_donor_id'
         LEFT JOIN wp_give_donationmeta give_donationmeta_attach_meta_4
                   ON ID = give_donationmeta_attach_meta_4.donation_id AND
                      give_donationmeta_attach_meta_4.meta_key = '_give_donor_billing_first_name'
         LEFT JOIN wp_give_donationmeta give_donationmeta_attach_meta_5
                   ON ID = give_donationmeta_attach_meta_5.donation_id AND
                      give_donationmeta_attach_meta_5.meta_key = '_give_donor_billing_last_name'
         LEFT JOIN wp_give_donationmeta give_donationmeta_attach_meta_6
                   ON ID = give_donationmeta_attach_meta_6.donation_id AND
                      give_donationmeta_attach_meta_6.meta_key = '_give_payment_donor_email'
         LEFT JOIN wp_give_donationmeta give_donationmeta_attach_meta_7
                   ON ID = give_donationmeta_attach_meta_7.donation_id AND
                      give_donationmeta_attach_meta_7.meta_key = 'subscription_id'
         LEFT JOIN wp_give_donationmeta donationMeta ON ID = donationMeta.donation_id
WHERE post_type = 'give_payment'
  AND post_status = 'give_subscription'
  AND donationMeta.meta_key = 'subscription_id'
  AND donationMeta.meta_value = '1'
ORDER BY post_date DESC
```

Returned result:

```
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
    [subscriptionId] => 1
)
```

### configureMetaTable

By default, `QueryBuilder::attachMeta` will use `meta_key`, and `meta_value` as meta table column names, but that sometimes might not be the case.

With `QueryBuilder::configureMetaTable` you can define a custom `meta_key` and `meta_value` column names.

```php
DB::table('posts')
    ->select(
        ['ID', 'id'],
        ['post_date', 'createdAt']
    )
    ->configureMetaTable(
        'give_donationmeta',
        'custom_meta_key',
        'custom_meta_value'
    )
    ->attachMeta(
        'give_donationmeta',
        'ID',
        'donation_id',
        ['_give_payment_total', 'amount']
    )
    ->leftJoin('give_donationmeta', 'ID', 'donationMeta.donation_id', 'donationMeta')
    ->where('post_type', 'give_payment')
    ->where('post_status', 'give_subscription')
    ->where('donationMeta.custom_meta_key', 'subscription_id')
    ->where('donationMeta.custom_meta_value', 1);
```

Generated SQL

```sql
SELECT ID AS id, post_date AS createdAt, give_donationmeta_attach_meta_0.custom_meta_value AS amount
FROM wp_posts
         LEFT JOIN wp_give_donationmeta give_donationmeta_attach_meta_0
                   ON ID = give_donationmeta_attach_meta_0.donation_id AND
                      give_donationmeta_attach_meta_0.custom_meta_key = '_give_payment_total'
         LEFT JOIN wp_give_donationmeta donationMeta ON ID = donationMeta.donation_id
WHERE post_type = 'give_payment'
  AND post_status = 'give_subscription'
  AND donationMeta.custom_meta_key = 'subscription_id'
  AND donationMeta.custom_meta_value = '1'
```

