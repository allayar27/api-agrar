<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;

class AcademicYearController extends Controller
{
    public function index(): JsonResponse
    {
        var_dump(openssl_get_cert_locations());
        $response = Http::withHeaders([
            'accept' => 'application/json',
            'Authorization' => 'Bearer '.env('HEMIS_BEARER_TOKEN')
        ])->get('https://student.tdaunukus.uz/rest/v1/data/student-list?page=1&limit=12222');
        return response()->json($response->json()->items);
    }
}
