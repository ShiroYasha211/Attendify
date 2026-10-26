<?php

namespace Tests\Feature;

use App\Http\Controllers\Api\Student\FlashcardController;
use App\Models\FlashcardItem;
use App\Models\FlashcardPack;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class FlashcardImportAndEditingTest extends TestCase
{
    private array $temporaryFiles = [];

    protected function setUp(): void
    {
        parent::setUp();
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->softDeletes();
        });
        DB::table('users')->insert([['id' => 1, 'name' => 'Owner'], ['id' => 2, 'name' => 'Other']]);
        foreach ([
            '2026_03_22_060000_create_flashcard_packs_table.php',
            '2026_03_22_060001_create_flashcard_items_table.php',
            '2026_03_22_060002_create_flashcard_progress_table.php',
            '2026_04_21_180000_expand_flashcards_for_nested_mixed_mode.php',
        ] as $migration) {
            (require database_path('migrations/'.$migration))->up();
        }
    }

    protected function tearDown(): void
    {
        foreach ($this->temporaryFiles as $path) {
            if (is_file($path)) {
                unlink($path);
            }
        }
        parent::tearDown();
    }

    private function pack(array $attributes = []): FlashcardPack
    {
        return FlashcardPack::create($attributes + [
            'user_id' => 1, 'title' => 'Personal', 'display_mode' => 'one_line',
        ]);
    }

    private function request(array $data = [], int $user = 1): Request
    {
        $request = Request::create('/test', 'POST', $data);
        $request->setUserResolver(fn () => User::findOrFail($user));

        return $request;
    }

    private function preview(FlashcardPack $pack, string $contents, string $extension = 'csv'): array
    {
        $path = tempnam(sys_get_temp_dir(), 'moeen_import_test_');
        $this->temporaryFiles[] = $path;
        file_put_contents($path, $contents);
        $request = $this->request();
        $request->files->set('file', new UploadedFile($path, 'cards.'.$extension, null, null, true));
        $response = app(FlashcardController::class)->importPreview($request, $pack->id);
        $this->assertSame(200, $response->getStatusCode(), $response->getContent());
        $preview = $response->getData(true)['data'];
        $this->temporaryFiles[] = storage_path('app/'.$preview['temp_file']);

        return $preview;
    }

    private function confirm(FlashcardPack $pack, array $preview, array $overrides = [])
    {
        return app(FlashcardController::class)->importConfirm($this->request($overrides + [
            'temp_file' => $preview['temp_file'], 'duplicate_strategy' => 'ignore',
            'has_headers' => $preview['is_first_row_header'],
        ] + $preview['guessed_mapping']), $pack->id);
    }

    public function test_downloaded_template_imports_first_option_and_all_item_types(): void
    {
        $pack = $this->pack();
        // StreamedResponse exposes the CSV through its callback, not getContent().
        ob_start();
        app(FlashcardController::class)->downloadImportTemplate()->sendContent();
        $template = ob_get_clean();
        $preview = $this->preview($pack, $template);
        $this->assertSame([2, 3, 4, 5, 6, 7], $preview['guessed_mapping']['options_cols']);
        $response = $this->confirm($pack, $preview);
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame(4, $response->getData(true)['data']['report']['success_count']);
        $mcq = $pack->items()->where('item_type', 'mcq')->firstOrFail();
        $this->assertSame(['الأحمر', 'الأسود', 'الأبيض', 'الرمادي'], $mcq->options);
        $this->assertSame(0, $mcq->correct_option);
    }

    public function test_single_column_and_semicolon_csv_import_correctly(): void
    {
        $pack = $this->pack();
        $preview = $this->preview($pack, "معلومة أولى\nمعلومة ثانية\n");
        $this->assertFalse($preview['is_first_row_header']);
        $this->assertSame(0, $preview['guessed_mapping']['front_content_col']);
        $this->assertSame(2, $this->confirm($pack, $preview)->getData(true)['data']['report']['success_count']);

        $preview = $this->preview($pack, "السؤال;الخيار 1;الخيار 2;الإجابة الصحيحة;نوع البطاقة\nQ;A;B;2;mcq\n");
        $this->assertSame(3, $preview['guessed_mapping']['correct_option_col']);
        $this->confirm($pack, $preview);
        $this->assertSame(1, $pack->items()->where('item_type', 'mcq')->firstOrFail()->correct_option);
    }

    public function test_real_xlsx_is_previewed_and_imported(): void
    {
        $sheet = new Spreadsheet();
        $sheet->getActiveSheet()->fromArray([['السؤال', 'الإجابة', 'نوع البطاقة'], ['Q', 'A', 'qa']]);
        $path = tempnam(sys_get_temp_dir(), 'moeen_xlsx_test_');
        $this->temporaryFiles[] = $path;
        (new Xlsx($sheet))->save($path);
        $sheet->disconnectWorksheets();
        $pack = $this->pack();
        $preview = $this->preview($pack, file_get_contents($path), 'xlsx');
        $response = $this->confirm($pack, $preview);
        $this->assertSame(1, $response->getData(true)['data']['report']['success_count']);
        $this->assertSame('A', $pack->items()->firstOrFail()->back_content);
    }

    public function test_missing_excel_reader_returns_actionable_service_error(): void
    {
        $sheet = new Spreadsheet();
        $sheet->getActiveSheet()->fromArray([['السؤال'], ['Q']]);
        $path = tempnam(sys_get_temp_dir(), 'moeen_missing_reader_');
        $this->temporaryFiles[] = $path;
        (new Xlsx($sheet))->save($path);
        $sheet->disconnectWorksheets();
        $request = $this->request();
        $request->files->set('file', new UploadedFile($path, 'cards.xlsx', null, null, true));
        $controller = new class extends FlashcardController {
            protected function excelReaderAvailable(): bool { return false; }
        };
        $response = $controller->importPreview($request, $this->pack()->id);
        $this->assertSame(503, $response->getStatusCode());
        $this->assertStringContainsString('CSV', $response->getData(true)['message']);
        $this->assertSame(0, FlashcardItem::count());
    }

    public function test_confirmation_rejects_other_account_files_and_invalid_columns(): void
    {
        $pack = $this->pack();
        $preview = $this->preview($pack, "السؤال\nQ\n");
        $this->assertSame(422, $this->confirm($pack, $preview, ['front_content_col' => 9])->getStatusCode());
        $response = app(FlashcardController::class)->importConfirm($this->request([
            'temp_file' => str_replace('student_1_', 'student_2_', $preview['temp_file']),
            'duplicate_strategy' => 'ignore', 'has_headers' => true,
        ] + $preview['guessed_mapping']), $pack->id);
        $this->assertSame(422, $response->getStatusCode());
        $this->assertSame(0, $pack->items()->count());
    }

    public function test_invalid_or_blank_correct_option_is_reported_without_silent_replacement(): void
    {
        $pack = $this->pack();
        $preview = $this->preview($pack, "السؤال,الخيار 1,الخيار 2,الخيار 3,الإجابة الصحيحة,نوع البطاقة\nQ1,A,,C,3,mcq\nQ2,A,,C,2,mcq\nQ3,A,B,,9,mcq\n");
        $report = $this->confirm($pack, $preview)->getData(true)['data']['report'];
        $this->assertSame(1, $report['success_count']);
        $this->assertSame(2, $report['failed_count']);
        $this->assertSame(['A', 'C'], $pack->items()->firstOrFail()->options);
        $this->assertSame(1, $pack->items()->firstOrFail()->correct_option);
    }

    public function test_personal_items_can_be_created_edited_and_deleted_without_losing_color_or_order(): void
    {
        $pack = $this->pack();
        $controller = app(FlashcardController::class);
        $data = ['item_type' => 'mcq', 'front_content' => 'Q', 'options' => ['A', 'B'],
            'correct_option' => 1, 'priority' => 'normal', 'item_color' => '#123456'];
        $created = $controller->storeItem($this->request($data), $pack->id)->getData(true)['data']['item'];
        $this->assertSame(1, $created['correct_option']);
        unset($data['item_color']);
        $data['front_content'] = 'Updated';
        $data['options'] = ['C', 'D', 'E'];
        $data['correct_option'] = 2;
        $controller->updateItem($this->request($data), $created['id']);
        $item = FlashcardItem::findOrFail($created['id']);
        $this->assertSame('Updated', $item->front_content);
        $this->assertSame(['C', 'D', 'E'], $item->options);
        $this->assertSame('#123456', $item->item_color);
        $this->assertSame(0, $item->sort_order);
        $this->assertSame(403, $controller->destroyItem($this->request(user: 2), $item->id)->getStatusCode());
        $controller->destroyItem($this->request(), $item->id);
        $this->assertNull($item->fresh());
    }

    public function test_assigned_items_remain_protected_and_permissions_match_details(): void
    {
        $source = $this->pack(['user_id' => 2]);
        $assigned = $this->pack(['source_pack_id' => $source->id]);
        $controller = app(FlashcardController::class);
        $details = $controller->show($this->request(), $assigned->id)->getData(true)['data'];
        $this->assertFalse($details['can_manage_items']);
        $this->assertTrue($controller->show($this->request(), $this->pack()->id)
            ->getData(true)['data']['can_manage_items']);
        $this->expectException(HttpException::class);
        $controller->storeItem($this->request(['front_content' => 'Forbidden', 'priority' => 'normal']), $assigned->id);
    }

    public function test_out_of_range_answer_is_rejected(): void
    {
        $this->expectException(ValidationException::class);
        app(FlashcardController::class)->storeItem($this->request([
            'front_content' => 'Q', 'priority' => 'normal', 'item_type' => 'mcq',
            'options' => ['A', 'B'], 'correct_option' => 2,
        ]), $this->pack()->id);
    }

    public function test_duplicate_handling_applies_within_the_file_and_updates_existing_mcq_options(): void
    {
        $pack = $this->pack();
        $csv = "السؤال,الخيار 1,الخيار 2,الإجابة الصحيحة,نوع البطاقة\nQ,A,B,1,mcq\nQ,C,D,2,mcq\n";
        $preview = $this->preview($pack, $csv);
        $report = $this->confirm($pack, $preview)->getData(true)['data']['report'];
        $this->assertSame(1, $report['success_count']);
        $this->assertSame(1, $report['ignored_count']);
        $preview = $this->preview($pack, $csv);
        $response = $this->confirm($pack, $preview, ['duplicate_strategy' => 'update']);
        $this->assertSame(200, $response->getStatusCode(), $response->getContent());
        $this->assertSame(1, $pack->items()->count());
        $this->assertSame(['C', 'D'], $pack->items()->firstOrFail()->options);
        $this->assertSame(1, $pack->items()->firstOrFail()->correct_option);
    }
}
