<?php

namespace Tests\Feature;

use App\Domains\Recurring\Daily;
use App\Domains\Sources\RecurringTypeEnum;
use App\Models\Source;
use Illuminate\Support\Facades\Bus;
use Tests\TestCase;

class DailyTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_daily(): void
    {
        Bus::fake();

        $source = Source::factory()
            ->create([
                'recurring' => RecurringTypeEnum::Daily,
                'last_run' => null,
            ]);

        (new Daily())->check();

        Bus::assertBatchCount(1);

    }
}
