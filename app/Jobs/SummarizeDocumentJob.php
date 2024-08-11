<?php

namespace App\Jobs;

use App\Domains\Agents\VerifyPromptInputDto;
use App\Domains\Agents\VerifyPromptOutputDto;
use App\Domains\Documents\StatusEnum;
use App\Domains\Prompts\SummarizeDocumentPrompt;
use App\Models\Document;
use Facades\App\Domains\Agents\VerifyResponseAgent;
use Facades\App\Domains\Tokenizer\Templatizer;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Laravel\Pennant\Feature;
use LlmLaraHub\LlmDriver\Functions\ToolTypes;
use LlmLaraHub\LlmDriver\LlmDriverFacade;
use LlmLaraHub\LlmDriver\Requests\MessageInDto;
use LlmLaraHub\LlmDriver\Responses\CompletionResponse;

class SummarizeDocumentJob implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected string $results = '';

    /**
     * Create a new job instance.
     */
    public function __construct(public Document $document, public string $prompt = '')
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {

        $content = [];

        /**
         * @NOTE
         * Hit max payload on system here so
         * not 100% sure the best way to break this up.
         */
        $divisor = too_large_for_json($this->document->document_chunks()->count());

        Log::info('[LaraChain] Divisor', [
            'count' => $divisor,
        ]);

        foreach ($this->document->document_chunks as $chunkIndex => $chunk) {
            if ($chunkIndex % $divisor == 0) {
                $content[] = $chunk->content;
            }
        }

        $content = implode(' ', $content);



        if (empty($this->prompt)) {
            $prompt = SummarizeDocumentPrompt::prompt($content);
        } else {
            $prompt = Templatizer::appendContext(true)
                ->handle($this->prompt, $content);
        }
        Log::info('[LaraChain] Summarizing Document', [
            'token_count_v2' => token_counter_v2($content),
            'token_count_v1' => token_counter($content),
            'prompt' => $prompt,
        ]);

        /** @var CompletionResponse $results */
        $results = LlmDriverFacade::driver(
            $this->document->getDriver()
        )->setToolType(ToolTypes::NoFunction)
            ->chat([
                MessageInDto::from([
                    'content' => $prompt,
                    'role' => 'user',
                ])
            ]);

        Log::info('[LaraChain] Summarizing Document Results', [
            'results' => $results->content,
        ]);

        $this->results = $results->content;

        $this->document->update([
            'summary' => $results->content,
            'status_summary' => StatusEnum::SummaryComplete,
        ]);

    }
}
