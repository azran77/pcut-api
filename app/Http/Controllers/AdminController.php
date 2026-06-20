<?php

namespace App\Http\Controllers;

use App\Models\ClassGroup;
use App\Models\Role;
use App\Models\SurveySubmission;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    // ══════════════════════════════════════════════════════════════════════════
    //  USERS
    // ══════════════════════════════════════════════════════════════════════════

    // GET /api/admin/users
    public function users(Request $request): JsonResponse
    {
        $query = User::with('role')->latest();

        if ($search = $request->query('search')) {
            $query->where(fn ($q) => $q->where('name', 'like', "%$search%")
                ->orWhere('email', 'like', "%$search%")
                ->orWhere('student_id', 'like', "%$search%"));
        }

        if ($role = $request->query('role')) {
            $query->whereHas('role', fn ($q) => $q->where('name', $role));
        }

        $users = $query->paginate(20)->through(fn ($u) => [
            'id'          => $u->id,
            'name'        => $u->name,
            'email'       => $u->email,
            'role'        => $u->role?->name,
            'student_id'  => $u->student_id,
            'institution' => $u->institution,
            'created_at'  => $u->created_at,
        ]);

        return response()->json($users);
    }

    // GET /api/admin/users/{id}
    public function showUser(int $id): JsonResponse
    {
        $user = User::with(['role', 'classes:id,name,code', 'submissions'])->findOrFail($id);
        return response()->json(['user' => $user]);
    }

    // POST /api/admin/users
    public function createUser(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name'        => 'required|string|max:255',
            'email'       => 'required|email|unique:users,email',
            'password'    => 'required|string|min:8',
            'role'        => 'required|in:admin,educator,student',
            'student_id'  => 'nullable|string',
            'institution' => 'nullable|string',
        ]);

        $role = Role::where('name', $data['role'])->firstOrFail();
        $user = User::create([
            'name'        => $data['name'],
            'email'       => $data['email'],
            'password'    => Hash::make($data['password']),
            'role_id'     => $role->id,
            'student_id'  => $data['student_id']  ?? null,
            'institution' => $data['institution'] ?? null,
        ]);

        return response()->json(['user' => $user->load('role')], 201);
    }

    // PUT /api/admin/users/{id}
    public function updateUser(Request $request, int $id): JsonResponse
    {
        $user = User::findOrFail($id);
        $data = $request->validate([
            'name'        => 'sometimes|string|max:255',
            'email'       => "sometimes|email|unique:users,email,$id",
            'password'    => 'sometimes|string|min:8',
            'student_id'  => 'nullable|string',
            'institution' => 'nullable|string',
        ]);

        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        $user->update($data);
        return response()->json(['user' => $user->fresh('role')]);
    }

    // DELETE /api/admin/users/{id}
    public function deleteUser(int $id): JsonResponse
    {
        $user = User::findOrFail($id);
        $user->tokens()->delete();
        $user->delete();
        return response()->json(['message' => 'User deleted.']);
    }

    // POST /api/admin/users/{id}/role
    public function assignRole(Request $request, int $id): JsonResponse
    {
        $data = $request->validate(['role' => 'required|in:admin,educator,student']);
        $user = User::findOrFail($id);
        $role = Role::where('name', $data['role'])->firstOrFail();
        $user->update(['role_id' => $role->id]);
        return response()->json(['user' => $user->fresh('role')]);
    }

    // ══════════════════════════════════════════════════════════════════════════
    //  CLASSES
    // ══════════════════════════════════════════════════════════════════════════

    // GET /api/admin/classes
    public function classes(Request $request): JsonResponse
    {
        $classes = ClassGroup::with('educator:id,name')->withCount('students')->latest()->get();
        return response()->json(['classes' => $classes]);
    }

    // POST /api/admin/classes
    public function createClass(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name'          => 'required|string|max:255',
            'code'          => 'required|string|unique:class_groups,code',
            'description'   => 'nullable|string',
            'educator_id'   => 'nullable|exists:users,id',
            'semester'      => 'nullable|string',
            'academic_year' => 'nullable|string',
        ]);

        $class = ClassGroup::create($data);
        return response()->json(['class' => $class->load('educator:id,name')], 201);
    }

    // PUT /api/admin/classes/{id}
    public function updateClass(Request $request, int $id): JsonResponse
    {
        $class = ClassGroup::findOrFail($id);
        $data  = $request->validate([
            'name'          => 'sometimes|string|max:255',
            'code'          => "sometimes|string|unique:class_groups,code,$id",
            'description'   => 'nullable|string',
            'educator_id'   => 'nullable|exists:users,id',
            'semester'      => 'nullable|string',
            'academic_year' => 'nullable|string',
        ]);
        $class->update($data);
        return response()->json(['class' => $class->fresh('educator:id,name')]);
    }

    // DELETE /api/admin/classes/{id}
    public function deleteClass(int $id): JsonResponse
    {
        ClassGroup::findOrFail($id)->delete();
        return response()->json(['message' => 'Class deleted.']);
    }

    // POST /api/admin/classes/{id}/enroll
    public function enrollStudent(Request $request, int $id): JsonResponse
    {
        $data    = $request->validate(['student_id' => 'required|exists:users,id']);
        $class   = ClassGroup::findOrFail($id);
        $class->students()->syncWithoutDetaching([$data['student_id']]);
        return response()->json(['message' => 'Student enrolled.']);
    }

    // DELETE /api/admin/classes/{id}/enroll
    public function unenrollStudent(Request $request, int $id): JsonResponse
    {
        $data  = $request->validate(['student_id' => 'required|exists:users,id']);
        $class = ClassGroup::findOrFail($id);
        $class->students()->detach($data['student_id']);
        return response()->json(['message' => 'Student unenrolled.']);
    }

    // POST /api/admin/classes/{id}/educator
    public function assignEducator(Request $request, int $id): JsonResponse
    {
        $data  = $request->validate(['educator_id' => 'required|exists:users,id']);
        $class = ClassGroup::findOrFail($id);
        $class->update(['educator_id' => $data['educator_id']]);
        return response()->json(['class' => $class->fresh('educator:id,name')]);
    }

    // ══════════════════════════════════════════════════════════════════════════
    //  SUBMISSIONS & ANALYTICS
    // ══════════════════════════════════════════════════════════════════════════

    // GET /api/admin/submissions
    public function submissions(Request $request): JsonResponse
    {
        $submissions = SurveySubmission::with([
            'student:id,name,student_id',
            'classGroup:id,name',
        ])->latest()->paginate(20);

        return response()->json($submissions);
    }

    // DELETE /api/admin/submissions/{id}
    public function deleteSubmission(int $id): JsonResponse
    {
        $sub = SurveySubmission::findOrFail($id);
        $sub->responses()->delete();
        $sub->delete();
        return response()->json(['message' => 'Submission deleted.']);
    }

    // GET /api/admin/analytics
    public function analytics(): JsonResponse
    {
        $totalStudents     = User::whereHas('role', fn ($q) => $q->where('name', 'student'))->count();
        $totalEducators    = User::whereHas('role', fn ($q) => $q->where('name', 'educator'))->count();
        $totalClasses      = ClassGroup::count();
        $totalSubmissions  = SurveySubmission::count();

        $submissions = SurveySubmission::all();
        $avgLogit    = round($submissions->avg('logit_score'), 2);

        $monthly = SurveySubmission::selectRaw('DATE_FORMAT(completed_at, "%Y-%m") as month, COUNT(*) as count')
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        return response()->json([
            'counts' => [
                'students'    => $totalStudents,
                'educators'   => $totalEducators,
                'classes'     => $totalClasses,
                'submissions' => $totalSubmissions,
            ],
            'avg_logit_score'   => $avgLogit,
            'monthly_submissions' => $monthly,
            'domain_averages' => [
                'M' => round($submissions->avg('logit_m'), 2),
                'R' => round($submissions->avg('logit_r'), 2),
                'P' => round($submissions->avg('logit_p'), 2),
                'T' => round($submissions->avg('logit_t'), 2),
                'O' => round($submissions->avg('logit_o'), 2),
            ],
        ]);
    }
}
