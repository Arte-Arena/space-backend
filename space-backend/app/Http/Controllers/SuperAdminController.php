<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SuperAdminController extends Controller
{
    public function getAllUsers()
    {
        return response()->json(\App\Models\User::all());
    }
}
