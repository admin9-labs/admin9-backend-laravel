<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Validation\ValidatesRequests;
use Mitoop\Http\JsonResponder;

abstract class Controller
{
    use JsonResponder;
    use ValidatesRequests;
}
