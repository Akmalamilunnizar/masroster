<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class AdminProfileController extends Controller
{
    // Menampilkan profil admin yang sedang login
    public function index()
    {
        $profile = Auth::user();
        return view('admin.adminprofile', compact('profile'));
    }

    // Mencari profil berdasarkan nama atau email
    public function SearchProfile(Request $request)
    {
        $search = $request->search;

        $users = User::where(function ($query) use ($search) {
            $query->where('id', 'like', "%$search%")
                ->orWhere('f_name', 'like', "%$search%");
        })->get();

        return view('admin.adminprofile', compact('users', 'search'));
    }

    // Memperbarui data profil
    public function update(Request $request, $id)
    {
        $request->validate([
            'f_name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $id,
            'email_verified_at' => 'nullable|date',
        ]);

        try {
            $profile = User::findOrFail($id);
            $profile->f_name = $request->input('f_name');
            $profile->email = $request->input('email');

            if ($request->filled('password')) {
                $profile->password = bcrypt($request->input('password'));
            }

            $profile->save();

            return redirect()->route('admin.profiles.index')->with('success', 'Profil berhasil diperbarui.');
        } catch (\Exception $e) {
            Log::error('Error updating profile: ' . $e->getMessage());
            return redirect()->back()->withErrors(['error' => 'Terjadi kesalahan saat memperbarui profil.']);
        }
    }

    // Mengupdate profil admin
    public function StoreProfile(Request $request)
    {
        // Debug: Log the incoming request data
        Log::info('Profile Update Request Data:', $request->all());
        
        $id = Auth::user()->username;
        $profile = User::find($id);

        if (!$profile) {
            Log::error('User not found with ID: ' . $id);
            return redirect()->back()->withErrors(['error' => 'User not found.']);
        }

        // Debug: Log the current user data
        Log::info('Current User Data:', [
            'id' => $profile->id,
            'name' => $profile->f_name,
            'email' => $profile->email
        ]);

        try {
            $validated = $request->validate(
                [
                    'f_name' => 'required|string|max:255',
                    'email' => 'required|email|max:255|unique:users,email,' . $id,
                    'currentPassword' => 'nullable|min:6',
                    'newPassword' => 'nullable|min:6|confirmed',
                    'img' => 'nullable|image|mimes:png,jpg,gif,jpeg|max:2048',
                ],
                [
                    'f_name.required' => 'Nama wajib diisi.',
                    'f_name.string' => 'Nama harus berupa teks.',
                    'f_name.max' => 'Nama tidak boleh lebih dari 255 karakter.',

                    'email.required' => 'Email wajib diisi.',
                    'email.email' => 'Format email tidak valid.',
                    'email.max' => 'Email tidak boleh lebih dari 255 karakter.',
                    'email.unique' => 'Email ini sudah terdaftar.',

                    'currentPassword.nullable' => 'Password saat ini opsional.',
                    'currentPassword.min' => 'Password saat ini harus terdiri dari minimal 6 karakter.',

                    'newPassword.nullable' => 'Password baru opsional.',
                    'newPassword.min' => 'Password baru harus terdiri dari minimal 6 karakter.',
                    'newPassword.confirmed' => 'Konfirmasi password baru tidak sesuai.',

                    'img.nullable' => 'Gambar profil opsional.',
                    'img.image' => 'File yang diunggah harus berupa gambar.',
                    'img.mimes' => 'Gambar harus memiliki format PNG, JPG, GIF, atau JPEG.',
                    'img.max' => 'Ukuran gambar tidak boleh lebih dari 2MB.',
                ]
            );

            // Debug: Log the validated data
            Log::info('Validated Data:', $validated);

            // Update nama dan email
            $profile->f_name = $request->input('f_name');
            $profile->email = $request->input('email');

            // Verifikasi dan update password jika ada
            if ($request->filled('newPassword')) {
                if (!Hash::check($request->input('currentPassword'), $profile->password)) {
                    return redirect()->back()->withErrors(['currentPassword' => 'Password saat ini tidak valid.']);
                }

                $profile->password = bcrypt($request->input('newPassword'));
            }

            // Update gambar profil jika ada
            if ($request->hasFile('img')) {
                // Delete old image if exists
                if ($profile->img) {
                    $del = public_path('uploads/users/' . $profile->img);
                    if (File::exists($del)) {
                        File::delete($del);
                    }
                }

                $file = $request->file('img');
                $filename = 'images/' . time() . '.' . $file->getClientOriginalExtension();
                
                // Ensure the directory exists
                $directory = public_path('uploads/users/images');
                if (!File::exists($directory)) {
                    File::makeDirectory($directory, 0755, true);
                }
                
                $file->move(public_path('uploads/users/images'), basename($filename));
                $profile->img = $filename;
            }

            $profile->save();

            // Debug: Log successful update
            Log::info('Profile updated successfully for user ID: ' . $id);

            return redirect()->route('profile')->with('success', 'Profil berhasil diperbarui.');
        } catch (\Exception $e) {
            // Debug: Log the error
            Log::error('Error updating profile: ' . $e->getMessage());
            Log::error('Error trace: ' . $e->getTraceAsString());
            return redirect()->back()->withErrors(['error' => 'Terjadi kesalahan saat memperbarui profil.']);
        }
    }

    // Menghapus profil
    public function destroy($id)
    {
        try {
            $profile = User::findOrFail($id);

            // Hapus gambar profil jika ada
            $imgPath = public_path('uploads/users/' . $profile->img);
            if (File::exists($imgPath)) {
                File::delete($imgPath);
            }

            $profile->delete();

            return redirect()->route('admin.profiles.index')->with('success', 'Profil berhasil dihapus.');
        } catch (\Exception $e) {
            Log::error('Error deleting profile: ' . $e->getMessage());
            return redirect()->back()->withErrors(['error' => 'Terjadi kesalahan saat menghapus profil.']);
        }
    }
}
