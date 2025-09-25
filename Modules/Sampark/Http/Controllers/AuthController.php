<?php

namespace Modules\Sampark\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Modules\Sampark\Entities\User;
use Illuminate\Support\Facades\DB;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('sampark::login'); // your Blade file
    }

    public function login(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'password' => 'required|string',
            'g-recaptcha-response' => 'required'
        ]);

        $recaptcha = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
            'secret' => env('RECAPTCHA_SECRET_KEY'),
            'response' => $request->input('g-recaptcha-response'),
            'remoteip' => $request->ip(),
        ]);

        if (!($recaptcha->json()['success'] ?? false)) {
            return back()->withErrors(['g-recaptcha-response' => 'Please confirm you are not a robot.'])->withInput();
        }

        $user = User::where('name', $request->name)->first();

        if ($user && Hash::check($request->password, $user->password)) {
            $request->session()->regenerate();
            session(['user' => $user->id]);

            $logId = DB::connection('sampark')->table('login_history')->insertGetId([
                'admin_id'        => $user->id,
                'login_date_time' => now(),
                'logout_date_time' => null,
                'ip'              => $request->ip(),
                'user_agent'      => $request->header('User-Agent')
            ], 'login_history_id');


            session([
                'logged_in_user' => $user->name,
                'logged_in_role' => $user->role,
                'user_id' => $user->id,
                'log_id' => $logId,
            ]);

            session(['login_history_id' => $logId]);

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'redirect' => route('sampark.dashboard'),
                    'message' => 'आप सफलतापूर्वक लॉगिन हो गए हैं।'
                ]);
            }

            return redirect()->route('sampark.dashboard')->with('success', 'आप सफलतापूर्वक लॉगिन हो गए हैं।');
        }

        if ($request->ajax()) {
            return response()->json([
                'success' => false,
                'message' => 'यूज़रनेम या पासवर्ड गलत है।'
            ], 401);
        }

        return back()->with('error', 'यूज़रनेम या पासवर्ड गलत है।');
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

    public function showRegister()
    {
        return view('sampark::register');
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'email' => [
                'required',
                'email',
                function ($attribute, $value, $fail) {
                    $exists = DB::connection('sampark')->table('sampark_users')->where('email', $value)->exists();
                    if ($exists) {
                        $fail('The email has already been taken.');
                    }
                },
            ],
            'password' => 'required|min:4',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $request->session()->regenerate();
        session(['user' => $user->id]);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'redirect' => route('sampark.login'),
                'message' => 'आपका अकाउंट सफलतापूर्वक बना लिया गया है।'
            ]);
        }

        return redirect()->route('sampark.login')
            ->with('success', 'आपका अकाउंट सफलतापूर्वक बना लिया गया है।');
    }
}
