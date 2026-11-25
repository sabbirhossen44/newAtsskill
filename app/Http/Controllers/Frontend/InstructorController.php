<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Repositories\InstructorRepository;

class InstructorController extends Controller
{
    public static function getActiveInstructors()
    {
        return InstructorRepository::activeOrdered();
    }
}
