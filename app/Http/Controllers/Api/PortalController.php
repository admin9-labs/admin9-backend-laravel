<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

class PortalController extends Controller
{
    public function welcome()
    {
        return $this->success("It's Admin9");
    }

    public function home()
    {
        return $this->success('Welcome to Admin9 API');
    }
}
