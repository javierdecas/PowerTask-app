<?php

namespace Database\Seeders;

use App\Models\Student;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Faker\Factory as Faker;

class MainSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $students = Student::all();
        foreach ($students as $student) {
            $subject_ids = array();
            if(!$student->tasks()->get()->isEmpty() && !$student->subjects()->get()->isEmpty()) {
                if($student->periods()->get()->isEmpty()) {
                    // DB::table('periods')->insert([
                    //     'name' => "Primer trimestre",
                    //     'date_start' => "1631704831",
                    //     'date_end' => "1640258431",
                    //     'student_id' => $student->id,
                    // ]);
                    DB::table('periods')->insert([
                        'name' => "Segundo trimestre",
                        'date_start' => "1641813631",
                        'date_end' => "1648466431",
                        'student_id' => $student->id,
                    ]);
                    // DB::table('periods')->insert([
                    //     'name' => "Tercer trimestre",
                    //     'date_start' => "1648552831",
                    //     'date_end' => "1655292031",
                    //     'student_id' => $student->id,
                    // ]);

                    $period_id = $student->periods()->where('name', 'Segundo trimestre')->first()->id;

                    foreach ($student->subjects()->get() as $subject) {
                        DB::table('contains')->insert([
                            'period_id' => $period_id,
                            'subject_id' => $subject->id,
                        ]);
                        array_push($subject_ids, $subject->id);
                    }

                    for ($i=1; $i <= 5; $i++) {
                        $this->createBlock(1646730000, 1646736000, $i, $student->id, $subject_ids[array_rand($subject_ids)], $period_id);
                        $this->createBlock(1646737200, 1646743200, $i, $student->id, $subject_ids[array_rand($subject_ids)], $period_id);
                        $this->createBlock(1646744400, 1646750400, $i, $student->id, $subject_ids[array_rand($subject_ids)], $period_id);
                    }
                }

                if($student->sessions()->get()->isEmpty()) {
                    $tasks = array();
                    foreach ($student->tasks()->get() as $task) array_push($tasks, $task->id);

                    for ($i=1; $i <= 10; $i++) {
                        $quantity = rand(2,4);
                        $duration = rand(600,900);
                        DB::table('sessions')->insert([
                            'quantity' => $quantity,
                            'duration' => $duration,
                            'total_time' => ($quantity*$duration)+rand(0, 60),
                            'task_id' => $tasks[array_rand($tasks)],
                            'student_id' => $student->id,
                        ]);
                    }
                }

                if($student->events()->get()->isEmpty()) {
                    $this->createEvent('Dia del carmen', 'vacation', 1, 'Notas del dia del carmen', 1657929600, 1658015999, NULL, NULL, NULL, $student->id);
                    $this->createEvent('Dia del padre', 'vacation', 1, 'Notas del dia del padre', 1655596800, 1655683199, NULL, NULL, NULL, $student->id);
                    $this->createEvent('Dia de la madre', 'vacation', 1, 'Notas del dia de la madre', 1651363200, 1651449599, NULL, NULL, NULL, $student->id);
                    $this->createEvent('Dia de la mujer', 'vacation', 1, 'Notas del dia de la mujer', 1646697600, 1646783999, NULL, NULL, NULL, $student->id);
                    $this->createEvent('Exámen empresa', 'exam', 0, 'Notas de examen de empresa', 1647334800, 1647344400, NULL, NULL, $subject_ids[array_rand($subject_ids)], $student->id);
                    $this->createEvent('Exámen matematicas', 'exam', 0, 'Notas de examen de matematicas', 1648900800, 1648906800, NULL, NULL, $subject_ids[array_rand($subject_ids)], $student->id);
                    $this->createEvent('Cita medica', 'personal', 0, 'Notas de cita medica', 1652436600, 1652439600, NULL, NULL, NULL, $student->id);
                }
            }
        }
    }

    public function createBlock($time_start, $time_end, $day, $student_id, $subject_id, $period_id) {
        DB::table('blocks')->insert([
            'time_start' => $time_start,
            'time_end' => $time_end,
            'day' => $day,
            'student_id' => $student_id,
            'subject_id' => $subject_id,
            'period_id' => $period_id,
        ]);
    }
    public function createEvent($name, $type, $all_day, $notes, $timestamp_start, $timestamp_end, $nullable1, $nullable2, $subject_id, $student_id) {
        DB::table('events')->insert([
            'name' => $name,
            'type' => $type,
            'all_day' => $all_day,
            'notes' => $notes,
            'timestamp_start' => $timestamp_start,
            'timestamp_end' => $timestamp_end,
            'subject_id' => $subject_id,
            'student_id' => $student_id,
        ]);
    }
}
