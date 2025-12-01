<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Student;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    /**
     * Get all active students
     */
    public function index(Request $request)
    {
        $query = Student::with('class')
            ->where('is_active', true);

        // Filter by class
        if ($request->has('class_id')) {
            $query->where('class_id', $request->class_id);
        }

        // Filter by gender
        if ($request->has('gender')) {
            $query->where('gender', $request->gender);
        }

        // Search by name or NIS
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('nis', 'like', "%{$search}%");
            });
        }

        // Only students with face data
        if ($request->has('with_face_data') && $request->with_face_data) {
            $query->whereNotNull('face_image')
                  ->whereNotNull('face_embeddings');
        }

        $students = $query->get();

        return response()->json([
            'success' => true,
            'data' => $students,
            'total' => $students->count(),
        ]);
    }

    /**
     * Get student by ID
     */
    public function show($id)
    {
        $student = Student::with(['class', 'attendances' => function($query) {
            $query->latest()->limit(10);
        }])->find($id);

        if (!$student) {
            return response()->json([
                'success' => false,
                'message' => 'Student not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'student' => $student,
                'has_face_data' => $student->hasFaceData(),
                'today_attendance' => $student->getTodayAttendance(),
                'attendance_rate' => $student->getAttendanceRate(),
            ],
        ]);
    }

    /**
     * Get student by NIS
     */
    public function getByNIS($nis)
    {
        $student = Student::with('class')
            ->where('nis', $nis)
            ->where('is_active', true)
            ->first();

        if (!$student) {
            return response()->json([
                'success' => false,
                'message' => 'Student not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $student->id,
                'nis' => $student->nis,
                'name' => $student->name,
                'class' => $student->class->name,
                'has_face_data' => $student->hasFaceData(),
                'checked_in_today' => $student->hasCheckedInToday(),
                'checked_out_today' => $student->hasCheckedOutToday(),
            ],
        ]);
    }
}