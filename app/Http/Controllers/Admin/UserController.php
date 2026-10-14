<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Division;
use App\Models\User;
use App\Support\Division as DivisionAccess;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public const TYPES = ['Super Admin', 'Admin', 'Staff'];

    public function index(Request $request)
    {
        $this->authorizeAdmin($request);

        $search = trim((string) $request->query('q'));
        $type = $request->query('type');
        $division = $request->query('division');

        $users = User::select('id', 'name', 'email', 'type', 'updated_at', 'image_path', 'stock_loc_sys_code')
            ->when($search !== '', fn ($q) => $q->where(fn ($w) => $w
                ->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")))
            ->when(in_array($type, self::TYPES, true), fn ($q) => $q->where('type', $type))
            ->when($division, fn ($q) => $q->where('stock_loc_sys_code', $division))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.users', [
            'users' => $users,
            'types' => self::TYPES,
            'divisions' => $this->divisionOptions(),
        ]);
    }

    public function create(Request $request)
    {
        $this->authorizeAdmin($request);

        return view('admin.create', ['user' => new User(), 'types' => self::TYPES, 'divisions' => $this->divisionOptions()]);
    }

    public function store(Request $request)
    {
        $this->authorizeAdmin($request);

        $validator = Validator::make($request->all(), $this->rules(), $this->messages());

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors()
            ], 422);
        }

        $data = $request->only(['name', 'email', 'type']);
        $data['password'] = Hash::make($request->password);
        $data['stock_loc_sys_code'] = $request->input('division') ?: null;

        // Handle profile image
        if ($request->hasFile('image_path')) {
            $data['image_path'] = $this->saveImage($request, $data['name']);
        }

        $user = User::create($data);

        return response()->json([
            'success' => true,
            'message' => 'User created successfully!',
            'user'    => $user,
        ]);
    }

    public function edit(Request $request, User $user)
    {
        $this->authorizeAdmin($request);

        return view('admin.create', ['user' => $user, 'types' => self::TYPES, 'divisions' => $this->divisionOptions()]);
    }

    public function update(Request $request, User $user)
    {
        $this->authorizeAdmin($request);

        $validator = Validator::make($request->all(), $this->rules($user), $this->messages());

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors()
            ], 422);
        }

        $data = $request->only(['name', 'email', 'type']);
        $data['stock_loc_sys_code'] = $request->input('division') ?: null;

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        if ($request->hasFile('image_path')) {
            $data['image_path'] = $this->saveImage($request, $data['name']);
        }

        // don't let an admin lock themselves out of the admin pages
        if ($user->id === $request->user()->id) {
            $after = (clone $user)->fill($data);
            DivisionAccess::flush();
            if (! $after->isAdmin() || ! DivisionAccess::seesAll($after)) {
                return response()->json([
                    'success' => false,
                    'errors' => ['division' => ['You cannot remove your own admin access. Ask another admin to change it.']],
                ], 422);
            }
        }

        $user->update($data);
        DivisionAccess::flush();

        return response()->json([
            'success' => true,
            'message' => 'User updated successfully!',
            'user'    => $user,
        ]);
    }

    public function destroy(Request $request, User $user)
    {
        $this->authorizeAdmin($request);

        if ($user->id === $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'You cannot delete your own account.'
            ]);
        }

        if ($user->type === 'Super Admin') {
            return response()->json([
                'success' => false,
                'message' => 'You cannot delete a super admin.'
            ]);
        }

        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'User deleted successfully!'
        ]);
    }

    private function rules(?User $user = null): array
    {
        return [
            'name'       => 'required|string|max:255',
            'email'      => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user?->id)],
            'password'   => [$user ? 'nullable' : 'required', 'string', 'min:6', 'confirmed'],
            'type'       => ['required', Rule::in(self::TYPES)],
            // every user except a Super Admin must belong to a division
            'division'   => [
                'nullable', 'required_unless:type,Super Admin',
                Rule::exists('pStock_Location_Tab', 'stock_loc_sys_code')->where('Comp_code', Division::COMPANY),
            ],
            'image_path' => 'nullable|image|mimes:jpg,jpeg,png,gif|max:2048',
        ];
    }

    private function messages(): array
    {
        return [
            'division.required_unless' => 'Choose the division this user works in.',
            'division.exists' => 'Choose a division from the list.',
        ];
    }

    private function saveImage(Request $request, string $name): string
    {
        $file = $request->file('image_path');
        $filename = time() . '_' . Str::slug($name) . '.' . $file->getClientOriginalExtension();
        $file->move(public_path('uploads/users'), $filename);

        return 'uploads/users/' . $filename;
    }

    /** All active divisions, Overall first. */
    private function divisionOptions()
    {
        return Division::query()
            ->where('status', '<>', 'I')
            ->orderByDesc('Is_Main')
            ->orderBy('stock_loc_user_code')
            ->get();
    }

    private function authorizeAdmin(Request $request): void
    {
        abort_unless($request->user()->canManageSystem(), 403, 'Only an admin of the Overall division can manage users.');
    }
}
