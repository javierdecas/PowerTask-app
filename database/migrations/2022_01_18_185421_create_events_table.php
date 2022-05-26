<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEventsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('type', ['vacation', 'exam', 'personal', 'task']);
            $table->boolean('all_day');
            $table->text('notes')->nullable();
            // $table->date('date_start');
            // $table->date('date_end');
            $table->double('timestamp_start');
            $table->double('timestamp_end');
            $table->timestamps();
            $table->foreignId('subject_id')->nullable()->constrained('subjects');
            $table->foreignId('student_id')->constrained('students');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('events');
    }
}
