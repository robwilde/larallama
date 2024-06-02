<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SourceResource extends JsonResource
{
    use HelperTrait;

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {

        $recurring = $this->getRecurring();
        $lastRun = $this->getLastRun();

        return [
            'id' => $this->id,
            'title' => $this->title,
            'collection_id' => $this->collection_id,
            'details' => $this->details,
            'active' => $this->active ? 'Yes' : 'No',
            'recurring' => $recurring,
            'description' => $this->description,
            'slug' => $this->slug,
            'last_run' => $lastRun,
            'type_key' => $this->type->value,
            'meta_data' => $this->meta_data,
            'secrets' => $this->secrets,
            'type' => str($this->type->name)->headline()->toString(),
        ];
    }
}
