<?php

namespace App\Http\Controllers;

use App\Models\Session;
use App\Models\Student;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SessionsController extends Controller
{
    function create(Request $request) {
        $data = $request->getContent();
        if($data) {
            try {
                $validator = Validator::make(json_decode($data, true), [
                    'quantity' => 'required|integer',
                    'duration' => 'required|integer',
                    'total_time' => 'required|integer',
                    'task_id' => 'sometimes|integer|exists:tasks,id',
                ]);

                if (!$validator->fails()) {
                    $data = json_decode($data);

                    $session = new Session();
                    $session->quantity = $data->quantity;
                    $session->duration = $data->duration;
                    $session->total_time = $data->total_time;
                    if(isset($data->task_id)) {
                        if(Task::find($data->task_id)->student()->first()->id == $request->student->id) {
                            $session->task_id = $data->task_id;
                        } else {
                            $response['response'] = "Student doesn't have task";
                            return response()->json($response)->setStatusCode(400);
                        }
                    }
                    $session->student_id = $request->student->id;

                    $session->save();

                    $response['id'] = $session->id;
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
    public function delete(Request $request, $id) {
        try {
            if ($session = Session::find($id)) {
                if($session->student()->first()->id == $request->student->id) {
                    $session->delete();
                    $response['response'] = "Session deleted successfully.";
                    $http_status_code = 200;
                } else {
                    $response['response'] = "Student doesn't have session.";
                    $http_status_code = 400;
                }
            } else {
                $response['response'] = "Session by that id doesn't exist.";
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
                $sessions = $student->sessions()->get();
                if(!$sessions->isEmpty()) {
                    $response['sessions'] = $sessions;
                    $http_status_code = 200;
                } else {
                    $response['response'] = "Student doesn't have sessions.";
                    $http_status_code = 400;
                }
            } else {
                $response['response'] = "Student not found.";
                $http_status_code = 404;
            }
        } catch (\Throwable $th) {
            $response['response'] = "An error has occurred: ".$th->getMessage();
            $http_status_code = 500;
        }
        return response()->json($response)->setStatusCode($http_status_code);
    }
}
