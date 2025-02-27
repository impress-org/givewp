# Migrations

We can think of migrations like version control for database.
We use them to create new tables, edit existing tables, and data manipulation.
Migrations are basically a representation of db schema at some point in time and that's why it's __important to never use models inside migration__.


## Migration types

There are two types of migrations supported in Give core - __Standard__ and __Batch__ migrations.

### Standard migration
We use these migrations each time we want to:
- Create new tables
- Edit existing tables (add/remove/update table columns)
- Delete tables

### Batch migration
Batch migrations, as name suggests, are processed in batches. We use them __only when we need to process large data sets__.

__Important__: You will never use this type of migration for table creation, or table editing.


## Migration structure

Both Standard and Batch migrations share these properties:
- __id__ - unique migration identifier
- __timestamp__ - represents the migration run order. __It's important to set the correct timestamp, otherwise you will brake the migration system__
- __title__ - used to describe migration and its purpose
- __source__ - represents the migration source. If you register a migration from add-on, it is important to set the source. By doing this, we have a clear indication where migration is registered.
Default value is: _GiveWP Core_

## Creating a migration

### Standard migration

Standard migration must extend the `Give\Framework\Migrations\Contracts\Migration` class.

```php
class MyMigration extends Migration
{
    /**
     * @inheritdoc
     */
    public static function id(): string
    {
        return 'give-my-migration';
    }

    /**
     * @inheritdoc
     */
    public static function title(): string
    {
        return 'My migration';
    }

    /**
     * @inheritdoc
     */
    public static function timestamp(): string
    {
        return strtotime('2025-02-27');
    }

    /**
     * @inheritDoc
     * @throws DatabaseMigrationException
     */
    public function run()
    {
        try {
            // do your thing
        } catch (DatabaseQueryException $exception) {
            throw new DatabaseMigrationException('An error occurred while doing something in my migration', 0, $exception);
        }
    }
}
```


### Batch migration

Batch migration must extend the `Give\Framework\Migrations\Contracts\BatchMigration` class.

### There are a couple of extra methods used in batch migrations.

- __runBatch($firstId, $lastId)__ - runs the batch and it uses firstId and lastId as a cursor
- __getItemsCount()__ - used to get the total items count
- __getBatchItemsAfter($lastId)__ - used as a cursor when setting the batches
- __getBatchSize()__ - returns the batch size
- __hasIncomingData($lastProcessedId)__ - checks if we have new data based on the last processed item id

```php
class MyBatchMigration extends BatchMigration
{
    /**
     * @inheritDoc
     */
    public static function id(): string
    {
        return 'my-batch-migration';
    }

    /**
     * @inheritDoc
     */
    public static function title(): string
    {
        return 'Do something with donations';
    }

    /**
     * @inheritdoc
     */
    public static function timestamp(): string
    {
        return strtotime('2025-02-28');
    }

    /**
     * Base query
     *
     * @unreleased
     */
    protected function query(): QueryBuilder
    {
        return DB::table('posts')->where('post_type', 'give_payment');
    }

    /**
     * @inheritDoc
     * @throws DatabaseMigrationException
     */
    public function runBatch($firstId, $lastId)
    {
        try {

            $query = $this->query()->select('ID');

            // Migration Runner will pass null for lastId in the last step
            if (is_null($lastId)) {
                $query->where('ID', $firstId, '>');
            } else {
                $query->whereBetween('ID', $firstId, $lastId);
            }

            $donations = $query->getAll();

            // do the processing

        } catch (DatabaseQueryException $exception) {
            throw new DatabaseMigrationException('An error occurred with my batch migration', 0, $exception);
        }
    }

    /**
     * @inheritDoc
     */
    public function getItemsCount(): int
    {
        return $this->query()->count();
    }

    /**
     * @inheritDoc
     */
    public function getBatchItemsAfter($lastId): ?array
    {
        $items = $this->query()
            ->select('ID')
            ->where('ID', $lastId, '>')
            ->orderBy('ID')
            ->limit($this->getBatchSize())
            ->getAll();

        if ( ! $items) {
            return null;
        }

        return [
            min($items)->ID,
            max($items)->ID,
        ];
    }

    /**
     * @inheritDoc
     */
    public function getBatchSize(): int
    {
        return 100;
    }

    /**
     * @inheritDoc
     */
    public function hasIncomingData($lastProcessedId): ?bool
    {
        return $this->query()
            ->where('ID', $lastProcessedId, '>')
            ->count();
    }
}
```
