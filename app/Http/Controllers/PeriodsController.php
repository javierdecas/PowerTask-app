<?php

namespace App\Http\Controllers;

use App\Models\Block;
use App\Models\Contain;
use App\Models\Period;
use App\Models\Student;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PeriodsController extends Controller
{
    function create(Request $request) {
        $data = $request->getContent();
        if($data) {
            try {
                $validator = Validator::make(json_decode($data, true), [
                    'name' => 'required|string',

                    'date_start' => 'required|numeric',
                    'date_start' => 'required|numeric|gte:date_start',

                    'subjects' => 'required|array',
                    'subjects.*.id' => 'required|numeric',
                    'subjects.*.name' => 'required|string',
                    'subjects.*.color' => 'required|string',
                ]);

                if (!$validator->fails()) {
                    $data = json_decode($data);

                    $period = new Period();
                    $period->name = $data->name;
                    $period->date_start = $data->date_start;
                    $period->date_end = $data->date_end;
                    $period->student_id = $request->student->id;
                    $period->save();

                    foreach ($data->subjects as $subject) {
                        $subject = Subject::find($subject->id);
                        if($subject->student()->first()->id == $request->student->id) {
                            $contain = new Contain();
                            $contain->period_id = $period->id;
                            $contain->subject_id = $subject->id;
                            $contain->save();

                            if($subject_ref = Subject::where('id', $subject->id)->first()) {
                                $subject_ref->name = $subject->name;
                                $subject_ref->color = $subject->color;
                                $subject_ref->save();
                            }
                        }
                    }

                    $response['id'] = $period->id;
                    $http_status_code = 201;
                } else {
                    $response['response'] = $validator->errors()->first();
                    $http_status_code = 400;
                }
            } catch (\Throwable $th) {
                $response['response'] = "An error has occurred: ".$th->getMessage();
                $http_status_code = 500;
            }
            return response()->json($response)->setStatusCode($http_status_code);
        } else {
            return response(null, 412);     //Ran when received data is empty    (412: Precondition failed)
        }
    }
    public function edit(Request $request, $id) {
        $data = $request->getContent();
        if($data) {
            try {
                $validator = Validator::make(json_decode($data, true), [
                    'name' => 'required|string',
                    'date_start' => 'required|numeric',
                    'date_start' => 'required|numeric|gte:date_start',

                    'subjects' => 'required|array',
                    'subjects.*.id' => 'required|numeric',
                    'subjects.*.name' => 'required|string',
                    'subjects.*.color' => 'required|string',

                    'blocks' => 'sometimes|array',
                    'blocks.*.time_start' => 'required|numeric',
                    'blocks.*.time_end' => 'required|numeric',
                    'blocks.*.day' => 'required|numeric',
                    'blocks.*.subject.id' => 'required|numeric',
                ]);

                if (!$validator->fails()) {
                    $data = json_decode($data);

                    if($period = Period::find($id)) {
                        if(isset($data->name)) $period->name = $data->name;
                        if(isset($data->date_start)) $period->date_start = $data->date_start;
                        if(isset($data->date_end)) $period->date_end = $data->date_end;
                        $period->save();

                        Contain::where('period_id', $period->id)->delete();
                        $insert_data = array();
                        foreach($data->subjects as $subject) {
                            array_push($insert_data, ['period_id' => $period->id, 'subject_id' => $subject->id]);
                            if($subject_ref = Subject::where('id', $subject->id)->first()) {
                                $subject_ref->name = $subject->name;
                                $subject_ref->color = $subject->color;
                                $subject_ref->deleted = $subject_ref->deleted;
                                $subject_ref->save();
                            }
                        }
                        DB::table('contains')->insert($insert_data);

                        if($data->blocks) {
                            Block::where('period_id', $period->id)->delete();
                            $insert_data = array();
                            foreach($data->blocks as $block) {
                                array_push($insert_data, ['time_start' => $block->time_start, 'time_end' => $block->time_end, 'day' => $block->day, 'subject_id' => $block->subject->id, 'student_id' => $request->student->id, 'period_id' => $period->id]);
                                DB::table('blocks')->insert($insert_data);
                            }
                        }
                        $response['response'] = "Period edited properly";
                        $http_status_code = 200;
                    } else {
                        $response['response'] = "Period by that id doesn't exist.";
                        $http_status_code = 404;
                    }
                } else {
                    $response['response'] = $validator->errors()->first();
                    $http_status_code = 400;
                }
            } catch (\Throwable $th) {
                $response['response'] = "An error has occurred: ".$th->getMessage();
                $http_status_code = 500;
            }
            return response()->json($response)->setStatusCode($http_status_code);
        } else {
            return response(null, 412);     //Ran when received data is empty    (412: Precondition failed)
        }
    }
    public function delete(Request $request, $id) {
        try {
            if ($period = Period::find($id)) {
                if($period->student()->first()->id == $request->student->id) {
                    if($blocks = $period->blocks()->get()) {
                        foreach ($blocks as $block) {
                            $block->delete();
                        }
                    }
                    if($subjects = $period->subjects()->get()) {
                        foreach ($subjects as $subject) {
                            Contain::where('period_id', $id)->where('subject_id', $subject->id)->first()->delete();
                        }
                    }
                    $period->delete();
                    $response['response'] = "Period deleted successfully.";
                    $http_status_code = 200;
                } else {
                    $response['response'] = "Student doesn't have this period.";
                    $http_status_code = 400;
                }
            } else {
                $response['response'] = "Period by that id doesn't exist.";
                $http_status_code = 404;
            }
        } catch (\Throwable $th) {
            $response['response'] = "An error has occurred: ".$th->getMessage();
            $http_status_code = 500;
        }
        return response()->json($response)->setStatusCode($http_status_code);
    }
    public function list(Request $request) {
        try {
            $id = $request->student->id;
            if ($student = Student::find($id)) {
                $periods = $student->periods()->get();
                if(!$periods->isEmpty()) {
                    foreach ($periods as $period) {
                        if($blocks = $period->blocks()->get()) {
                            $period->blocks = $blocks;
                            $period->subjects = $period->subjects()->get();
                            foreach($blocks as $block) {
                                $block->subject = $block->subject()->first();
                            }
                        }
                    }
                    $response['periods'] = $periods;
                    $http_status_code = 200;
                } else {
                    $response['response'] = "Student doesn't have periods";
                    $http_status_code = 400;
                }
            } else {
                $response['response'] = "Student by that id doesn't exist.";
                $http_status_code = 404;
            }
        } catch (\Throwable $th) {
            $response['response'] = "An error has occurred: ".$th->getMessage();
            $http_status_code = 500;
        }
        return response()->json($response)->setStatusCode($http_status_code);
    }
    public function getSubjects(Request $request, $id) {
        try {
            if ($period = Period::find($id)) {
                $subjects = $period->subjects()->where('deleted', false)->get();
                if(!$subjects->isEmpty()) {
                    $response['subjects'] = $subjects;
                    $http_status_code = 200;
                } else {
                    $response['response'] = "Period doesn't have subjects";
                    $http_status_code = 400;
                }
            } else {
                $response['response'] = "Period by that id doesn't exist.";
                $http_status_code = 404;
            }
        } catch (\Throwable $th) {
            $response['response'] = "An error has occurred: ".$th->getMessage();
            $http_status_code = 500;
        }
        return response()->json($response)->setStatusCode($http_status_code);
    }
}
