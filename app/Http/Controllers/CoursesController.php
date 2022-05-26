<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CoursesController extends Controller
{
    function create(Request $request) {
        $data = $request->getContent();
        if($data) {
            try {
                $validator = Validator::make(json_decode($data, true), [
                    'name' => 'required|string',
                ]);

                if (!$validator->fails()) {
                    $data = json_decode($data);

                    $course = new Course();
                    $course->name = $data->name;
                    $course->student_id = $request->student->id;

                    $course->save();

                    $response['id'] = $course->id;
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
                ]);

                if (!$validator->fails()) {
                    $data = json_decode($data);

                    if($course = Course::find($id)) {
                        $course->name = $data->name;
                        $course->save();

                        $response['response'] = "Course edited properly";
                        $http_status_code = 200;
                    } else {
                        $response['response'] = "Course by that id doesn't exist.";
                        $http_status_code = 404;
                    }
                } else {
                    $response['response'] =$validator->errors()->first();
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
            if ($course = Course::find($id)) {
                $course->delete();
                $response['response'] = "Course deleted successfully.";
                $http_status_code = 200;
            } else {
                $response['response'] = "Course by that id doesn't exist.";
                $http_status_code = 404;
            }
        } catch (\Throwable $th) {
            $response['response'] = "An error has occurred: ".$th->getMessage();
            $http_status_code = 500;
        }
        return response()->json($response)->setStatusCode($http_status_code);
    }
    public function list(Request $request, $id) {
        try {
            if ($student = Student::find($id)) {
                $courses = $student->courses()->get();
                if(!$courses->isEmpty()) {
                    $response['courses'] = $courses;
                    $http_status_code = 200;
                } else {
                    $response['response'] = "Student doesn't have courses";
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
