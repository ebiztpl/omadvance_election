<?php

namespace Modules\Sampark\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Modules\Sampark\Entities\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AuthController extends Controller
{
    // ===== Web (Blade) =====
    public function showLogin()
    {
        return view('sampark::login');
    }

    public function showRegister()
    {
        return view('sampark::register');
    }

    public function login(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'password' => 'required|string',
        ]);

        // Find user on sampark connection
        $user = User::on('sampark')
            ->where('name', $request->name)
            ->first();

        // Validate credentials
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'यूज़रनेम या पासवर्ड गलत है।'
            ], 401);
        }

        // Set connection for this user instance
        $user->setConnection('sampark');

        // Generate Sanctum token (this is all you need for API auth)
        $token = $user->createToken('sampark_api')->plainTextToken;

        $logId = DB::connection('sampark')->table('login_history')->insertGetId([
            'admin_id'        => $user->id,
            'login_date_time' => now(),
            'ip'              => $request->ip(),
            'user_agent'      => $request->header('User-Agent'),
        ], 'login_history_id');

        return response()->json([
            'success' => true,
            'message' => 'आप सफलतापूर्वक लॉगिन हो गए हैं।',
            'user'    => $user,
            'token'   => $token,
            'log_id'  => $logId
        ]);
    }


    public function register(Request $request)
    {
        $request->validate([
            'name' => [
                'required',
                function ($attribute, $value, $fail) {
                    $exists = DB::connection('sampark')
                        ->table('sampark_users')
                        ->where('name', $value)
                        ->exists();
                    if ($exists) {
                        $fail('यह यूज़रनेम पहले से लिया जा चुका है।');
                    }
                }
            ],
            'email' => ['nullable', 'email'],
            'password' => 'required|min:6',
        ]);

        $user = new User();
        $user->setConnection('sampark');
        $user->name = $request->name;
        $user->email = $request->email;
        $user->password = Hash::make($request->password);
        $user->save();

        // Create Sanctum token
        $token = $user->createToken('sampark_api')->plainTextToken;


        // Always return JSON for AJAX
        return response()->json([
            'success' => true,
            'message' => 'आपका अकाउंट सफलतापूर्वक बना लिया गया है।',
            'user' => $user,
            'token' => $token,
            'redirect' => '/sampark/login'
        ]);
    }


    public function logout(Request $request)
    {
        $logId = session('log_id');

        if ($logId) {
            DB::connection('sampark')->table('login_history')
                ->where('login_history_id', session('login_history_id'))
                ->update(['logout_date_time' => now()]);
        }

        session()->forget(['user', 'login_history_id']);

        return redirect()->route('sampark.login')->with('success', 'आप लॉग आउट हो गए हैं।');
    }

    public function checkUsername(Request $request)
    {
        $exists = DB::connection('sampark')
            ->table('sampark_users')
            ->where('name', $request->name)
            ->exists();

        return response()->json(['exists' => $exists]);
    }
}
