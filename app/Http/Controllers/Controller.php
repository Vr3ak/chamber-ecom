<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

abstract class Controller
{
    protected function wantsJson(Request $request): bool
    {
        return $request->expectsJson() && ! $request->inertia();
    }
}
