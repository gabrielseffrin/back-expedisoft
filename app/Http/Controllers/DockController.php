<?php

namespace App\Http\Controllers;

use App\Services\DockService;

class DockController extends Controller
{

    public function __construct(private readonly DockService $dockService)
    {
    }

    public function getAllDocks(): \Illuminate\Database\Eloquent\Collection
    {
        return $this->dockService->getAllDocks();
    }
}
