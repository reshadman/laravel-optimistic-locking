# Laravel Optimistic Locking
Adds optimistic locking feature to eloquent models.

## Installation 
```bash
composer require reshadman/laravel-optimistic-locking
```

## Usage

### Basic usage
use the `\Reshadman\OptimisticLocking\OptimisticLocking` trait
in your model:

```php
<?php

class BlogPost extends Model {
    use OptimisticLocking;
}
```

and add the `lock_version` integer field to the table of the model:
```php
<?php

$schema->integer('lock_version')->unsigned()->nullable();
```

Then you are ready to go, if the same resource is edited by two 
different processes **CONCURRENTLY** then the following exception
will be raised:

```
\Reshadman\OptimisticLocking\StaleModelLockingException
```

You should catch the above exception and act properly based 
on your business logic.

### Maintaining lock_version during business transactions

You can keep track of a lock version during a business transaction:
```html
<input type="hidden" name="lock_version" value="{{$blogPost->lock_version}}" 
```

So if two authors are editing the same content concurrently,
you can keep track of your **Read State**, And ask the second
author to rewrite his changes.

### Disabling and enabling optimistic locking
You can disable and enable optimistic locking for a specific 
instance:

```php
<?php
$blogPost->disableLocking();
$blogPost->enableLocking();
```

By default optimistic locking is enabled when you use
`OptimisticLocking` trait in your model, to alter the default
behaviour you can set the lock strictly to `false`:

```php
<?php
class BlogPost extends \Illuminate\Database\Eloquent\Model 
{
    use \Reshadman\OptimisticLocking\OptimisticLocking;
    
    protected $lock = false;
}
```
and then you may enable it: `$blogPost->enableLocking();`

### Use a different column for tracking version
By default the `lock_version` column is used for tracking
version, you can alter that by overriding the following method
of the trait:

```php
<?php
class BlogPost extends \Illuminate\Database\Eloquent\Model
{
    use \Reshadman\OptimisticLocking\OptimisticLocking;
    
    /**
     * Name of the lock version column.
     *
     * @return string
     */
    protected static function lockVersionColumn()
    {
        return 'track_version';
    }
}
```

## What is optimistic locking?
For detailed explanation read the concurrency section of [*Patterns of Enterprise Application Architecture by Martin Fowler*](https://www.martinfowler.com/eaaCatalog/optimisticOfflineLock.html).

There are two way to approach generic concurrency race conditions:
 1. Do not allow other processes (or users) to read and update the same
 resource (Pessimistic Locking)
 2. Allow other processes to read the same resource concurrently, but
 do not allow further update, if one of the processes updated the resource before the others (Optimistic locking).

Laravel allows Pessimistic locking as described in the documentation,
this package allows you to have Optimistic locking in a rails like way.

### What happens during an optimistic lock?
Every time you perform an upsert action to your resource(model), 
the `lock_version` counter field in the table is incremented by `1`,
If you read a resource and another process updates the resource
after you read it, the true version counter is incremented by one,
If the current process attempts to update the model, simply a
`StaleModelLockingException` will be thrown, and you should
handle the race condition (merge, retry, ignore) based on your
business logic. That is simply via adding the following criteria
to the update query of a **optimistically lockable model**:

```php
<?php
$query->where('id', $this->id)
    ->where('lock_version', $this->lock_version + 1)
    ->update($changes);
```

If the resource has been updated before your update attempt, then the above will simply
update **no** records and it means that the model has been updated before
current attempt or it has been deleted.

## Running tests
Clone the repo, perform a composer install and run:

```vendor/bin/phpunit```