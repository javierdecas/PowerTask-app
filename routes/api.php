<?php

use App\Http\Controllers\BlocksController;
use App\Http\Controllers\ClassroomController;
use App\Http\Controllers\CoursesController;
use App\Http\Controllers\EventsController;
use App\Http\Controllers\PeriodsController;
use App\Http\Controllers\SessionsController;
use App\Http\Controllers\StudentsController;
use App\Http\Controllers\SubjectsController;
use App\Http\Controllers\SubtasksController;
use App\Http\Controllers\TasksController;
use App\Models\Student;
use Illuminate\Support\Facades\Route;


use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('checkApiToken')->group(function () {
    Route::prefix('task')->group(function() {
        Route::post('create', [TasksController::class, 'create']);
        Route::put('edit', [TasksController::class, 'edit']);
        Route::middleware('getUserFromToken')->get('list', [TasksController::class, 'list']);
        Route::delete('delete/{id}', [TasksController::class, 'delete']);
        Route::put('toggle/{id}', [TasksController::class, 'toggleCheck']);
    });

    Route::prefix('subtask')->group(function() {
        Route::post('create/{id}', [SubtasksController::class, 'create']);
        Route::put('edit/{id}', [SubtasksController::class, 'edit']);
        Route::delete('delete/{id}', [SubtasksController::class, 'delete']);
        Route::put('toggle/{id}', [SubtasksController::class, 'toggleCheck']);
    });

    Route::prefix('period')->group(function() {
        Route::post('create', [PeriodsController::class, 'create']);
        Route::put('edit/{id}', [PeriodsController::class, 'edit']);
        Route::delete('delete/{id}', [PeriodsController::class, 'delete']);
        Route::get('list', [PeriodsController::class, 'list']);
        Route::get('getSubjects/{id}', [PeriodsController::class, 'getSubjects']);
    });

    Route::prefix('course')->group(function() {
        Route::post('create', [CoursesController::class, 'create']);
        Route::put('edit/{id}', [CoursesController::class, 'edit']);
        Route::delete('delete/{id}', [CoursesController::class, 'delete']);
        Route::get('list/{id}', [CoursesController::class, 'list']);
    });

    Route::prefix('session')->group(function() {
        Route::post('create', [SessionsController::class, 'create']);
        Route::delete('delete/{id}', [SessionsController::class, 'delete']);
        Route::get('list', [SessionsController::class, 'list']);
    });

    // Route::prefix('block')->group(function() {
    //     Route::post('create/{id}', [BlocksController::class, 'create']);
    //     Route::put('edit/{id}', [BlocksController::class, 'edit']);
    //     Route::delete('delete/{id}', [BlocksController::class, 'delete']);
    //     Route::get('list/{id}', [BlocksController::class, 'list']);
    // });

    Route::prefix('event')->group(function() {
        Route::post('create', [EventsController::class, 'create']);
        Route::put('edit/{id}', [EventsController::class, 'edit']);
        Route::delete('delete/{id}', [EventsController::class, 'delete']);
        Route::get('list', [EventsController::class, 'list']);
    });

    Route::prefix('subject')->group(function() {
        Route::put('edit/{id}', [SubjectsController::class, 'edit']);
        Route::middleware('getUserFromToken')->get('list', [SubjectsController::class, 'list']);
    });


    Route::prefix('student')->group(function() {
        Route::get('initialDownload', [StudentsController::class, 'initialDownload'])->middleware('getUserFromToken');

        Route::put('edit', [StudentsController::class, 'edit']);
        Route::post('uploadImage', [StudentsController::class, 'uploadImage']);

        Route::prefix('widget')->group(function() {
            Route::get('getAllWidgetInfo', [StudentsController::class, 'getAllWidgetInfo']);
            // Route::get('totalSessionTime', [StudentsController::class, 'widget_totalSessionTime']);
            // Route::get('daysUntilPeriodEnds', [StudentsController::class, 'widget_daysUntilPeriodEnds']);
            // Route::get('completedTasks', [StudentsController::class, 'widget_completedTasks']);
            // Route::get('markAverage', [StudentsController::class, 'widget_markAverage']);
        });
    });
});

Route::post('loginRegister', [StudentsController::class, 'loginRegister'])->middleware('getUserFromToken');




