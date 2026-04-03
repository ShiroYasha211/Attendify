<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\Quiz\Quiz;
use App\Models\Quiz\QuizModel;
use App\Models\Quiz\QuizQuestion;
use App\Models\Quiz\QuizOption;
use App\Models\Quiz\QuizAttempt;
use App\Models\Academic\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class QuizController extends Controller
{
    /**
     * List all quizzes created by this doctor.
     */
    public function index(Request $request)
    {
        $doctor = Auth::user();
        $status = $request->get('status', 'all');

        $query = Quiz::forDoctor($doctor->id)
            ->with(['subject', 'models'])
            ->withCount('attempts');

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        $quizzes = $query->latest()->paginate(12);

        return view('doctor.quizzes.index', compact('quizzes', 'status'));
    }

    /**
     * Show quiz creation form.
     */
    public function create()
    {
        $doctor = Auth::user();
        $subjects = Subject::where('doctor_id', $doctor->id)->with('level')->get();

        return view('doctor.quizzes.create', compact('subjects'));
    }

    /**
     * Store a new quiz with models, questions, and options.
     */
    public function store(Request $request)
    {
        $doctor = Auth::user();

        $validated = $request->validate([
            'subject_id'           => 'required|exists:subjects,id',
            'title'                => 'required|string|max:255',
            'description'          => 'nullable|string',
            'time_limit_minutes'   => 'nullable|integer|min:1|max:300',
            'shuffle_questions'    => 'nullable|boolean',
            'shuffle_options'      => 'nullable|boolean',
            'show_correct_answers' => 'nullable|boolean',
            'show_correction_notes'=> 'nullable|boolean',
            'results_visibility'   => 'required|in:hidden,individual,public',
            'scheduled_at'         => 'nullable|date',
            'closes_at'            => 'nullable|date|after:scheduled_at',
            'models'               => 'required|array|min:1',
            'models.*.name'        => 'required|string|max:100',
            'models.*.questions'   => 'required|array|min:1',
            'models.*.questions.*.question_text'     => 'required|string',
            'models.*.questions.*.question_type'     => 'required|in:multiple_choice,true_false',
            'models.*.questions.*.score'             => 'nullable|numeric|min:0',
            'models.*.questions.*.correction_note'   => 'nullable|string',
            'models.*.questions.*.info_source'       => 'nullable|string',
            'models.*.questions.*.options'           => 'required|array|min:2',
            'models.*.questions.*.options.*.option_text' => 'required|string',
            'models.*.questions.*.options.*.is_correct'  => 'nullable',
        ]);

        // Verify subject ownership
        Subject::where('id', $validated['subject_id'])
            ->where('doctor_id', $doctor->id)
            ->firstOrFail();

        DB::beginTransaction();

        try {
            // Determine status
            $status = 'draft';
            if (!empty($validated['scheduled_at'])) {
                $status = now()->gte($validated['scheduled_at']) ? 'published' : 'scheduled';
            }

            // Create quiz
            $quiz = Quiz::create([
                'created_by'            => $doctor->id,
                'creator_type'          => 'doctor',
                'subject_id'            => $validated['subject_id'],
                'title'                 => $validated['title'],
                'description'           => $validated['description'] ?? null,
                'time_limit_minutes'    => $validated['time_limit_minutes'] ?? null,
                'shuffle_questions'     => $request->boolean('shuffle_questions'),
                'shuffle_options'       => $request->boolean('shuffle_options'),
                'show_correct_answers'  => $request->boolean('show_correct_answers'),
                'show_correction_notes' => $request->boolean('show_correction_notes', true),
                'results_visibility'    => $validated['results_visibility'],
                'scheduled_at'          => $validated['scheduled_at'] ?? null,
                'closes_at'             => $validated['closes_at'] ?? null,
                'status'                => $status,
                'notify_students'       => $request->boolean('notify_students'),
                'show_countdown'        => $request->boolean('show_countdown'),
            ]);

            // Create models with questions and options
            foreach ($validated['models'] as $modelData) {
                $quizModel = QuizModel::create([
                    'quiz_id'     => $quiz->id,
                    'name'        => $modelData['name'],
                    'access_code' => strtoupper(Str::random(6)),
                ]);

                foreach ($modelData['questions'] as $qIndex => $questionData) {
                    $question = QuizQuestion::create([
                        'quiz_model_id'   => $quizModel->id,
                        'question_text'   => $questionData['question_text'],
                        'question_type'   => $questionData['question_type'],
                        'score'           => $questionData['score'] ?? 1,
                        'correction_note' => $questionData['correction_note'] ?? null,
                        'info_source'     => $questionData['info_source'] ?? null,
                        'order'           => $qIndex + 1,
                    ]);

                    foreach ($questionData['options'] as $oIndex => $optionData) {
                        QuizOption::create([
                            'question_id' => $question->id,
                            'option_text'  => $optionData['option_text'],
                            'is_correct'   => !empty($optionData['is_correct']),
                            'order'        => $oIndex + 1,
                        ]);
                    }
                }
            }

            DB::commit();

            return redirect()->route('doctor.quizzes.show', $quiz)
                ->with('success', 'تم إنشاء الكويز بنجاح.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'حدث خطأ أثناء إنشاء الكويز: ' . $e->getMessage());
        }
    }

    /**
     * Show quiz details and stats.
     */
    public function show(Quiz $quiz)
    {
        $doctor = Auth::user();

        if ($quiz->created_by !== $doctor->id) {
            abort(403);
        }

        $quiz->load(['subject', 'models.questions.options', 'attempts.student']);

        $stats = [
            'total_attempts' => $quiz->attempts_count,
            'avg_score'      => $quiz->attempts->where('status', 'graded')->avg('percentage') ?? 0,
            'highest_score'  => $quiz->attempts->where('status', 'graded')->max('percentage') ?? 0,
            'lowest_score'   => $quiz->attempts->where('status', 'graded')->min('percentage') ?? 0,
        ];

        return view('doctor.quizzes.show', compact('quiz', 'stats'));
    }

    /**
     * Edit quiz form.
     */
    public function edit(Quiz $quiz)
    {
        $doctor = Auth::user();

        if ($quiz->created_by !== $doctor->id) {
            abort(403);
        }

        $quiz->load(['models.questions.options']);
        $subjects = Subject::where('doctor_id', $doctor->id)->with('level')->get();
        $canEditContent = $quiz->attempts()->count() === 0;

        // Prepare initial data for the frontend builder
        $initialModels = $quiz->models->map(function($model) {
            return [
                'name' => $model->name,
                'questions' => $model->questions->map(function($q) {
                    return [
                        'text' => $q->question_text,
                        'score' => $q->score,
                        'correction_note' => $q->correction_note,
                        'info_source' => $q->info_source,
                        'options' => $q->options->map(function($o) {
                            return [
                                'text' => $o->option_text,
                                'is_correct' => (bool)$o->is_correct
                            ];
                        })
                    ];
                })
            ];
        });

        return view('doctor.quizzes.edit', compact('quiz', 'subjects', 'canEditContent', 'initialModels'));
    }

    /**
     * Update quiz settings and content.
     */
    public function update(Request $request, Quiz $quiz)
    {
        $doctor = Auth::user();

        if ($quiz->created_by !== $doctor->id) {
            abort(403);
        }

        $canEditContent = $quiz->attempts()->count() === 0;

        $rules = [
            'subject_id'           => 'required|exists:subjects,id',
            'title'                => 'required|string|max:255',
            'description'          => 'nullable|string',
            'time_limit_minutes'   => 'nullable|integer|min:1|max:300',
            'shuffle_questions'    => 'nullable|boolean',
            'shuffle_options'      => 'nullable|boolean',
            'show_correct_answers' => 'nullable|boolean',
            'show_correction_notes'=> 'nullable|boolean',
            'notify_students'      => 'nullable|boolean',
            'show_countdown'       => 'nullable|boolean',
            'results_visibility'   => 'required|in:hidden,individual,public',
            'scheduled_at'         => 'nullable|date',
            'closes_at'            => 'nullable|date|after:scheduled_at',
        ];

        if ($canEditContent) {
            $rules += [
                'models'               => 'required|array|min:1',
                'models.*.name'        => 'required|string|max:100',
                'models.*.questions'   => 'required|array|min:1',
                'models.*.questions.*.question_text'     => 'required|string',
                'models.*.questions.*.question_type'     => 'required|in:multiple_choice,true_false',
                'models.*.questions.*.score'             => 'nullable|numeric|min:0',
                'models.*.questions.*.correction_note'   => 'nullable|string',
                'models.*.questions.*.info_source'       => 'nullable|string',
                'models.*.questions.*.options'           => 'required|array|min:2',
                'models.*.questions.*.options.*.option_text' => 'required|string',
                'models.*.questions.*.options.*.is_correct'  => 'nullable',
            ];
        }

        $validated = $request->validate($rules);

        // Verify subject ownership
        Subject::where('id', $validated['subject_id'])->where('doctor_id', $doctor->id)->firstOrFail();

        DB::beginTransaction();

        try {
            // Determine status
            $status = $quiz->status;
            if ($status === 'draft' || $status === 'scheduled') {
                if (!empty($validated['scheduled_at'])) {
                    $status = now()->gte($validated['scheduled_at']) ? 'published' : 'scheduled';
                }
            }

            // Update quiz basic fields
            $quiz->update([
                'subject_id'            => $validated['subject_id'],
                'title'                 => $validated['title'],
                'description'           => $validated['description'] ?? null,
                'time_limit_minutes'    => $validated['time_limit_minutes'] ?? null,
                'shuffle_questions'     => $request->boolean('shuffle_questions'),
                'shuffle_options'       => $request->boolean('shuffle_options'),
                'show_correct_answers'  => $request->boolean('show_correct_answers'),
                'show_correction_notes' => $request->boolean('show_correction_notes', true),
                'notify_students'       => $request->boolean('notify_students'),
                'show_countdown'        => $request->boolean('show_countdown'),
                'results_visibility'    => $validated['results_visibility'],
                'scheduled_at'          => $validated['scheduled_at'] ?? null,
                'closes_at'             => $validated['closes_at'] ?? null,
                'status'                => $status,
            ]);

            // Full content replacement if allowed (no attempts yet)
            if ($canEditContent && isset($validated['models'])) {
                // Delete existing models (cascading deletes questions/options)
                $quiz->models()->delete();

                // Recreate models with questions and options (reuse logic from store)
                foreach ($validated['models'] as $modelData) {
                    $quizModel = QuizModel::create([
                        'quiz_id'     => $quiz->id,
                        'name'        => $modelData['name'],
                        'access_code' => strtoupper(Str::random(6)),
                    ]);

                    foreach ($modelData['questions'] as $qIndex => $questionData) {
                        $question = QuizQuestion::create([
                            'quiz_model_id'   => $quizModel->id,
                            'question_text'   => $questionData['question_text'],
                            'question_type'   => $questionData['question_type'],
                            'score'           => $questionData['score'] ?? 1,
                            'correction_note' => $questionData['correction_note'] ?? null,
                            'info_source'     => $questionData['info_source'] ?? null,
                            'order'           => $qIndex + 1,
                        ]);

                        foreach ($questionData['options'] as $oIndex => $optionData) {
                            QuizOption::create([
                                'question_id' => $question->id,
                                'option_text'  => $optionData['option_text'],
                                'is_correct'   => !empty($optionData['is_correct']),
                                'order'        => $oIndex + 1,
                            ]);
                        }
                    }
                }
            }

            DB::commit();

            return redirect()->route('doctor.quizzes.show', $quiz)
                ->with('success', 'تم تحديث الكويز بنجاح.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'حدث خطأ أثناء التعديل: ' . $e->getMessage());
        }
    }

    /**
     * Results page for a specific quiz.
     */
    public function results(Quiz $quiz)
    {
        $doctor = Auth::user();

        if ($quiz->created_by !== $doctor->id) {
            abort(403);
        }

        $attempts = QuizAttempt::where('quiz_id', $quiz->id)
            ->with(['student', 'quizModel', 'answers.question'])
            ->orderByDesc('score')
            ->get();

        return view('doctor.quizzes.results', compact('quiz', 'attempts'));
    }

    /**
     * Publish a quiz.
     */
    public function publish(Quiz $quiz)
    {
        $doctor = Auth::user();

        if ($quiz->created_by !== $doctor->id) {
            abort(403);
        }

        $quiz->update(['status' => 'published']);

        return back()->with('success', 'تم نشر الكويز بنجاح.');
    }

    /**
     * Close a quiz.
     */
    public function close(Quiz $quiz)
    {
        $doctor = Auth::user();

        if ($quiz->created_by !== $doctor->id) {
            abort(403);
        }

        $quiz->update(['status' => 'closed']);

        return back()->with('success', 'تم إغلاق الكويز.');
    }

    /**
     * Toggle results visibility.
     */
    public function shareResults(Request $request, Quiz $quiz)
    {
        $doctor = Auth::user();

        if ($quiz->created_by !== $doctor->id) {
            abort(403);
        }

        $visibility = $request->get('visibility', 'individual');
        $quiz->update(['results_visibility' => $visibility]);

        return back()->with('success', 'تم تحديث إعدادات مشاركة النتائج.');
    }

    /**
     * Delete a quiz.
     */
    public function destroy(Quiz $quiz)
    {
        $doctor = Auth::user();

        if ($quiz->created_by !== $doctor->id) {
            abort(403);
        }

        $quiz->delete();

        return redirect()->route('doctor.quizzes.index')
            ->with('success', 'تم حذف الكويز بنجاح.');
    }
}
