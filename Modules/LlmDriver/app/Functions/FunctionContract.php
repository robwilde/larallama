<?php

namespace LlmLaraHub\LlmDriver\Functions;

use App\Models\Message;
use Illuminate\Bus\Batch;
use LlmLaraHub\LlmDriver\Responses\FunctionResponse;

abstract class FunctionContract
{
    protected string $name;

    protected string $description;

    protected string $type = 'object';

    public Batch $batch;

    public function setBatch(Batch $batch): self
    {
        $this->batch = $batch;

        return $this;
    }

    abstract public function handle(
        Message $message,
    ): FunctionResponse;

    public function getFunction(): FunctionDto
    {
        return FunctionDto::from(
            [
                'name' => $this->getName(),
                'description' => $this->getDescription(),
                'parameters' => [
                    'type' => $this->type,
                    'properties' => $this->getProperties(),
                ],
            ]
        );
    }

    protected function getName(): string
    {
        return $this->name;
    }

    protected function getKey(): string
    {
        return $this->name;
    }

    protected function getDescription(): string
    {
        return $this->description;
    }

    public function runAsBatch(): bool
    {
        return false;
    }

    /**
     * @return PropertyDto[]
     */
    abstract protected function getProperties(): array;
}
