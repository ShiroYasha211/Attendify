<?php

namespace App\Http\Controllers\Api\Student;

use App\Models\Quiz\Quiz;
use App\Models\Quiz\QuizModel;
use App\Models\Quiz\QuizAttempt;
use App\Models\Quiz\QuizAnswer;
use App\Models\Quiz\QuizOption;
use App\Models\Academic\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class QuizController extends StudentApiController
{
    /**
     * List available quizzes for this student.
     */
    public function index()
    {
        $student = Auth::user();

        // Get subjects matching this student's major/level
        $subjectIds = Subject::where('major_id', $student->major_id)
            ->where('level_id', $student->level_id)
            ->pluck('id');

        // 1. Doctor quizzes for student's subjects
        $doctorQuizzes = Quiz::where('creator_type', 'doctor')
            ->whereIn('subject_id', $subjectIds)
            ->where(function ($q) {
                $q->where('status', 'published')
                  ->orWhere(function ($sub) {
                      $sub->where('status', 'scheduled')
                          ->where(function ($inner) {
                              $inner->where('notify_students', true)
                                    ->orWhere('scheduled_at', '<=', now());
                          });
                  });
            })
            ->where(function ($q) {
                $q->whereNull('closes_at')->orWhere('closes_at', '>', now());
            })
            ->with(['creator:id,name', 'subject:id,name'])
            ->withCount('models')
            ->latest()
            ->get();

        // 2. Competition quizzes targeting this student
        $competitions = Quiz::competitions()
            ->where(function ($q) {
                $q->where('status', 'published')
                  ->orWhere(function ($sub) {
                      $sub->where('status', 'scheduled')
                          ->where(function ($inner) {
                              $inner->where('notify_students', true)
                                    ->orWhere('scheduled_at', '<=', now());
                          });
                  });
            })
            ->whereHas('targets', function ($q) use ($student) {
                $q->where(function ($sub) use ($student) {
                    $sub->whereNull('major_id')->orWhere('major_id', $student->major_id);
                })->where(function ($sub) use ($student) {
                    $sub->whereNull('level_id')->orWhere('level_id', $student->level_id);
                });
            })
            ->where(function ($q) {
                $q->whereNull('closes_at')->orWhere('closes_at', '>', now());
            })
            ->with(['creator:id,name'])
            ->latest()
            ->get();

        // 3. My recent attempts
        $myAttempts = QuizAttempt::where('student_id', $student->id)
            ->with(['quiz.subject', 'quiz.creator:id,name'])
            ->latest()
            ->take(10)
            ->get();

        return $this->success([
            'doctor_quizzes' => $doctorQuizzes,
            'competitions'   => $competitions,
            'my_attempts'    => $myAttempts,
        ], 'تم جلب الكويزات بنجاح');
    }

    /**
     * Start/resume a quiz attempt.
     */
    public function take(Request $request, Quiz $quiz)
    {
        $student = Auth::user();

        // Check access
        if (!$quiz->isAccessibleBy($student)) {
            return $this->error('لا يمكنك الوصول لهذا الكويز', 403);
        }

        // Check if already attempted and not in progress
        $existingAttempt = $quiz->attemptBy($student);
        if ($existingAttempt && $existingAttempt->status !== 'in_progress') {
            return $this->success([
                'already_submitted' => true,
                'attempt_id'        => $existingAttempt->id,
            ], 'لقد قمت بتسليم هذا الكويز مسبقاً');
        }

        // Pick or retrieve quiz model
        if ($existingAttempt) {
            $attempt = $existingAttempt;
            $quizModel = $attempt->quizModel;
        } else {
            $quizModel = $quiz->models()->inRandomOrder()->first();

            if (!$quizModel) {
                return $this->error('لا توجد نماذج متاحة لهذا الكويز', 404);
            }

            // Handle access code
            if ($quizModel->access_code && $request->get('access_code') !== $quizModel->access_code) {
                return $this->error('رمز الدخول غير صحيح', 403, ['requires_access_code' => true]);
            }

            // Create attempt
            $attempt = QuizAttempt::create([
                'quiz_id'       => $quiz->id,
                'quiz_model_id' => $quizModel->id,
                'student_id'    => $student->id,
                'started_at'    => now(),
                'status'        => 'in_progress',
            ]);
        }

        // Load nested questions & options
        $questions = $quizModel->questions()
            ->with(['options' => function ($q) use ($quiz) {
                if ($quiz->shuffle_options) {
                    $q->inRandomOrder();
                } else {
                    $q->orderBy('order');
                }
            }])
            ->when($quiz->shuffle_questions, fn($q) => $q->inRandomOrder())
            ->when(!$quiz->shuffle_questions, fn($q) => $q->orderBy('order'))
            ->get();

        // Get existing answers
        $existingAnswers = $attempt->answers()->pluck('selected_option_id', 'question_id');

        return $this->success([
            'quiz'             => $quiz->load(['creator:id,name', 'subject:id,name']),
            'attempt'          => $attempt,
            'questions'        => $questions,
            'existing_answers' => $existingAnswers,
        ], 'بدأ المحاولة');
    }

    /**
     * Submit quiz answers.
     */
    public function submit(Request $request, QuizAttempt $attempt)
    {
        $student = Auth::user();

        if ($attempt->student_id !== $student->id) {
            return $this->error('غير مصرح لك بالوصول لهذه المحاولة', 403);
        }

        if ($attempt->status !== 'in_progress') {
            return $this->error('تم تسليم هذا الكويز مسبقاً', 400);
        }

        $request->validate([
            'answers'   => 'required|array',
            'answers.*' => 'required|integer|exists:quiz_options,id',
        ]);

        DB::beginTransaction();
        try {
            foreach ($request->answers as $questionId => $optionId) {
                $option = QuizOption::findOrFail($optionId);

                QuizAnswer::updateOrCreate(
                    ['attempt_id' => $attempt->id, 'question_id' => $questionId],
                    [
                        'selected_option_id' => $optionId,
                        'is_correct'         => $option->is_correct,
                    ]
                );
            }

            $attempt->update([
                'submitted_at' => now(),
                'status'       => 'submitted',
            ]);

            $attempt->calculateScore();

            DB::commit();

            return $this->success([
                'attempt_id' => $attempt->id,
            ], 'تم تسليم الكويز بنجاح');

        } catch (\Exception $e) {
            DB::rollBack();
            return $this->error('حدث خطأ أثناء التسليم: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Show quiz result.
     */
    public function result(QuizAttempt $attempt)
    {
        $student = Auth::user();

        if ($attempt->student_id !== $student->id) {
            return $this->error('غير مصرح لك بالوصول لهذه النتيجة', 403);
        }

        $quiz = $attempt->quiz;

        // Check if results are hidden
        if ($quiz->results_visibility === 'hidden') {
            return $this->success([
                'quiz'    => $quiz->only(['id', 'title']),
                'attempt' => $attempt->only(['id', 'score', 'submitted_at', 'status']),
                'message' => 'نتائج هذا الكويز مخفية حالياً من قبل المدرس.',
            ], 'تم إخفاء النتائج');
        }

        $attempt->load(['answers.question.options', 'answers.selectedOption', 'quizModel']);

        return $this->success([
            'quiz'    => $quiz->load(['creator:id,name', 'subject:id,name']),
            'attempt' => $attempt,
        ], 'تم جلب النتيجة بنجاح');
    }
}
