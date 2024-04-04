<?php

namespace App\LlmDriver\Functions;


use App\Models\Chat;
use App\LlmDriver\Requests\MessageInDto;
use App\LlmDriver\Responses\FunctionResponse;

abstract class FunctionContract
{
    protected string $name;

    protected string $description;

    protected string $type = 'object';

    /**
     * 
     * @param MessageInDto[] $messageArray 
     * @param App\LlmDriver\Functions\Chat $chat 
     * @param FunctionCallDto $functionCallDto 
     * @return array 
     */
    abstract public function handle(
        array $messageArray,
        Chat $chat,
        FunctionCallDto $functionCallDto): FunctionResponse;

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

    protected function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @return PropertyDto[]
     */
    abstract protected function getProperties(): array;
}
