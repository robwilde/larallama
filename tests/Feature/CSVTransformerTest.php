<?php

namespace Tests\Feature;

use App\Domains\Documents\Transformers\CSVTransformer;
use App\Domains\Documents\Transformers\XlsxTransformer;
use App\Domains\Documents\TypesEnum;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class CSVTransformerTest extends TestCase
{
    use SharedSetupForPptFile;

    public function test_import_null(): void
    {

        $file = 'empty_rows.xlsx';

        $document = $this->setupFile($file);

        $document->update([
            'type' => TypesEnum::Xlsx,
        ]);

        $results = (new XlsxTransformer())->handle($document);

        $this->assertCount(0, $results);

        $this->assertDatabaseCount('documents', 0);
        $this->assertDatabaseCount('document_chunks', 0);
    }

    /**
     * A basic feature test example.
     */
    public function test_import_csv(): void
    {

        $file = 'strategies.csv';

        $document = $this->setupFile($file);

        $document->update([
            'type' => TypesEnum::CSV,
        ]);

        $results = (new CSVTransformer())->handle($document);

        $this->assertCount(5, $results);

        $this->assertDatabaseCount('documents', 5);
        $this->assertDatabaseCount('document_chunks', 5);
    }

    protected function tearDown(): void
    {
        if (! File::exists($this->document->pathToFile())) {
            File::delete($this->document->pathToFile());
        }
        parent::tearDown(); // TODO: Change the autogenerated stub
    }
}
