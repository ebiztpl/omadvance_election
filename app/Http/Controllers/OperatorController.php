<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\District;
use App\Models\RegistrationForm;
use App\Models\Step2;
use App\Models\Step3;
use App\Models\Step4;
use App\Models\Mandal;
use App\Models\Nagar;
use App\Models\Area;
use App\Models\Polling;
use App\Models\Reply;
use App\Models\Level;
use App\Models\Position;
use Illuminate\Support\Str;
use App\Models\Complaint;
use App\Models\City;
use App\Models\State;
use Carbon\Carbon;
use Intervention\Image\Facades\Image;
use App\Models\Division;
use Mpdf\Mpdf;
use App\Models\VidhansabhaLokSabha;
use Illuminate\Support\Facades\Response;

class OperatorController extends Controller
{
    public function index()
    {
        $states = State::orderBy('name')->get();
        $nagars = Nagar::orderBy('nagar_name')->get();
        return view('operator/complaints', compact('states', 'nagars'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'txtname' => 'required|string|max:255',
            'mobile' => 'nullable|string|regex:/^[0-9]{10,15}$/',
            'division_id' => 'required|integer',
            'voter' => 'required|string|max:255',
            'txtdistrict_name' => 'required|integer',
            'txtvidhansabha' => 'required|integer',
            'txtmandal' => 'required|integer',
            'txtgram' => 'required|integer',
            'txtpolling' => 'required|integer',
            'txtarea' => 'required|integer',
            'txtaddress' => 'nullable|string|max:1000',
            'CharCounter' => 'required|string|max:100',
            'NameText' => 'required|string|max:2000',
            'type' => 'required|string',
            'department' => 'nullable',
            'post' => 'nullable',
            'from_date' => 'nullable|date',
            'program_date' => 'nullable|date',
            'to_date' => 'nullable',
            'file_attach' => 'nullable|file|max:20480',
        ]);

        $userId = session('user_id');

        if (!$userId) {
            return back()->with('error', 'User session expired. Please log in again.');
        }

        // $userAreaId = DB::table('step2')
        //     ->where('registration_id', $userId)
        //     ->value('area_id');

        // if ((int)$userAreaId !== (int)$request->txtarea) {
        //     return back()->with('error', 'आप केवल अपने क्षेत्र के लिए ही शिकायत दर्ज कर सकते हैं।');
        // }

        $vidhansabha = (int) $request->txtvidhansabha;
        $ref = '00';
        if ($vidhansabha === 50) {
            $ref = '19';
        } elseif ($vidhansabha === 49) {
            $ref = '18';
        }

        $randomTimestamp = mt_rand(strtotime('2018-01-01'), time());
        $rndno = date('dHi', $randomTimestamp);
        $complaint_number = 'BJS' . $ref . '-' . $rndno;

        $attachment = '';
        if ($request->hasFile('file_attach')) {
            $file = $request->file('file_attach');
            $extension = $file->getClientOriginalExtension();
            $blocked = ['exe', 'php', 'js'];

            if (in_array(strtolower($extension), $blocked)) {
                return back()->with('error', 'This file type is not allowed.');
            }

            $filename = time() . '_' . Str::random(6) . '.' . $extension;
            $file->move(public_path('assets/upload/complaints'), $filename);
            $attachment = $filename;
        }

        // $mobile = RegistrationForm::where('registration_id', $userId)->value('mobile1');

        $complaint = Complaint::create([
            'user_id' => session('user_id'),
            'name' => $request->txtname,
            'mobile_number' => $request->mobile,
            'email' => $request->mobile,
            'voter_id' => $request->voter,
            'complaint_type' => $request->type,
            'issue_title' => $request->CharCounter,
            'issue_description' => $request->NameText,
            'address' => $request->txtaddress,
            'division_id' => $request->division_id,
            'district_id' => $request->txtdistrict_name,
            'vidhansabha_id' => $vidhansabha,
            'mandal_id' => $request->txtmandal,
            'gram_id' => $request->txtgram,
            'polling_id' => $request->txtpolling,
            'area_id' => $request->txtarea,
            'issue_attachment' => $attachment,
            'complaint_number' => $complaint_number,
            'complaint_department' => $request->department ?? '',
            'complaint_designation' => $request->post ?? '',
            'news_date' => $request->from_date,
            'complaint_status' => 1,
            'program_date' => $request->program_date,
            'complaint_created_by'  => session('user_id'),
            'type' => 2,
            'news_time' => $request->filled('to_date')  ? $request->to_date : '00:00',
            'posted_date' => now(),
        ]);

        // $message = 'आपकी शिकायत सफलतापूर्वक दर्ज की गई है। शिकायत संख्या: ' . $complaint_number;
        // $this->messageSent($complaint_number, $mobile);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'शिकायत सफलतापूर्वक दर्ज की गई है। आपकी शिकायत संख्या है: ' . $complaint_number,
            ]);
        }

        return redirect()->route('operator_complaint.index')->with('success', 'शिकायत सफलतापूर्वक दर्ज की गई है। आपकी शिकायत संख्या है: ' . $complaint_number);

        // return redirect()->route('operator_complaint.index')->with('success', 'शिकायत सफलतापूर्वक दर्ज की गई है। आपकी शिकायत संख्या है: ' . $complaint_number);
    }

    public function view_complaints()
    {
        $userId = session('user_id');

        if (!$userId) {
            return redirect()->route('login')->with('error', 'कृपया पहले लॉगिन करें।');
        }


        $complaints = Complaint::with(['polling', 'area'])
            ->where('complaint_created_by', $userId)
            ->where('type', 2)
            ->get();

        return view('operator/view_complaint', compact('complaints'));
    }


    public function getDistricts($division_id)
    {
        $districts = District::where('division_id', $division_id)->get();
        return response()->json($districts->map(function ($d) {
            return "<option value='{$d->district_id}'>{$d->district_name}</option>";
        }));
    }

    public function getVidhansabhas($district_id)
    {
        $vidhansabhas = VidhansabhaLokSabha::where('district_id', $district_id)->get();
        return response()->json($vidhansabhas->map(function ($v) {
            return "<option value='{$v->vidhansabha_id}'>{$v->vidhansabha}</option>";
        }));
    }

    public function getMandals($vidhansabha_id)
    {
        $mandals = Mandal::where('vidhansabha_id', $vidhansabha_id)->get();
        return response()->json($mandals->map(function ($m) {
            return "<option value='{$m->mandal_id}'>{$m->mandal_name}</option>";
        }));
    }

    public function getNagars($mandal_id)
    {
        $nagars = Nagar::where('mandal_id', $mandal_id)->get();
        return response()->json($nagars->map(function ($n) {
            return "<option value='{$n->nagar_id}'>{$n->nagar_name}</option>";
        }));
    }

    public function getPollings($mandal_id)
    {
        $pollings = Polling::where('mandal_id', $mandal_id)->get([
            'gram_polling_id',
            'polling_name',
            'polling_no'
        ]);

        return response()->json($pollings);
    }

    public function getAreas($polling_id)
    {
        $areas = Area::where('polling_id', $polling_id)->get([
            'area_id',
            'area_name'
        ]);

        return response()->json($areas);
    }

    public function getMandalFromNagar($nagar_id)
    {
        $nagar = Nagar::find($nagar_id);

        if (!$nagar) {
            return response()->json([
                'error' => 'Nagar not found'
            ], 404);
        }

        return response()->json([
            'mandal_id' => $nagar->mandal_id
        ]);
    }

    public function getVidhansabhaFromMandal($mandal_id)
    {
        $mandal = Mandal::find($mandal_id);

        if (!$mandal) {
            return response()->json(['error' => 'Mandal not found'], 404);
        }

        return response()->json([
            'vidhansabha_id' => $mandal->vidhansabha_id
        ]);
    }

    public function getDistrictFromVidhansabha($vidhansabha_id)
    {
        $vidhansabha = VidhansabhaLokSabha::find($vidhansabha_id);

        if (!$vidhansabha) {
            return response()->json(['error' => 'Vidhansabha not found'], 404);
        }

        return response()->json([
            'district_id' => $vidhansabha->district_id
        ]);
    }

    public function getDivisionFromDistrict($district_id)
    {
        $district = District::find($district_id);

        if (!$district) {
            return response()->json(['error' => 'District not found'], 404);
        }

        return response()->json([
            'division_id' => $district->division_id
        ]);
    }


    public function getMandalOptionsFromId($mandal_id)
    {
        $mandal = Mandal::find($mandal_id);
        return response("<option value='{$mandal->mandal_id}' selected>{$mandal->mandal_name}</option>");
    }

    public function getVidhansabhaOptionsFromId($vidhansabha_id)
    {
        $vidhansabha = VidhansabhaLokSabha::find($vidhansabha_id);
        return response("<option value='{$vidhansabha->vidhansabha_id}' selected>{$vidhansabha->vidhansabha}</option>");
    }

    public function getDistrictOptionsFromId($district_id)
    {
        $district = District::find($district_id);
        return response("<option value='{$district->district_id}' selected>{$district->district_name}</option>");
    }

    public function getDivisionOptionsFromId($division_id)
    {
        $division = Division::find($division_id);
        return response("<option value='{$division->division_id}' selected>{$division->division_name}</option>");
    }

    public function messageSent($complaint_number, $mobile)
    {
        if (!preg_match('/^[6-9][0-9]{9}$/', $mobile)) {
            \Log::error('Invalid mobile format: ' . $mobile);
            return 0;
        }

        $senderId = "EBIZTL";
        $flow_id = '686e28df91e9813c053cb273';

        $recipients = [[
            "mobiles" => "91" . $mobile,
            "otp" => $complaint_number,
        ]];

        $postData = [
            "sender" => $senderId,
            "flow_id" => $flow_id,
            "recipients" => $recipients
        ];

        \Log::info('Sending SMS with payload: ' . json_encode($postData));

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

        \Log::info('MSG91 response: ' . $output);

        if ($err) {
            \Log::error('SMS send error: ' . $err);
            return 0;
        }

        $response = json_decode($output, true);

        if ($response && isset($response['type']) && $response['type'] === 'success') {
            \Log::info("Complaint number sent to $mobile: $complaint_number");
            return 1;
        } else {
            \Log::error("Failed to send complaint number to $mobile. Response: " . $output);
            return 0;
        }
    }

    public function operator_complaints_show($id)
    {
        $complaint = Complaint::with(
            'replies',
            'registration',
            'division',
            'district',
            'vidhansabha',
            'mandal',
            'gram',
            'polling',
            'area',
            'registrationDetails'
        )->findOrFail($id);

        return view('operator/details_complaints', [
            'complaint' => $complaint,
        ]);
    }

    public function operatorReply(Request $request, $id)
    {
        $request->validate([
            'cmp_reply' => 'required|string',
            'cmp_status' => 'required|in:1,2,3,4',
            'cb_photo.*' => 'nullable|image|mimes:jpeg,png,jpg,bmp,gif|max:2048',
            'ca_photo.*' => 'nullable|image|mimes:jpeg,png,jpg,bmp,gif|max:2048',
            'c_video' => 'nullable|url|max:255',
        ]);

        $reply = new Reply();
        $reply->complaint_id = $id;
        $reply->complaint_reply = $request->cmp_reply;
        $reply->reply_from = auth()->id() ?? 2;
        $reply->reply_date = now();

        if ($request->filled('c_video')) {
            $reply->c_video = $request->c_video;
        }

        $reply->save();

        Complaint::where('complaint_id', $id)->update([
            'complaint_status' => $request->cmp_status
        ]);

        if ($request->hasFile('cb_photo')) {
            $before = $request->file('cb_photo')[0];
            $beforeName = 'before_' . time() . '.' . $before->getClientOriginalExtension();
            $destinationPath = public_path('assets/upload_complaint');
            $before->move($destinationPath, $beforeName);
            $reply->cb_photo = 'assets/upload_complaint/' . $beforeName;
        }

        if ($request->hasFile('ca_photo')) {
            $after = $request->file('ca_photo')[0];
            $afterName = 'after_' . time() . '.' . $after->getClientOriginalExtension();
            $destinationPath = public_path('assets/upload_complaint');
            $after->move($destinationPath, $afterName);
            $reply->ca_photo = 'assets/upload_complaint/' . $afterName;
        }

        $reply->save();

        return redirect()->route('operator_complaint.view', $id)
            ->with('success', 'जवाब प्रस्तुत किया गया और शिकायत अपडेट की गई');
    }
}
