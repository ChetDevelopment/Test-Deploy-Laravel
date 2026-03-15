<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Student;

class StudentController extends Controller
{
    public function getStudentsByClass($className)
    {
        $students = Student::where('class', $className)->get();
        return response()->json($students);
    }
}