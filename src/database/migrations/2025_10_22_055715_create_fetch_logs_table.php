<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('fetch_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_id')->constrained('fetch_jobs')->cascadeOnDelete();
            $table->unsignedInteger('new_count')->default(0);
            $table->unsignedInteger('update_count')->default(0);
            $table->unsignedInteger('skip_count')->default(0);
            $table->text('error_message')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ended_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('fetch_logs');
    }
};
