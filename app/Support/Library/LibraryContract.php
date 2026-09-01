<?php

namespace App\Support\Library;

final class LibraryContract
{
    public const TYPE_SHARED = 'shared';

    public const TYPE_COURSE = 'course';

    public const TYPES = [
        self::TYPE_SHARED,
        self::TYPE_COURSE,
    ];

    public const DEFAULT_SECTIONS = [
        'lectures' => [
            'title' => 'المحاضرات',
            'icon' => 'video_library',
            'aliases' => ['lectures', 'lecture'],
        ],
        'summaries' => [
            'title' => 'الملخصات',
            'icon' => 'summarize',
            'aliases' => ['summaries', 'summary'],
        ],
        'quizzes' => [
            'title' => 'الكويزات',
            'icon' => 'quiz',
            'aliases' => ['quizzes', 'quiz'],
        ],
        'exams' => [
            'title' => 'نماذج الاختبارات',
            'icon' => 'assignment',
            'aliases' => ['exams', 'exam'],
        ],
        'references' => [
            'title' => 'المراجع',
            'icon' => 'menu_book',
            'aliases' => ['references', 'reference', 'book'],
        ],
        'other' => [
            'title' => 'أخرى',
            'icon' => 'folder',
            'aliases' => ['other'],
        ],
    ];

    public const CAPABILITIES = [
        'can_upload',
        'can_create_sections',
        'can_update_sections',
        'can_delete_sections',
        'can_reorder_sections',
        'can_manage_resources',
    ];

    private function __construct() {}
}
