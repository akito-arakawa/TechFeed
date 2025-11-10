<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('articles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('source_id');
            $table->foreign('source_id')->references('id')->on('sources')->onDelete('cascade');
            $table->string('source_item_id', 255)->nullable();
            $table->string('url', 255)->unique();
            $table->string('title', 255);
            $table->string('author_name', 100)->nullable();
            $table->string('thumbnail_url', 255)->nullable();
            $table->unsignedInteger('source_like_count')->default(0);
            $table->timestamp('pubished_at')->nullable();
            $table->timestamp('feched_at');
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
        Schema::dropIfExists('articles');
    }
};
