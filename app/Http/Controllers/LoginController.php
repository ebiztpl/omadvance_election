<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\Models\RegistrationForm;
use App\Models\AssignPosition;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
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
            'user_role' => 'required|in:एडमिन,मैनेजर,कार्यालय',
            'g-recaptcha-response' => 'required'
        ]);

        // Verify reCAPTCHA
        $recaptcha = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
            'secret' => env('RECAPTCHA_SECRET_KEY'),
            'response' => $request->input('g-recaptcha-response'),
            'remoteip' => $request->ip(),
        ]);

        if (!($recaptcha->json()['success'] ?? false)) {
            return back()->withErrors(['g-recaptcha-response' => 'Please confirm you are not a robot.'])->withInput();
        }


        $roleMap = ['एडमिन' => 1, 'मैनेजर' => 2, 'कार्यालय' => 3];
        $roleValue = $roleMap[$request->user_role];

        $user = User::where('admin_name', $request->username)
            ->where('role', $roleValue)
            ->first();

        if ($user && Hash::check($request->password, $user->admin_pass)) {

            $logId = DB::table('login_history')->insertGetId([
                'admin_id' => $user->admin_id,
                'login_date_time' => now(),
                'logout_date_time' => null,
                'ip' => $request->ip(),
                'user_agent'      => $request->header('User-Agent'),
            ], 'login_history_id');

            session([
                'logged_in_user' => $user->admin_name,
                'logged_in_role' => $user->role,
                'user_id' => $user->admin_id,
                'log_id' => $logId,
            ]);

            switch ($request->user_role) {
                case 'एडमिन':
                    return redirect('/admin/page');
                case 'मैनेजर':
                    return redirect('/manager/dashboard');
                case 'कार्यालय':
                    return redirect('/operator/dashboard');
            }
        }

        return back()->withErrors(['login_error' => 'Invalid credentials or role.']);
    }

    // public function logout()
    // {
    //     session()->flush();
    //     return redirect('/login');
    // }

    // public function logout(Request $request)
    // {
    //     $logId = Session::get('log_id');
    //     dd($logId);
    //     if ($logId) {
    //         DB::table('login_history')->where('login_history_id', $logId)->update([
    //             'logout_date_time' => now()
    //         ]);
    //     }

    //     Session::flush();
    //     return redirect('/login')->with('success', 'Logged out successfully.');
    // }


    public function logout(Request $request)
    {
        $logId = session('log_id');
        // dd($logId);

        if ($logId) {
            DB::table('login_history')
                ->where('login_history_id', $logId)
                ->update(['logout_date_time' => now()]);
        }

        $request->session()->flush();

        // return redirect('/login')->with('success', 'Logged out successfully.');
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

    public function messageSent($otp, $mobile)
    {
        if (!preg_match('/^[6-9][0-9]{9}$/', $mobile)) {
            \Log::error('Invalid mobile format: ' . $mobile);
            return 0;
        }

        $senderId = "EBIZTL";
        $flow_id = '686e28df91e9813c053cb273';
        $recipients = [[
            "mobiles" => "91" . $mobile,
            "otp" => $otp,
        ]];

        $postData = [
            "sender" => $senderId,
            "flow_id" => $flow_id,
            "recipients" => $recipients
        ];

        $url = "http://api.msg91.com/api/v5/flow/";
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => json_encode($postData),
            CURLOPT_HTTPHEADER => [
                "authkey: 459517AVl7UerR686e26ffP1",
                "content-type: application/json"
            ],
        ]);

        $output = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);

        if ($err) {
            \Log::error('SMS send error: ' . $err);
            return 0;
        }

        $response = json_decode($output, true);

        if ($response && isset($response['type']) && $response['type'] === 'success') {
            \Log::info("OTP sent to $mobile: $otp");
            return 1;
        } else {
            \Log::error("Failed OTP send to $mobile. Response: " . $output);
            return 0;
        }
    }

    public function sendOtp(Request $request)
    {
        $request->validate(['mobile' => 'required']);

        $inputMobile = $request->mobile;

        $sendToMobile = $inputMobile;

        $member = RegistrationForm::where('mobile1', $inputMobile)->first();

        if (!$member) {
            return response()->json([
                'status' => 'error',
                'message' => 'मोबाइल नंबर पंजीकृत नहीं है या अधिकृत नहीं है!'
            ]);
        }

        $assign = AssignPosition::where('member_id', $member->registration_id)
            ->where('position_id', 8)
            ->first();

        if (!$assign) {
            return response()->json([
                'status' => 'error',
                'message' => 'आप अधिकृत नहीं हैं!'
            ]);
        }

        $otp = rand(100000, 999999);

        $member->update([
            'otp' => $otp,
            'otp_created_at' => now()
        ]);

        $sent = $this->messageSent($otp, $sendToMobile);

        return response()->json([
            'status' => $sent ? 'success' : 'error',
            'message' => $sent ? 'ओटीपी सफलतापूर्वक भेजा गया!' : 'ओटीपी भेजने में विफल!'
        ]);
    }


    public function verifyOtp(Request $request)
    {
        $request->validate([
            'mobile' => 'required',
            'otp' => 'required|digits:6',
            'g-recaptcha-response' => 'required'
        ]);

        $recaptcha = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
            'secret' => config('services.recaptcha.secret'),
            'response' => $request->input('g-recaptcha-response'),
            'remoteip' => $request->ip(),
        ]);

        if (!($recaptcha->json()['success'] ?? false)) {
            return back()->withErrors(['g-recaptcha-response' => 'Please confirm you are not a robot.'])->withInput();
        }


        $member = RegistrationForm::where('mobile1', $request->mobile)
            ->where('otp', $request->otp)
            ->first();

        if (!$member) {
            return back()->withErrors(['otp' => 'अमान्य ओटीपी या अनुमति नहीं है!']);
        }

        $assign = AssignPosition::where('member_id', $member->registration_id)
            ->where('position_id', 8)
            ->first();

        if (!$assign) {
            return back()->withErrors(['otp' => 'आप अधिकृत नहीं हैं!']);
        }

        $logId = DB::table('login_history')->insertGetId([
            'registration_id' => $member->registration_id,
            'login_date_time' => now(),
            'logout_date_time' => null,
            'ip' => $request->ip(),
            'user_agent'      => $request->header('User-Agent'),
        ], 'login_history_id');

        Session::put([
            'registration_id' => $member->registration_id,
            'admin_name' => $member->name ?? '',
            'admin_role' => 'फ़ील्ड',
            'log_id' => $logId,
        ]);

        $member->update(['otp' => null]);

        return redirect('member/complaint');
        // return redirect('member/complaint')->with('success', 'आप सफलतापूर्वक लॉग इन हो गए हैं!');
    }



    // public function loginhistory()
    // {
    //     if (!$this->usermodel->hasLoggedIn()) {
    //         redirect("account/login");
    //     }
    //     $data = $this->db->select("*")->from('login_history')->order_by("login_date_time", "desc")->get()->result_array();
    //     $this->load->view($this->folder . "login_history", array("rst" => $data));
    // }
}
