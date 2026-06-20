<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('survey_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('class_id')->nullable()->constrained('class_groups')->nullOnDelete();
            $table->unsignedSmallInteger('total_score')->default(0);   // 50–200
            $table->float('logit_score')->default(0);                  // overall logit
            $table->timestamp('completed_at')->nullable();

            // Raw scores per domain (10 items × 4 max = 40 each)
            $table->unsignedTinyInteger('score_m')->default(0);
            $table->unsignedTinyInteger('score_r')->default(0);
            $table->unsignedTinyInteger('score_p')->default(0);
            $table->unsignedTinyInteger('score_t')->default(0);
            $table->unsignedTinyInteger('score_o')->default(0);

            // Logit scores per domain
            $table->float('logit_m')->default(0);
            $table->float('logit_r')->default(0);
            $table->float('logit_p')->default(0);
            $table->float('logit_t')->default(0);
            $table->float('logit_o')->default(0);

            $table->timestamps();

            $table->index(['student_id', 'completed_at']);
            $table->index('class_id');
        });

        Schema::create('survey_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('submission_id')->constrained('survey_submissions')->onDelete('cascade');
            $table->foreignId('item_id')->constrained('survey_items')->onDelete('cascade');
            $table->unsignedTinyInteger('response_value');  // 1–4
            $table->timestamps();

            $table->unique(['submission_id', 'item_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('survey_responses');
        Schema::dropIfExists('survey_submissions');
    }
};
