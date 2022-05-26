<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\Subject;
use App\Models\Subtask;
use App\Models\Task;
use Google\Service\Classroom;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TasksController extends Controller
{
    public function create(Request $request) {
        $data = $request->getContent();
        if($data) {
            try {
                $validator = Validator::make(json_decode($data, true), [
                    'name' => 'required|string',
                    'date_start' => 'sometimes|numeric',
                    'description' => 'sometimes|string',
                    'subject_id' => 'sometimes|int|exists:subjects,id',
                    'subtasks' => 'sometimes|array',
                ]);

                if (!$validator->fails()) {
                    $data = json_decode($data);

                    $task = new Task();
                    $task->name = $data->name;
                    if(isset($data->date_start)) $task->date_start = $data->date_start;
                    if(isset($data->description)) $task->description = $data->description;
                    $task->student_id = $request->student->id;

                    if(isset($data->subject_id)) {
                        $subject = Subject::find($data->subject_id);
                        if($subject->student_id == $request->student->id) {
                            $task->subject_id = $data->subject_id;
                        } else {
                            $response['response'] = "User doesn't have that subject";
                            return response()->json($response)->setStatusCode(400);
                        }
                    }
                    $task->save();

                    if(isset($data->subtasks)) {
                        foreach ($data->subtasks as $subtask_data) {
                            $subtask = new Subtask();
                            $subtask->name = $subtask_data->name;
                            $subtask->completed = $subtask_data->completed;
                            $subtask->task_id = $task->id;
                            $subtask->save();
                        }
                    }

                    $response['id'] = $task->id;
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
    public function edit(Request $request) {
        $data = $request->getContent();
        if($data) {
            try {
                $validator = Validator::make(json_decode($data, true), [
                    'id' => 'required|integer|exists:tasks,id',
                    'name' => 'required|string',
                    'date_start' => 'sometimes|nullable|numeric',
                    'description' => 'sometimes|string',
                    'completed' => 'sometimes|nullable|boolean',
                    'subject_id' => 'sometimes|int|exists:subjects,id',

                    'subtasks' => 'sometimes|array',            #Validate arrays on requests
                    'subtasks.*.id' => 'sometimes|integer',
                    'subtasks.*.name' => 'required|string',
                    'subtasks.*.completed' => 'sometimes|nullable|boolean',
                ]);

                if (!$validator->fails()) {
                    $data = json_decode($data);

                    if($task = Task::find($data->id)) {
                        if(isset($data->name)) $task->name = $data->name;
                        if(isset($data->date_start)) $task->date_start = $data->date_start;
                        if(isset($data->description)) $task->description = $data->description;
                        if(isset($data->completed)) {
                            $task->completed = $data->completed;
                        } else {
                            $task->completed = false;
                        }
                        if(isset($data->subject_id)) {
                            $subject = Subject::find($data->subject_id);
                            if($subject->student_id == $request->student->id) {
                                $task->subject_id = $data->subject_id;
                            } else {
                                $response['response'] = "User doesn't have that subject";
                                return response()->json($response)->setStatusCode(400);
                            }
                        }
                        $task->save();

                        foreach ($data->subtasks as $subtask_data) {
                            if(isset($subtask_data->id)) {
                                if($subtask = Subtask::find($subtask_data->id)) {
                                    if(isset($subtask_data->name)) $subtask->name = $subtask_data->name;
                                    if(isset($subtask_data->completed)) $subtask->completed = $subtask_data->completed;
                                    $subtask->save();
                                }
                            } else {
                                $subtask = new Subtask();
                                $subtask->name = $subtask_data->name;
                                if(isset($subtask_data->completed)) $subtask->completed = $subtask_data->completed;
                                $subtask->task_id = $task->id;
                                $subtask->save();
                            }
                        }

                        $response['response'] = "Task edited properly";
                        $http_status_code = 200;
                    } else {
                        $response['response'] = "Task by that id doesn't exist.";
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
            return response(null, 204);     //Ran when received data is empty    (412: Precondition failed)
        }
    }

    /**
     * Receives: access token and api-token in headers
     * Returns:
     *      'tasks'   ->  task array of all user's tasks
     * Notes: only tasks of non-deleted subjects will be returned, the function downloads all the tasks of the existing subjects from Classroom
     */
    public function list(Request $request) {
        try {
            $user = $request->user;
            $id = $request->student->id;
            if ($student = Student::find($id)) {
                $subjects = $student->subjects()->get();

                if(!$subjects->isEmpty()) {     //Checks if student has subjects
                    if($student->subjects()->where('google_id', '<>', null)->where('deleted', false)->first()) {
                        $client = new \Google\Client();
                        $client->setAuthConfig('../laravel_id_secret.json');
                        $client->addScope('https://www.googleapis.com/auth/classroom.course-work.readonly');
                        $client->addScope('https://www.googleapis.com/auth/classroom.student-submissions.me.readonly');
                        $client->setAccessToken($user->token);      //Uses received access token from the headers

                        $service = new Classroom($client);
                        unset($client);
                        $google_tasks = array();
                        foreach ($subjects as $subject) {       //Goes over all subjects and adds all the tasks to
                            if($subject->deleted != true) {
                                $courseworks = $service->courses_courseWork->listCoursesCourseWork($subject->google_id)->courseWork;
                                foreach ($courseworks as $coursework) {
                                    array_push($google_tasks, $coursework);        //Stores all tasks of the subjects
                                }
                            }
                        }

                        foreach ($google_tasks as $google_task) {
                            $submission = $service->courses_courseWork_studentSubmissions->listCoursesCourseWorkStudentSubmissions($google_task->courseId, $google_task->id);
                            $submission = $submission->studentSubmissions[0];       //Gets more information (submission status, mark) of each task

                            $task_ref = Task::where('google_id', $google_task->id)->where('student_id', $student->id)->first();
                            if(!$task_ref) {
                                $task = new Task();
                                $task->student_id = $request->student->id;
                                $task->google_id = $google_task->id;
                                $task->subject_id = Subject::where('google_id', $google_task->courseId)->where('student_id',$student->id)->first()->id;

                                $task->name = $google_task->title;
                                if($google_task->description) $task->description = $google_task->description;
                                if($google_task->dueDate) {
                                    $task->date_handover = strtotime($google_task->dueDate->year.'-'.$google_task->dueDate->month.'-'.$google_task->dueDate->day);
                                }

                                if($submission->assignedGrade) $task->mark = ($submission->assignedGrade / $google_task->maxPoints) * 10;

                                switch ($submission->state) {
                                    case 'TURNED_IN': $task->completed = 1; break;
                                    case 'RETURNED': $task->completed = 1; break;
                                    default: $task->completed = 0; break;
                                }
                                $task->save();
                                unset($task);
                            } else {
                                $task_ref->name = $google_task->title;
                                if($google_task->description) $task_ref->description = $google_task->description;
                                if($google_task->dueDate) {
                                    $task_ref->date_handover = strtotime($google_task->dueDate->year.'-'.$google_task->dueDate->month.'-'.$google_task->dueDate->day);
                                }
                                if($google_task->description) $task_ref->description = $google_task->description;

                                if($submission->assignedGrade) $task_ref->mark = ($submission->assignedGrade / $google_task->maxPoints) * 10;

                                switch ($submission->state) {
                                    case 'TURNED_IN': $task_ref->completed = 1; break;
                                    case 'RETURNED': $task_ref->completed = 1; break;
                                    default: $task_ref->completed = 0; break;
                                }
                                $task_ref->save();
                            }
                            unset($task_ref);
                            unset($submission);
                        }
                        unset($google_tasks);
                    }

                    $tasks = $student->tasks()->get();
                    if(!$tasks->isEmpty()) {
                        foreach ($tasks as $task) {
                            if(!$task->subject()->where('deleted', true)->first()) {
                                $task->subtasks = $task->subtasks()->get();     //Adds subtasks to task object
                                $task->subject = $task->subject()->first();     //Adds subject to task object
                            }
                        }

                        $response['tasks'] = $tasks;
                        $http_status_code = 200;
                    } else {
                        $response['response'] = "Student doesn't have tasks";
                        $http_status_code = 400;
                    }
                } else {
                    $response['response'] = "Student doesn't have subjects";
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
    public function delete(Request $request, $id) {
        try {
            if ($task = Task::find($id)) {
                if($task->student()->first()->id == $request->student->id) {
                    if($task->google_id == null) {
                        foreach($task->subtasks()->get() as $subtask) {
                            $subtask->delete();
                        }
                        $task->delete();
                        $response['response'] = "Task deleted successfully.";
                        $http_status_code = 200;
                    } else {
                        $response['response'] = "Task can't be deleted.";
                        $http_status_code = 403;
                    }
                } else {
                    $response['response'] = "Student doesn't have this task.";
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
            if ($task = Task::find($id)) {
                if($task->student()->first()->id == $request->student->id) {
                    if($task->google_id == null) {
                        if($task->completed == true) {
                            $task->completed = false;
                            $task->save();
                            $response['response'] = 0;
                            $http_status_code = 200;
                        } else {
                            $task->completed = true;
                            $task->save();
                            $response['response'] = 1;
                            $http_status_code = 200;
                        }
                    } else {
                        $response['response'] = "Can't toggle this task.";
                        $http_status_code = 400;
                    }
                } else {
                    $response['response'] = "User doesn't have this task.";
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
}
