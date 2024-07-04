<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('faculties', [App\Http\Controllers\FacultyController::class, 'import']);
Route::get('groups', [App\Http\Controllers\GroupController::class, 'downloadFile']);
Route::post('import-students', [App\Http\Controllers\ImportController::class, 'importStudents']);
