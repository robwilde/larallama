<?php

namespace App\Http\Controllers;

use App\Domains\Outputs\OutputTypeEnum;
use App\Domains\Prompts\AnonymousChat;
use App\Http\Resources\CollectionResource;
use App\Http\Resources\DocumentResource;
use App\Http\Resources\OutputResource;
use App\Http\Resources\PublicOutputResource;
use App\Models\Collection;
use App\Models\Document;
use App\Models\Output;
use Illuminate\Support\Arr;
use LlmLaraHub\LlmDriver\Requests\MessageInDto;

class OutputController extends Controller
{
    protected OutputTypeEnum $outputTypeEnum = OutputTypeEnum::WebPage;

    protected string $edit_path = 'Outputs/WebPage/Edit';

    protected string $show_path = 'Outputs/WebPage/Show';

    protected string $create_path = 'Outputs/WebPage/Create';

    public function index(Collection $collection)
    {
        $chatResource = $chatResource = $this->getChatResource($collection);

        return inertia('Outputs/Index', [
            'chat' => $chatResource,
            'collection' => new CollectionResource($collection),
            'documents' => DocumentResource::collection(Document::query()
                ->where('collection_id', $collection->id)
                ->latest('id')
                ->get()),
            'available_outputs' => [
                [
                    'route' => route('collections.outputs.web_page.create',
                        [
                            'collection' => $collection->id,
                        ]
                    ),
                    'name' => 'Web Page',
                    'active' => true,
                ],
                [
                    'route' => route('collections.outputs.email_output.create',
                        [
                            'collection' => $collection->id,
                        ]
                    ),
                    'name' => 'Email',
                    'active' => true,
                ],
                [
                    'route' => null,
                    'name' => 'API',
                    'active' => false,
                ],
            ],
            'outputs' => OutputResource::collection($collection->outputs()->paginate(10)),
        ]);
    }

    public function store(Collection $collection)
    {

        $validated = request()->validate([
            'title' => 'required|string',
            'summary' => 'required|string',
            'active' => 'boolean|nullable',
            'public' => 'boolean|nullable',
            'recurring' => 'string|nullable',
            'meta_data' => 'array|nullable',
        ]);

        Output::create([
            'title' => $validated['title'],
            'summary' => $validated['summary'],
            'collection_id' => $collection->id,
            'recurring' => data_get($validated, 'recurring', null),
            'active' => data_get($validated, 'active', false),
            'public' => data_get($validated, 'public', false),
            'type' => $this->outputTypeEnum,
            'meta_data' => data_get($validated, 'meta_data', []),
        ]);

        request()->session()->flash('flash.banner', 'Output added successfully');

        return to_route('collections.outputs.index', $collection);
    }

    public function edit(Collection $collection, Output $output)
    {
        return inertia($this->edit_path, [
            'output' => $output,
            'collection' => new CollectionResource($collection),
        ]);
    }

    public function update(Collection $collection, Output $output)
    {
        $validated = request()->validate([
            'title' => 'required|string',
            'recurring' => 'string|nullable',
            'summary' => 'required|string',
            'active' => 'boolean|nullable',
            'public' => 'boolean|nullable',
            'meta_data' => 'array|nullable',
        ]);

        $output->update($validated);

        request()->session()->flash('flash.banner', 'Updated');

        return back();
    }

    public function show(Output $output)
    {
        if (! auth()->check() && ! $output->public) {
            abort(404);
        }

        if (! $output->active) {
            abort(404);
        }

        return inertia($this->show_path, [
            'output' => new PublicOutputResource($output),
            'messages' => data_get($this->getChatMessages(), 'messages', []),
        ]);
    }

    public function create(Collection $collection)
    {
        return inertia($this->create_path, [
            'collection' => new CollectionResource($collection),
        ]);
    }

    protected function getChatMessages(): array
    {
        $messages = request()->session()->only(['messages']);

        if (empty($messages)) {
            $messages = MessageInDto::from([
                'content' => AnonymousChat::system(),
                'role' => 'system',
                'is_ai' => true,
                'show' => false,
            ]);

            request()->session()->push('messages', $messages);
            $messages = Arr::wrap($messages);
        }

        return $messages;
    }

    protected function setChatMessages(string $input, string $role = 'user')
    {
        $this->getChatMessages();

        $messages = MessageInDto::from([
            'content' => str($input)->markdown(),
            'role' => $role,
            'is_ai' => $role !== 'user',
            'show' => $role !== 'system',
        ]);

        request()->session()->push('messages', $messages);

        return $messages;
    }
}
