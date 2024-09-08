<?php

namespace Tests\Feature\Models;

use App\Models\Project;
use Tests\TestCase;

class ProjectTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_model(): void
    {
        $model = Project::factory()->create();
        $this->assertNotNull($model->name);
        $this->assertNotNull($model->status);
        $this->assertNotNull($model->content);
        $this->assertNotNull($model->team->id);
    }
}
