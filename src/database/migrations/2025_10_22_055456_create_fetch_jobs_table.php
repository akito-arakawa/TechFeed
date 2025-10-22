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
        Schema::create('fetch_jobs', function (Blueprint $table) {
            $table->id();
            $table->string('target', 50);        
            $table->json('params_json')->nullable();
            $table->string('status', 20)->default('queued');
            $table->unsignedInteger('fetched_count')->default(0);
            $table->text('error_message')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('fetch_jobs');
    }
};
