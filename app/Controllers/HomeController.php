<?php

namespace App\Controllers;

class HomeController extends Controller
{
    public function index()
    {
        if (isset($_SESSION['user_id'])) {
            $this->redirect('/dashboard');
        } else {
            $this->redirect('/login');
        }
    }
}
