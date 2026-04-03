<?php

namespace App\Http\Controllers\Api\Doctor;

use App\Models\Academic\Subject;
use App\Models\Quiz\Quiz;
use App\Models\Quiz\QuizAttempt;
use App\Models\Quiz\QuizModel;
use App\Models\Quiz\QuizOption;
use App\Models\Quiz\QuizQuestion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class QuizController extends DoctorApiController
{
    protected function ownedQuizOrFail(Quiz $quiz): Quiz
    {
        abort_unless($quiz->created_by === Auth::id() && $quiz->creator_type === 'doctor', 403);

        return $quiz;
    }

    public function index(Request $request)
    {
        $query = Quiz::forDoctor(Auth::id())
            ->with(['subject:id,name', 'models:id,quiz_id,name'])
            ->withCount('attempts');

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        $quizzes = $query->latest()->paginate($request->integer('per_page', 12));

        return $this->success($quizzes);
    }

    public function store(Request $request)
    {
        $doctor = $request->user();
        $validated = $request->validate([
            'subject_id' => 'required|exists:subjects,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'time_limit_minutes' => 'nullable|integer|min:1|max:300',
            'shuffle_questions' => 'nullable|boolean',
            'shuffle_options' => 'nullable|boolean',
            'show_correct_answers' => 'nullable|boolean',
            'show_correction_notes' => 'nullable|boolean',
            'notify_students' => 'nullable|boolean',
            'show_countdown' => 'nullable|boolean',
            'results_visibility' => 'required|in:hidden,individual,public',
            'scheduled_at' => 'nullable|date',
            'closes_at' => 'nullable|date|after:scheduled_at',
            'models' => 'required|array|min:1',
            'models.*.name' => 'required|string|max:100',
            'models.*.questions' => 'required|array|min:1',
            'models.*.questions.*.question_text' => 'required|string',
            'models.*.questions.*.question_type' => 'required|in:multiple_choice,true_false',
            'models.*.questions.*.score' => 'nullable|numeric|min:0',
            'models.*.questions.*.correction_note' => 'nullable|string',
            'models.*.questions.*.info_source' => 'nullable|string',
            'models.*.questions.*.options' => 'required|array|min:2',
            'models.*.questions.*.options.*.option_text' => 'required|string',
            'models.*.questions.*.options.*.is_correct' => 'nullable|boolean',
        ]);

        Subject::where('id', $validated['subject_id'])
            ->where('doctor_id', $doctor->id)
            ->firstOrFail();

        DB::beginTransaction();

        try {
            $status = 'draft';
            if (!empty($validated['scheduled_at'])) {
                $status = now()->gte($validated['scheduled_at']) ? 'published' : 'scheduled';
            }

            $quiz = Quiz::create([
                'created_by' => $doctor->id,
                'creator_type' => 'doctor',
                'subject_id' => $validated['subject_id'],
                'title' => $validated['title'],
                'description' => $validated['description'] ?? null,
                'time_limit_minutes' => $validated['time_limit_minutes'] ?? null,
                'shuffle_questions' => $request->boolean('shuffle_questions'),
                'shuffle_options' => $request->boolean('shuffle_options'),
                'show_correct_answers' => $request->boolean('show_correct_answers'),
                'show_correction_notes' => $request->boolean('show_correction_notes', true),
                'results_visibility' => $validated['results_visibility'],
                'scheduled_at' => $validated['scheduled_at'] ?? null,
                'closes_at' => $validated['closes_at'] ?? null,
                'status' => $status,
                'notify_students' => $request->boolean('notify_students'),
                'show_countdown' => $request->boolean('show_countdown'),
            ]);

            $this->syncModels($quiz, $validated['models']);

            DB::commit();

            return $this->success($quiz->load('subject:id,name', 'models.questions.options'), 'تم إنشاء الكويز بنجاح.', 201);
        } catch (\Throwable $exception) {
            DB::rollBack();
            return $this->error('حدث خطأ أثناء إنشاء الكويز: ' . $exception->getMessage(), 500);
        }
    }

    public function show(Quiz $quiz)
    {
        $quiz = $this->ownedQuizOrFail($quiz);
        $quiz->load(['subject:id,name', 'models.questions.options', 'attempts.student:id,name,student_number']);

        $gradedAttempts = $quiz->attempts->where('status', 'graded');

        return $this->success([
            'quiz' => $quiz,
            'stats' => [
                'total_attempts' => $quiz->attempts->count(),
                'avg_score' => round($gradedAttempts->avg('percentage') ?? 0, 1),
                'highest_score' => $gradedAttempts->max('percentage') ?? 0,
                'lowest_score' => $gradedAttempts->min('percentage') ?? 0,
            ],
            'can_edit_content' => $quiz->attempts->count() === 0,
        ]);
    }

    public function update(Request $request, Quiz $quiz)
    {
        $quiz = $this->ownedQuizOrFail($quiz);
        $canEditContent = $quiz->attempts()->count() === 0;

        $rules = [
            'subject_id' => 'required|exists:subjects,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'time_limit_minutes' => 'nullable|integer|min:1|max:300',
            'shuffle_questions' => 'nullable|boolean',
            'shuffle_options' => 'nullable|boolean',
            'show_correct_answers' => 'nullable|boolean',
            'show_correction_notes' => 'nullable|boolean',
            'notify_students' => 'nullable|boolean',
            'show_countdown' => 'nullable|boolean',
            'results_visibility' => 'required|in:hidden,individual,public',
            'scheduled_at' => 'nullable|date',
            'closes_at' => 'nullable|date|after:scheduled_at',
        ];

        if ($canEditContent) {
            $rules += [
                'models' => 'required|array|min:1',
                'models.*.name' => 'required|string|max:100',
                'models.*.questions' => 'required|array|min:1',
                'models.*.questions.*.question_text' => 'required|string',
                'models.*.questions.*.question_type' => 'required|in:multiple_choice,true_false',
                'models.*.questions.*.score' => 'nullable|numeric|min:0',
                'models.*.questions.*.correction_note' => 'nullable|string',
                'models.*.questions.*.info_source' => 'nullable|string',
                'models.*.questions.*.options' => 'required|array|min:2',
                'models.*.questions.*.options.*.option_text' => 'required|string',
                'models.*.questions.*.options.*.is_correct' => 'nullable|boolean',
            ];
        }

        $validated = $request->validate($rules);

        Subject::where('id', $validated['subject_id'])
            ->where('doctor_id', Auth::id())
            ->firstOrFail();

        DB::beginTransaction();

        try {
            $status = $quiz->status;
            if (in_array($status, ['draft', 'scheduled'], true) && !empty($validated['scheduled_at'])) {
                $status = now()->gte($validated['scheduled_at']) ? 'published' : 'scheduled';
            }

            $quiz->update([
                'subject_id' => $validated['subject_id'],
                'title' => $validated['title'],
                'description' => $validated['description'] ?? null,
                'time_limit_minutes' => $validated['time_limit_minutes'] ?? null,
                'shuffle_questions' => $request->boolean('shuffle_questions'),
                'shuffle_options' => $request->boolean('shuffle_options'),
                'show_correct_answers' => $request->boolean('show_correct_answers'),
                'show_correction_notes' => $request->boolean('show_correction_notes', true),
                'notify_students' => $request->boolean('notify_students'),
                'show_countdown' => $request->boolean('show_countdown'),
                'results_visibility' => $validated['results_visibility'],
                'scheduled_at' => $validated['scheduled_at'] ?? null,
                'closes_at' => $validated['closes_at'] ?? null,
                'status' => $status,
            ]);

            if ($canEditContent && isset($validated['models'])) {
                $quiz->models()->delete();
                $this->syncModels($quiz, $validated['models']);
            }

            DB::commit();

            return $this->success($quiz->fresh()->load('subject:id,name', 'models.questions.options'), 'تم تحديث الكويز بنجاح.');
        } catch (\Throwable $exception) {
            DB::rollBack();
            return $this->error('حدث خطأ أثناء تعديل الكويز: ' . $exception->getMessage(), 500);
        }
    }

    public function results(Quiz $quiz)
    {
        $quiz = $this->ownedQuizOrFail($quiz);

        $attempts = QuizAttempt::where('quiz_id', $quiz->id)
            ->with(['student:id,name,student_number', 'quizModel:id,name'])
            ->orderByDesc('score')
            ->get();

        return $this->success([
            'quiz' => $quiz->only(['id', 'title', 'status', 'results_visibility']),
            'attempts' => $attempts,
        ]);
    }

    public function publish(Quiz $quiz)
    {
        $quiz = $this->ownedQuizOrFail($quiz);
        $quiz->update(['status' => 'published']);

        return $this->success($quiz->only(['id', 'status']), 'تم نشر الكويز بنجاح.');
    }

    public function close(Quiz $quiz)
    {
        $quiz = $this->ownedQuizOrFail($quiz);
        $quiz->update(['status' => 'closed']);

        return $this->success($quiz->only(['id', 'status']), 'تم إغلاق الكويز.');
    }

    public function shareResults(Request $request, Quiz $quiz)
    {
        $quiz = $this->ownedQuizOrFail($quiz);
        $validated = $request->validate([
            'visibility' => 'required|in:hidden,individual,public',
        ]);

        $quiz->update(['results_visibility' => $validated['visibility']]);

        return $this->success($quiz->only(['id', 'results_visibility']), 'تم تحديث إعدادات مشاركة النتائج.');
    }

    public function destroy(Quiz $quiz)
    {
        $quiz = $this->ownedQuizOrFail($quiz);
        $quiz->delete();

        return $this->success(null, 'تم حذف الكويز بنجاح.');
    }

    protected function syncModels(Quiz $quiz, array $models): void
    {
        foreach ($models as $modelData) {
            $quizModel = QuizModel::create([
                'quiz_id' => $quiz->id,
                'name' => $modelData['name'],
                'access_code' => strtoupper(Str::random(6)),
            ]);

            foreach ($modelData['questions'] as $qIndex => $questionData) {
                $question = QuizQuestion::create([
                    'quiz_model_id' => $quizModel->id,
                    'question_text' => $questionData['question_text'],
                    'question_type' => $questionData['question_type'],
                    'score' => $questionData['score'] ?? 1,
                    'correction_note' => $questionData['correction_note'] ?? null,
                    'info_source' => $questionData['info_source'] ?? null,
                    'order' => $qIndex + 1,
                ]);

                foreach ($questionData['options'] as $oIndex => $optionData) {
                    QuizOption::create([
                        'question_id' => $question->id,
                        'option_text' => $optionData['option_text'],
                        'is_correct' => !empty($optionData['is_correct']),
                        'order' => $oIndex + 1,
                    ]);
                }
            }
        }
    }
}
