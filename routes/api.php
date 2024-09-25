<?php

use App\Http\Controllers\DeviceController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MainController;
use App\Http\Controllers\FacultyController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\TeacherController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\GroupController;

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
Route::get('groups', [GroupController::class, 'allGroups']);
Route::get('groups/{id}',[GroupController::class, 'getGroupById']);
Route::get('reports', [GroupController::class, 'monthReport']);
Route::get('reports/study_days', [GroupController::class, 'getMonthStudyDays']);
Route::get('reports/by_students', [GroupController::class, 'monthReportByStudents']);
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
Route::get('student/monthly/not_comers', [StudentController::class, 'mothlyNotComers']);
Route::get('student/monthly/late_comers', [StudentController::class, 'mothlyLateComers']);
// Route::get('main/student',[MainController::class, 'student']);

Route::get('late_teachers',[TeacherController::class, 'allTeachers']);
Route::get('teachers/days',[TeacherController::class, 'getMonthlyStatistics']);
Route::get('employees/days',[TeacherController::class, 'getEmployeesMonthly']);
Route::get('teachers/all', [TeacherController::class, 'getAllTeachers']);
Route::get('teachers/daily', [TeacherController::class, 'dayliReport']);
Route::get('teachers/monthly', [TeacherController::class, 'monthReport']);
Route::get('teachers/month_study_days', [TeacherController::class, 'monthStudyDays']);
Route::get('teachers/month_report_by_teachers', [TeacherController::class, 'monthReportByTeachers']);

Route::get('lastComers',[AttendanceController::class,'lastComers']);
Route::get('residential',[AttendanceController::class,'residential']);
Route::get('latest',[AttendanceController::class,'latest']);

Route::post('device/add',[DeviceController::class,'create']);
