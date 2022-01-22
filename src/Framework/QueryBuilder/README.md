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
