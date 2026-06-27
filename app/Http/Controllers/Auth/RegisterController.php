<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = '/tokodashboard';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'nomor_telepon' => ['required', 'string', 'min:11', 'unique:users'],
            'tipe_user' => ['sometimes', 'required', 'in:end_customer,retailer'],
            'foto_toko' => ['sometimes', 'file', 'mimes:jpg,jpeg,png,webp', 'max:2048', 'required_if:tipe_user,retailer'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\Models\User
     */
    protected function create(array $data)
    {
        $tipeUser = $data['tipe_user'] ?? 'end_customer';
        $statusVerifikasi = $tipeUser === 'retailer' ? 'pending' : 'approved';
        $fotoTokoPath = null;

        if (!empty($data['foto_toko']) && is_object($data['foto_toko']) && method_exists($data['foto_toko'], 'store')) {
            $fotoTokoPath = $data['foto_toko']->store('foto-toko', 'public');
        }

        // Generate username from name (remove spaces, lowercase)
        $baseUsername = strtolower(str_replace(' ', '', $data['name']));
        $username = $baseUsername;

        // Ensure username is unique by appending number if needed
        $counter = 1;
        while (User::where('username', $username)->exists()) {
            $username = $baseUsername . $counter;
            $counter++;
        }

        $user = User::create([
            'f_name' => $data['name'],
            'email' => $data['email'],
            'nomor_telepon' => $data['nomor_telepon'],
            'username' => $username,
            'password' => Hash::make($data['password']),
            'user' => 'User', // Set default role
            'img' => 'default-avatar.png',
            'tipe_user' => $tipeUser,
            'status_verifikasi' => $statusVerifikasi,
            'foto_toko' => $fotoTokoPath,
        ]);

        // Assign role using Laratrust
        $user->addRole('user');

        return $user;
    }
}
