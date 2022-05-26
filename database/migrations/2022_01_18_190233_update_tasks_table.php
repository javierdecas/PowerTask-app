<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateTasksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tasks', function(Blueprint $table) {
            $table->foreignId('student_id')->constrained('students');
            $table->foreignId('subject_id')->nullable()->constrained('subjects');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tasks', function(Blueprint $table) {
            $table->dropForeign(['student_id']);
            $table->dropForeign(['subject_id']);

            $table->dropColumn('student_id');
            $table->dropColumn('subject_id');
        });
    }
}
