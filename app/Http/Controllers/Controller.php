<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

abstract class Controller
{
    /**
     * Whether this caller wants the raw JSON resource rather than an Inertia page.
     *
     * The cart / wishlist / address endpoints predate the storefront UI and are
     * covered by tests that call them with `getJson()` etc. Those must keep
     * getting JSON, while a browser hitting the same URL gets the rendered page.
     *
     * Inertia's own XHR visits are excluded explicitly: they run through axios,
     * which sets `X-Requested-With: XMLHttpRequest`, so `expectsJson()` alone
     * would wrongly classify them as API calls.
     */
    protected function wantsJson(Request $request): bool
    {
        return $request->expectsJson() && ! $request->inertia();
    }
}
