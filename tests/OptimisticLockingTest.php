<?php

namespace Reshadman\OptimisticLocking\Tests;

use Reshadman\OptimisticLocking\StaleModelLockingException;

class OptimisticLockingTest extends TestCase
{
    public function test_throws_exception_on_concurrent_change()
    {
        $truth = StubLockablePostModel::create([
            'title' => 'Before Update Title.',
            'description' => 'Before Update Description.'
        ]);

        $first = StubLockablePostModel::find($truth->id);
        $second = StubLockablePostModel::find($truth->id);

        $this->expectException(StaleModelLockingException::class);

        $first->title = $wantedTitle = 'Title changed by first process.';
        $this->assertTrue($first->save());

        try {
            $second->title = 'Title changed by second process.';
            $second->save();
        } catch (StaleModelLockingException $e) {
            $fetchedAfterFirstUpdate = StubLockablePostModel::find($truth->id);
            $this->assertEquals($fetchedAfterFirstUpdate->title, $wantedTitle);
            $this->assertEquals($fetchedAfterFirstUpdate->lock_version, $first->lock_version);
            $this->assertEquals($fetchedAfterFirstUpdate->lock_version, $truth->lock_version + 1);

            throw $e;
        }
    }
}