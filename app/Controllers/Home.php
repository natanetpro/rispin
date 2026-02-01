<?php

namespace App\Controllers;

class Home extends BaseController
{
    public function index()
    {
        // Check if user is logged in
        if (service('authentication')->check()) {
            return redirect()->to('/dashboard');
        }

        // If not logged in, redirect to login
        return redirect()->to('/login');
    }

    public function dashboard(): string
    {
        return view('pages/dashboard/index');
    }
}
