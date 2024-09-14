<?php

use App\Http\Controllers\DeviceController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MainController;
use App\Http\Controllers\FacultyController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\TeacherController;
use App\Http\Controllers\AttendanceController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->group(function(){

});

Route::post('attendance/add', [AttendanceController::class,'create']);
Route::get('groups', [App\Http\Controllers\GroupController::class, 'allGroups']);
Route::get('groups/{id}',[\App\Http\Controllers\GroupController::class, 'getGroupById']);
// Route::get('reports', [\App\Http\Controllers\GroupController::class, 'reports']);
// Route::get('reports/day', [\App\Http\Controllers\GroupController::class, 'dailyGroupReport']);

//faculties

Route::get('faculties', [FacultyController::class, 'allFaculties']);
Route::get('faculties2', [FacultyController::class, 'allFaculties2']);
Route::get('main/all',[MainController::class, 'index']);
Route::get('main/students',[StudentController::class, 'allStudents']);
Route::get('main/late_comers',[StudentController::class, 'lateComers']);
Route::post('import',[StudentController::class, 'import']);
Route::get('main/note_comers',[StudentController::class, 'noteComers']);
Route::get('monthly',[StudentController::class, 'monthly']);
Route::get('student/latest/{id}',[StudentController::class, 'studentAttendance']);
// Route::get('main/student',[MainController::class, 'student']);

Route::get('late_teachers',[TeacherController::class, 'allTeachers']);
Route::get('teachers/days',[TeacherController::class, 'getMonthlyStatistics']);
Route::get('employees/days',[TeacherController::class, 'getEmployeesMonthly']);

Route::get('lastComers',[AttendanceController::class,'lastComers']);
Route::get('latest',[AttendanceController::class,'latest']);

Route::post('device/add',[DeviceController::class,'create']);
