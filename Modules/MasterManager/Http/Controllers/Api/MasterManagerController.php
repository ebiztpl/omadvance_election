<?php

namespace Modules\MasterManager\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Modules\MasterManager\Entities\MasterUser;
use Modules\MasterManager\Entities\LoginHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class MasterManagerController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'nullable|email',
            'password' => 'required|string|min:6',
        ]);

        $user = MasterUser::on('master')->create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Registration successful',
            'redirect' => route('mastermanager.login.view')
        ]);
    }

    public function login(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'password' => 'required|string',
        ]);

        $user = MasterUser::on('master')->where('name', $request->name)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials',
            ]);
        }

        // Save session
        session([
            'logged_in_user_id' => $user->id,
            'logged_in_user'    => $user->name,
            'logged_in_role'    => $user->role ?? 'admin',
        ]);

        // Record login history
        LoginHistory::on('master')->create([
            'admin_id'        => $user->id,
            'login_date_time' => now(),
            'ip'              => $request->ip(),
            'user_agent'      => $request->header('User-Agent'),
        ]);

        return response()->json([
            'success'  => true,
            'message'  => 'Login successful',
            'redirect' => route('master.dashboard'),
        ]);
    }

    public function logout(Request $request)
    {
        $userId = session('logged_in_user_id');

        if ($userId) {
            LoginHistory::on('master')
                ->where('admin_id', $userId)
                ->latest('login_date_time')
                ->update(['logout_date_time' => now()]);
        }

        $request->session()->forget(['logged_in_user', 'logged_in_user_id', 'logged_in_role']);
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('mastermanager.login.view')
            ->with('success', 'Logged out successfully');
    }

    public function checkUsername(Request $request)
    {
        $exists = MasterUser::on('master')->where('name', $request->name)->exists();
        return response()->json(['exists' => $exists]);
    }
}
