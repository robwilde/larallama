<?php

namespace App\Http\Controllers;

use App\Models\Document;

class DeleteDocumentsController extends Controller
{
    public function delete()
    {
        $validated = \request()->validate([
            'documents' => ['required', 'array'],
        ]);

        foreach ($validated['documents'] as $id) {
            $document = Document::with('document_chunks')->find($id);
            $document->document_chunks()->delete();

            $document->tags()->delete();
            $document->delete();
        }

        \request()->session()->flash('flash.banner', 'Deleted Documents');

        return back();
    }
}
