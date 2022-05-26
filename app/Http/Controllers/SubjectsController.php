<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\Subject;
use Google\Service\Classroom;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class SubjectsController extends Controller
{
    function edit(Request $request, $id) {
        $data = $request->getContent();
        if($data) {
            try {
                $validator = Validator::make(json_decode($data, true), [
                    'name' => 'sometimes|string',
                    'color' => 'sometimes|string',
                ]);

                if (!$validator->fails()) {
                    $data = json_decode($data);

                    if($subject = Subject::find($id)) {
                        if($subject->student()->first()->id == $request->student->id) {
                            if(isset($data->name)) $subject->name = $data->name;
                            if(isset($data->color)) $subject->color = $data->color;
                            $subject->save();

                            $response['response'] = "Subject edited properly";
                            $http_status_code = 200;
                        } else {
                            $response['response'] = "Student doesn't have this subject";
                            $http_status_code = 400;
                        }
                    } else {
                        $response['response'] = "Subject by that id doesn't exist.";
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
    function list(Request $request) {
        try {
            $user = $request->user;

            if($user) {
                $student = Student::where('google_id', $user->id)->first();

                if($student) {
                    $client = new \Google\Client();
                    $client->setAuthConfig('../laravel_id_secret.json');
                    $client->addScope(\Google\Service\Classroom::CLASSROOM_COURSES);
                    $client->setAccessToken($user->token);

                    $service = new Classroom($client);
                    $courses = $service->courses->listCourses()->courses;

                    $subjects = Subject::all();
                    if(!$subjects->isEmpty()) {
                        foreach ($subjects as $subject) {
                            $exists = false;
                            foreach ($courses as $course) {
                                if($subject->google_id == $course->id) {
                                    $exists = true;
                                }
                            }
                            if(!$exists) {
                                $subject->deleted = true;
                            } else {
                                $subject->deleted = false;
                            }
                            $subject->save();
                        }
                    }
                    foreach ($courses as $course) {
                        if(!$course->enrollmentCode) {
                            if(!Subject::where('google_id', $course->id)->where('student_id', $student->id)->first()) {
                                $subject = new Subject();
                                $subject->name = $course->name;
                                $subject->google_id = $course->id;
                                $subject->student_id = $student->id;
                                $subject->save();
                                unset($subject);
                            }
                        }
                    }
                    unset($courses);

                    $response['subjects'] = $student->subjects()->where('deleted', false)->get();
                    $http_status_code = 200;
                } else {
                    $response['response'] = "Student not found";
                    $http_status_code = 404;
                }
            } else {
                $response['response'] = "Student doesn't exist";
                $http_status_code = 404;
            }
        } catch (\Throwable $th) {
            $response['response'] = "An error has occurred: ".$th->getMessage();
            $http_status_code = 500;
            Log::channel('errors')->info('[app/Http/Controllers/AuthController.php : getCourses] An error has ocurred', [
                'error' => $th->getMessage(),
            ]);
        }
        return response()->json($response)->setStatusCode($http_status_code);
    }
}
