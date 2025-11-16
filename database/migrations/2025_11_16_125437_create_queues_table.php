<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('queues', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained()->onDelete('cascade');
            $table->foreignId('service_id')->constrained()->onDelete('cascade');
            $table->foreignId('counter_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('staff_id')->nullable()->constrained('staff')->onDelete('set null');
            $table->string('queue_number'); // e.g., "A-001"
            $table->integer('number_in_queue'); // Just the numeric part
            $table->enum('status', ['waiting', 'called', 'serving', 'completed', 'cancelled', 'no_show'])->default('waiting');
            $table->enum('priority', ['normal', 'senior', 'pwd', 'pregnant', 'emergency'])->default('normal');
            $table->timestamp('called_at')->nullable();
            $table->timestamp('serving_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('queues');
    }
};
