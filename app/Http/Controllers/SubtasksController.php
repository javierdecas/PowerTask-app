<?php

namespace App\Http\Controllers;

use App\Models\Subject;
use App\Models\Subtask;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SubtasksController extends Controller
{
    public function create(Request $request, $id) {
        $data = $request->getContent();
        if($data) {
            try {
                $validator = Validator::make(json_decode($data, true), [
                    'name' => 'required|string',
                ]);

                if (!$validator->fails()) {
                    $data = json_decode($data);

                    $subtask = new Subtask();
                    $subtask->name = $data->name;

                    if($task = Task::find($id)) {
                        if($task->student()->first()->id == $request->student->id) {
                            $subtask->task_id = $id;
                            $subtask->save();

                            $response['id'] = $subtask->id;
                            $http_status_code = 201;
                        } else {
                            $response['response'] = "Student doesn't have this task";
                            $http_status_code = 400;
                        }
                    } else {
                        $response['response'] = "Task id doesn't match any task";
                        $http_status_code = 400;
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
    public function edit(Request $request, $id) {
        $data = $request->getContent();
        if($data) {
            try {
                $validator = Validator::make(json_decode($data, true), [
                    'name' => 'required|string',
                ]);

                if (!$validator->fails()) {
                    $data = json_decode($data);

                    if($subtask = Subtask::find($id)) {
                        if($subtask->task()->first()->student()->first()->id == $request->student->id) {
                            $subtask->name = $data->name;
                            $subtask->save();

                            $response['response'] = "Subtask edited properly";
                            $http_status_code = 200;
                        } else {
                            $response['response'] = "Student doesn't have this task";
                            $http_status_code = 400;
                        }
                    } else {
                        $response['response'] = "Subtask by that id doesn't exist.";
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
            if ($subtask = Subtask::find($id)) {
                if($subtask->task()->first()->student()->first()->id == $request->student->id) {
                    $subtask->delete();
                    $response['response'] = "Subtask deleted successfully.";
                    $http_status_code = 200;
                } else {
                    $response['response'] = "Student doesn't have this subtask.";
                    $http_status_code = 400;
                }
            } else {
                $response['response'] = "Task by that id doesn't exist.";
                $http_status_code = 404;
            }
        } catch (\Throwable $th) {
            $response['response'] = "An error has occurred: ".$th->getMessage();
            $http_status_code = 500;
        }
        return response()->json($response)->setStatusCode($http_status_code);
    }
    public function toggleCheck(Request $request, $id) {
        try {
            if ($subtask = Subtask::find($id)) {
                if($subtask->task()->first()->student()->first()->id == $request->student->id) {
                    if($subtask->completed == true) {
                        $subtask->completed = false;
                        $subtask->save();
                        $response['response'] = 0;
                        $http_status_code = 200;
                    } else {
                        $subtask->completed = true;
                        $subtask->save();
                        $response['response'] = 1;
                        $http_status_code = 200;
                    }
                } else {
                    $response['response'] = "Student doesn't have this subtask";
                    $http_status_code = 400;
                }
            } else {
                $response['response'] = "Subtask by that id doesn't exist.";
                $http_status_code = 404;
            }
        } catch (\Throwable $th) {
            $response['response'] = "An error has occurred: ".$th->getMessage();
            $http_status_code = 500;
        }
        return response()->json($response)->setStatusCode($http_status_code);
    }
}
