<?php

namespace Reshadman\OptimisticLocking\Tests;

use Illuminate\Database\Eloquent\Model;
use Reshadman\OptimisticLocking\OptimisticLocking;

class StubLockablePostModel extends Model
{
    use OptimisticLocking;

    protected $guarded = [];

    protected $table = 'posts';
}