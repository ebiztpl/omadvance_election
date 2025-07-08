<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    // public function createUser(Request $request)
    // {
    //     $request->validate([
    //         'admin_name' => 'required|string|unique:admin_master,admin_name',
    //         'admin_pass' => 'required|string|min:6|confirmed',
    //         'role' => 'required|in:1,2,3', // 1=admin, 2=manager, 3=user
    //     ]);

    //     $user = new User([
    //         'admin_name' => $request->admin_name,
    //         'admin_pass' => $request->admin_pass,
    //         'role' => $request->role,
    //         'posted_date' => now(),
    //     ]);

    //     $user->save();

    //     return back()->with('success', 'User created successfully.');
    // }

    public function showLoginForm()
    {
        return view('login'); 
    }

    public function login(Request $request)
    {
       
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
            'user_role' => 'required|in:admin,manager,user',
        ]);

        $roleMap = ['admin' => 1, 'manager' => 2, 'user' => 3];
        $roleValue = $roleMap[$request->user_role];

        $user = User::where('admin_name', $request->username)
            ->where('role', $roleValue)
            ->first();

        if ($user && Hash::check($request->password, $user->admin_pass)) {

            session([
                'logged_in_user' => $user->admin_name,
                'logged_in_role' => $user->role,
                'user_id' => $user->admin_id,
            ]);

            switch ($request->user_role) {
                case 'admin':
                    return redirect('/admin/dashboard');
                case 'manager':
                    return redirect('/manager/division_master');
                case 'user':
                    // return redirect('/user/home');
            }
        }

        return back()->withErrors(['login_error' => 'Invalid credentials or role.']);
    }

    public function logout()
    {
        session()->flush();
        return redirect('/login');
    }

    public function showChangePasswordForm()
    {
        return view('change_password');
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'old_password' => 'required',
            'new_password' => 'required|min:6|confirmed',
        ]);
        // dd(session()->all());


        $admin = User::find(session('user_id'));

        if (!$admin) {
            return back()->withErrors(['msg' => 'Admin not found.']);
        }

        if (!Hash::check($request->old_password, $admin->admin_pass)) {
            return back()->withErrors(['old_password' => 'Old password does not match.']);
        }

        $admin->admin_pass = $request->new_password;
        $admin->modify_at = now();
        $admin->save();

        session()->flush();

        return redirect('/login')->with('success', 'Password changed successfully. Please log in again.');
    }
}
