<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up()
    {
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('event');
            $table->string('model_type');
            $table->unsignedBigInteger('model_id');
            $table->string('parent_model_type')->nullable();
            $table->unsignedBigInteger('parent_model_id')->nullable();
            $table->foreignId('activity_log_id')->nullable()->constrained('activity_logs')->onDelete('set null');
            $table->json('old_data')->nullable();
            $table->json('new_data')->nullable();
            $table->json('changes')->nullable();
            $table->json('request_details')->nullable();
            $table->timestamps();

            $table->index(['model_type', 'model_id']);
            $table->index(['parent_model_type', 'parent_model_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('activity_logs');
    }
};
