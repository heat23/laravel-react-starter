<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\DataHealthService;
use Inertia\Inertia;
use Inertia\Response;

class AdminDataHealthController extends Controller
{
    public function index(DataHealthService $dataHealth): Response
    {
        return Inertia::render('Admin/DataHealth', [
            'checks' => $dataHealth->runAllChecks(),
        ]);
    }
}
