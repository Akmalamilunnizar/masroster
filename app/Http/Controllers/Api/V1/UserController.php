<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
// use App\Models\Category;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    //
    public function Index()
    {
        $users = User::all();

        return view('admin.allusers', compact('users'));
    }

    public function SearchUsers(Request $request)
    {
        $validated = $request->validate([
            'search' => 'nullable|string|max:255',
        ]);

        $search = trim((string) ($validated['search'] ?? ''));

        $users = User::where(function ($query) use ($search) {

            $query->where('id', 'like', "%$search%")
                ->orWhere('f_name', 'like', "%$search%");

        })->get();

        return view('admin.allusers', compact('users', 'search'));
    }

    public function AddUsers()
    {
        $users = User::latest()->get();

        return view('admin.addusers', compact('users'));
    }

    public function StoreUsers(Request $request)
    {

        $request->validate([
            'f_name' => 'required',
            'email' => 'required|unique:users,email',
            'phone' => 'required',
            'password' => 'required|min:8',
            // 'email_verified_at' => now()
        ]);

        // $category_id = $request->category_id;

        // $category_name = Category::where('id', $category_id)->value('category_name');

        $user = User::create([
            'f_name' => $request->f_name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => bcrypt($request->password),
            'img' => 'images/default-avatar.png',
        ]);
        $user->addRole('user');

        // $token = $user->createToken('RestaurantCustomerAuth')->accessToken;

        // return response()->json(['token' => $token,'is_phone_verified' => 0, 'phone_verify_end_url'=>"api/v1/auth/verify-phone" ], 200);
        // Category::where('id', $category_id)->increment('subcategory_count', 1);

        return redirect()->route('allusers')->with('message', 'Pengguna telah berhasil ditambah!');

    }

    public function EditUsers($id)
    {
        $users_info = User::findOrFail($id);

        return view('admin.editusers', compact('users_info'));
    }

    public function UpdateUsers(Request $request)
    {
        $request->validate([
            'f_name' => 'required',
            'email' => ['required', Rule::unique('users')->ignore($request->id)],
            'phone' => 'required',
            'password' => 'required|min:8',
        ]);

        $userid = $request->id;
        $mytime = Carbon::now();
        $mytime->toDateTimeString();
        User::findOrFail($userid)->update([
            'f_name' => $request->f_name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => bcrypt($request->password),
            // 'updated_at' => $mytime,
        ]);

        return redirect()->route('allusers')->with('message', 'Pengguna berhasil diupdate!');
    }

    public function DeleteUsers(User $user)
    {
        $user->delete();

        return redirect()->route('allusers')->with('message', 'Pengguna berhasil dihapus');
    }
}
