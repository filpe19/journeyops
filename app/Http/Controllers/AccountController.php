<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class AccountController extends Controller
{
    public function show(Request $request): View
    {
        return view('account.show', [
            'user' => $request->user(),
            'orders' => $request->user()->orders()->with('event')->latest()->get(),
        ]);
    }
}
