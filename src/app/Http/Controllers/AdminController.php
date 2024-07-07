<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function dashboard()
    {
        $users = User::withCount('posts', 'comments')->get();
        return view('admin.dashboard', compact('users'));
    }
}
