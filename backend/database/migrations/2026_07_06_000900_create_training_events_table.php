<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('training_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('created_by_admin_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('tag')->nullable();
            $table->string('event_type')->default('upcoming')->index();
            $table->string('date_label')->nullable();
            $table->date('starts_at')->nullable()->index();
            $table->string('time_label')->nullable();
            $table->string('location')->nullable();
            $table->string('venue')->nullable();
            $table->text('description')->nullable();
            $table->string('register_url')->nullable();
            $table->json('image_paths')->nullable();
            $table->string('status')->default('draft')->index();
            $table->boolean('is_featured')->default(false)->index();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('training_events');
    }
};
