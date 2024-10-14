<?php

namespace App\Http\Controllers\web;

use App\Http\Controllers\Controller;
use App\Imports\StudentImport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ExcelController extends Controller
{
    public function import(Request $request)
    {
        $file = $request->file('file');
        Excel::import(new StudentImport, $file);
        return back()->with('success', 'Excel Data Imported successfully.');
    }
}
