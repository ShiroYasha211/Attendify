<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('course_resources', function (Blueprint $table) {
            $table->index(
                ['subject_id', 'category', 'visibility', 'created_at'],
                'course_resources_library_browse_index'
            );
        });

        Schema::create('library_spaces', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
            $table->string('type', 24);
            $table->string('name')->nullable();
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['subject_id', 'type'], 'library_spaces_subject_type_unique');
            $table->index(['type', 'is_active']);
        });

        Schema::create('library_nodes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('library_space_id')->constrained()->cascadeOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('library_nodes')->cascadeOnDelete();
            $table->string('title');
            $table->string('slug')->nullable();
            $table->string('node_type', 24)->default('section');
            $table->string('icon', 64)->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_system')->default(false);
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['library_space_id', 'parent_id', 'is_active', 'sort_order'], 'library_nodes_navigation_index');
            $table->unique(['library_space_id', 'parent_id', 'slug'], 'library_nodes_sibling_slug_unique');
        });

        Schema::create('library_node_resources', function (Blueprint $table) {
            $table->id();
            $table->foreignId('library_node_id')->constrained()->cascadeOnDelete();
            $table->foreignId('course_resource_id')->constrained('course_resources')->cascadeOnDelete();
            $table->unsignedInteger('sort_order')->default(0);
            $table->foreignId('attached_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['library_node_id', 'course_resource_id'], 'library_node_resources_unique');
            $table->index(['library_node_id', 'sort_order']);
        });

        Schema::create('library_contributors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('library_space_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->boolean('can_upload')->default(false);
            $table->boolean('can_create_sections')->default(false);
            $table->boolean('can_update_sections')->default(false);
            $table->boolean('can_delete_sections')->default(false);
            $table->boolean('can_reorder_sections')->default(false);
            $table->boolean('can_manage_resources')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamp('expires_at')->nullable();
            $table->foreignId('granted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['library_space_id', 'user_id'], 'library_contributors_space_user_unique');
            $table->index(['user_id', 'is_active', 'expires_at']);
        });

        Schema::create('library_contributor_scopes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('library_contributor_id')->constrained()->cascadeOnDelete();
            $table->foreignId('library_node_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['library_contributor_id', 'library_node_id'], 'library_contributor_scopes_unique');
        });

        Schema::create('library_activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('library_space_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('library_node_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('course_resource_id')->nullable()->constrained('course_resources')->nullOnDelete();
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action', 64);
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['library_space_id', 'created_at']);
            $table->index(['actor_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('library_activity_logs');
        Schema::dropIfExists('library_contributor_scopes');
        Schema::dropIfExists('library_contributors');
        Schema::dropIfExists('library_node_resources');
        Schema::dropIfExists('library_nodes');
        Schema::dropIfExists('library_spaces');

        Schema::table('course_resources', function (Blueprint $table) {
            $table->dropIndex('course_resources_library_browse_index');
        });
    }
};
