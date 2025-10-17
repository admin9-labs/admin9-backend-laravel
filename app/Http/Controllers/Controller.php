<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Validation\ValidatesRequests;
use Mitoop\Http\RespondsWithJson;

abstract class Controller
{
    use RespondsWithJson, ValidatesRequests;
}
