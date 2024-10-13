<?php

namespace App\Http\Controllers\web;

use App\Http\Controllers\Controller;
use App\Models\UsersLog;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function users(Request $request):View
    {
        $date = $request->input('date') ?: Carbon::today()->toDateString();

        $users = UsersLog::query()->whereDate('date_time', $date)->orderByDesc('date_time')->paginate(20);

        return view('admin.not_found_users.users', ['users' => $users, 'selectedDate' => $date]);
    }

    public function index()
    {
        
    }
}
