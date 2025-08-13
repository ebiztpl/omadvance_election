<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\District;
use App\Models\Department;
use App\Models\ComplaintReply;
use App\Models\User;
use App\Models\Adhikari;
use App\Models\Subject;
use App\Models\Designation;
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

class MemberController extends Controller
{
    public function dashboard()
    {
        return view('member/dashboard');
    }

    public function complaint(Request $request)
    {
        $registrationId = session('registration_id');

        $nagarId = DB::table('assign_position')
            ->where('member_id', $registrationId)
            ->latest('post_date')
            ->value('refrence_id');

        if (!$nagarId) {
            return redirect()->back()->with('error', 'कोई नगर केंद्र नहीं मिला।');
        }

        $pollingCenters = DB::table('gram_polling')
            ->where('nagar_id', $nagarId)
            ->pluck('gram_polling_id');

        $areas = DB::table('area_master')
            ->whereIn('polling_id', $pollingCenters)
            ->get();

        return view('member/complaint', compact('areas'));
    }


    public function store(Request $request)
    {
        $request->validate([
            'area_id' => 'required|integer',
            'video' => 'required|file|mimetypes:video/*|max:2560000',
        ], [
            'video.max' => 'वीडियो फ़ाइल अधिकतम 2.5GB हो सकती है।',
            'video.mimetypes' => 'केवल वीडियो फ़ॉर्मेट स्वीकार्य है।',
        ]);

        $registrationId = session('registration_id');
        if (!$registrationId) {
            return back()->with('error', 'Session expired. Please log in again.');
        }

        $areaId = $request->area_id;

        $pollingId = DB::table('area_master')
            ->where('area_id', $areaId)
            ->value('polling_id');

        $nagarId = DB::table('gram_polling')
            ->where('gram_polling_id', $pollingId)
            ->value('nagar_id');

        if (!$nagarId) {
            return back()->with('error', 'नगर नहीं मिला।');
        }

        $mandalId = DB::table('nagar_master')
            ->where('nagar_id', $nagarId)
            ->value('mandal_id');

        $vidhansabhaId = DB::table('mandal')
            ->where('mandal_id', $mandalId)
            ->value('vidhansabha_id');

        $vidhansabhaInfo = DB::table('vidhansabha_loksabha')
            ->where('vidhansabha_id', $vidhansabhaId)
            ->first();

        $districtId = $vidhansabhaInfo->district_id ?? null;

        $divisionId = DB::table('district_master')
            ->where('district_id', $districtId)
            ->value('division_id');

        $ref = '00';
        if ($vidhansabhaId == 50) $ref = '19';
        elseif ($vidhansabhaId == 49) $ref = '18';

        $rndno = date('dHi', mt_rand(strtotime('2018-01-01'), time()));
        $complaintNumber = 'BJS' . $ref . '-' . $rndno;

        $videoFilename = '';
        if ($request->hasFile('video')) {
            $file = $request->file('video');
            $extension = $file->getClientOriginalExtension();

            if (in_array(strtolower($extension), ['exe', 'php', 'js'])) {
                return back()->with('error', 'This file type is not allowed.');
            }

            $videoFilename = time() . '_' . Str::random(6) . '.' . $extension;
            $file->move(public_path('assets/upload/complaints'), $videoFilename);
        }

        $role = session('admin_role');
        $type = ($role === 'फ़ील्ड') ? 1 : 2;

        $mobile = DB::table('registration_form')
            ->where('registration_id', $registrationId)
            ->value('mobile1');

        $complaint = Complaint::create([
            'user_id' => 0,
            'complaint_number' => $complaintNumber,
            'video' => $videoFilename,
            'polling_id' => $pollingId,
            'area_id' => $areaId,
            'mandal_id' => $mandalId,
            'vidhansabha_id' => $vidhansabhaId,
            'district_id' => $districtId,
            'division_id' => $divisionId,
            'father_name' => '',
            'mobile_number' => $mobile,
            'complaint_type' => 'समस्या',
            'complaint_designation' => '',
            'type' => $type,
            'complaint_created_by' => $registrationId,
            'complaint_status' => 1,
            'issue_title' => '',
            'issue_description' => '',
            'issue_attachment' => $videoFilename,
            'name' => '',
            'email' => '',
            'gram_id' => $nagarId ?? null,
            'news_time' => null,
            'posted_date' => now(),
        ]);


        Reply::create([
            'complaint_id' => $complaint->complaint_id,
            'forwarded_to' => 6,
            'reply_from' => 0,
            'reply_date' => now(),
            'selected_reply' => Null,
            'complaint_status' => 1,
            'complaint_reply' => 'शिकायत दर्ज की गई है।',
        ]);


        $message = 'आपकी शिकायत सफलतापूर्वक दर्ज की गई है। शिकायत संख्या: ' . $complaintNumber;
        $this->messageSent($complaintNumber, $mobile);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'redirect' => route('member.complaint'),
            ]);
        }

        return redirect()->route('member.complaint')->with('success', $message);
    }






    public function index()
    {
        $states = State::orderBy('name')->get();
        $divisions = Division::orderBy('division_name')->get();
        return view('member/complaints', compact('states', 'divisions'));
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
        $pollings = Polling::where('mandal_id', $mandal_id)->get();
        return response()->json($pollings->map(function ($p) {
            return "<option value='{$p->gram_polling_id}'>{$p->polling_name} ({$p->polling_no})</option>";
        }));
    }

    public function getAreas($polling_id)
    {
        $areas = Area::where('polling_id', $polling_id)->get();
        return response()->json($areas->map(function ($a) {
            return "<option value='{$a->area_id}'>{$a->area_name}</option>";
        }));
    }


    public function pollingsfetch($mandal_id)
    {
        $pollings = Polling::where('mandal_id', $mandal_id)->get([
            'gram_polling_id',
            'polling_name',
            'polling_no'
        ]);

        return response()->json($pollings);
    }

    public function areasfetch($polling_id)
    {
        $areas = Area::where('polling_id', $polling_id)->get([
            'area_id',
            'area_name'
        ]);

        return response()->json($areas);
    }

    public function getSubjects($department_id)
    {
        $subjects = Subject::where('department_id', $department_id)->get(['subject_id', 'subject']);
        return response()->json($subjects);
    }


    public function getgramPollings($nagar_id)
    {
        $pollings = Polling::where('nagar_id', $nagar_id)->get([
            'gram_polling_id',
            'polling_name',
            'polling_no'
        ]);

        return response()->json($pollings);
    }

    // public function store(Request $request)
    // {
    //     $request->validate([
    //         'txtname' => 'required|string|max:255',
    //         'mobile' => 'nullable|string|regex:/^[0-9]{10,15}$/',
    //         'division_id' => 'required|integer',
    //         'voter' => 'required|string|max:255',
    //         'txtdistrict_name' => 'required|integer',
    //         'txtvidhansabha' => 'required|integer',
    //         'txtmandal' => 'required|integer',
    //         'txtgram' => 'required|integer',
    //         'txtpolling' => 'required|integer',
    //         'txtarea' => 'required|integer',
    //         'txtaddress' => 'nullable|string|max:1000',
    //         'CharCounter' => 'required|string|max:100',
    //         'NameText' => 'required|string|max:2000',
    //         'type' => 'required|string',
    //         'department' => 'nullable',
    //         'from_date' => 'nullable|date',
    //         'program_date' => 'nullable|date',
    //         'to_date' => 'nullable',
    //         'file_attach' => 'nullable|file|max:20480',
    //     ]);

    //     $userId = session('registration_id');

    //     if (!$userId) {
    //         return back()->with('error', 'User session expired. Please log in again.');
    //     }

    //     $userAreaId = DB::table('step2')
    //         ->where('registration_id', $userId)
    //         ->value('area_id');

    //     if ((int)$userAreaId !== (int)$request->txtarea) {
    //         return back()->with('error', 'आप केवल अपने क्षेत्र के लिए ही शिकायत दर्ज कर सकते हैं।');
    //     }

    //     $vidhansabha = (int) $request->txtvidhansabha;
    //     $ref = '00';
    //     if ($vidhansabha === 50) {
    //         $ref = '19';
    //     } elseif ($vidhansabha === 49) {
    //         $ref = '18';
    //     }

    //     $randomTimestamp = mt_rand(strtotime('2018-01-01'), time());
    //     $rndno = date('dHi', $randomTimestamp);
    //     $complaint_number = 'BJS' . $ref . '-' . $rndno;

    //     $attachment = '';
    //     if ($request->hasFile('file_attach')) {
    //         $file = $request->file('file_attach');
    //         $extension = $file->getClientOriginalExtension();
    //         $blocked = ['exe', 'php', 'js'];

    //         if (in_array(strtolower($extension), $blocked)) {
    //             return back()->with('error', 'This file type is not allowed.');
    //         }

    //         $filename = time() . '_' . Str::random(6) . '.' . $extension;
    //         $file->move(public_path('assets/upload/complaints'), $filename);
    //         $attachment = $filename;
    //     }

    //     $mobile = RegistrationForm::where('registration_id', $userId)->value('mobile1');

    //     $complaint = Complaint::create([
    //         'user_id' => session('registration_id'),
    //         'name' => $request->txtname,
    //         'mobile_number' => RegistrationForm::where('registration_id', session('registration_id'))->value('mobile1'),
    //         'email' => $request->mobile,
    //         'voter_id' => $request->voter,
    //         'complaint_type' => $request->type,
    //         'issue_title' => $request->CharCounter,
    //         'issue_description' => $request->NameText,
    //         'address' => $request->txtaddress,
    //         'division_id' => $request->division_id,
    //         'district_id' => $request->txtdistrict_name,
    //         'vidhansabha_id' => $vidhansabha,
    //         'mandal_id' => $request->txtmandal,
    //         'gram_id' => $request->txtgram,
    //         'polling_id' => $request->txtpolling,
    //         'area_id' => $request->txtarea,
    //         'issue_attachment' => $attachment,
    //         'complaint_number' => $complaint_number,
    //         'complaint_department' => $request->department ?? '',
    //         'news_date' => $request->from_date,
    //         'complaint_status' => 5,
    //         'program_date' => $request->program_date,
    //         'news_time' => $request->filled('to_date')  ? $request->to_date : '00:00',
    //         'posted_date' => now(),
    //     ]);

    //     $message = 'आपकी शिकायत सफलतापूर्वक दर्ज की गई है। शिकायत संख्या: ' . $complaint_number;
    //     $this->messageSent($complaint_number, $mobile);

    //     return redirect()->route('complaint.index')->with('success', 'शिकायत सफलतापूर्वक दर्ज की गई है। आपकी शिकायत संख्या है: ' . $complaint_number);
    // }


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




    public function complaint_index(Request $request)
    {
        $registrationId = session('registration_id');

        if (!$registrationId) {
            return redirect()->route('login')->with('error', 'कृपया पहले लॉगिन करें।');
        }


        $query = Complaint::with(['polling', 'area', 'replies.forwardedToManager'])
            ->where('complaint_created_by', $registrationId)
            ->where('type', 1);


        if ($request->filled('complaint_status')) {
            $query->where('complaint_status', $request->complaint_status);
        }

        if ($request->filled('complaint_type')) {
            $query->where('complaint_type', $request->complaint_type);
        } else {
            // Apply default filter for initial load or sabhi
            $query->where('complaint_type', 'समस्या');
        }

        // if ($request->filled('department_id')) {
        //     $query->where('complaint_department', $request->department_id);
        // }

        // if ($request->filled('subject_id')) {
        //     $query->where('issue_title', $request->subject_id);
        // }

        if ($request->filled('department_id')) {
            $department = Department::find($request->department_id);
            if ($department) {
                $query->where('complaint_department', $department->department_name);
            }
        }

        if ($request->filled('admin_id')) {
            $query->whereHas('latestReply', function ($q) use ($request) {
                $q->where('forwarded_to', $request->admin_id);
            });
        }

        if ($request->filled('reply_id')) {
            $query->whereHas('replies', function ($q) use ($request) {
                $q->where('selected_reply', $request->reply_id);
            });
        }

        if ($request->filled('subject_id')) {
            $subject = Subject::find($request->subject_id);
            if ($subject) {
                $query->where('issue_title', $subject->subject);
            }
        }

        if ($request->filled('mandal_id')) {
            $query->where('mandal_id', $request->mandal_id);
        }

        if ($request->filled('gram_id')) {
            $query->where('gram_id', $request->gram_id);
        }

        if ($request->filled('polling_id')) {
            $query->where('polling_id', $request->polling_id);
        }

        if ($request->filled('area_id')) {
            $query->where('area_id', $request->area_id);
        }

        if ($request->filled('from_date')) {
            $query->whereDate('posted_date', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('posted_date', '<=', $request->to_date);
        }

        $complaints = $query->orderBy('posted_date', 'desc')->get();

        foreach ($complaints as $complaint) {
            if (!in_array($complaint->complaint_status, [4, 5])) {
                $complaint->pending_days = Carbon::parse($complaint->posted_date)->diffInDays(now());
            } else {
                $complaint->pending_days = 0;
            }

            $lastReply = $complaint->replies
                ->whereNotNull('forwarded_to')
                ->sortByDesc('reply_date')
                ->first();

            $complaint->forwarded_to_name = $lastReply?->forwardedToManager?->admin_name ?? '-';
            $complaint->forwarded_reply_date = $lastReply?->reply_date?->format('d-m-Y h:i A') ?? '-';
        }

        if ($request->ajax()) {
            $html = '';

            foreach ($complaints as $index => $complaint) {
                $html .= '<tr>';
                $html .=  '<td>
        <strong>शिकायत क्र.: </strong>' . ($complaint->complaint_number ?? 'N/A') . '<br>
        <strong>नाम: </strong>' . ($complaint->name ?? 'N/A') . '<br>
        <strong>मोबाइल: </strong>' . ($complaint->mobile_number ?? '') . '<br>
        <strong>पुत्र श्री: </strong>' . ($complaint->father_name ?? '') . '<br>
        <strong>रेफरेंस: </strong>' . ($complaint->reference_name ?? '') . '<br><br>
        <strong>स्थिति: </strong>' . strip_tags($complaint->statusTextPlain()) . '
        </td>';

                $html .= '<td title="
            विभाग: ' . ($complaint->division->division_name ?? 'N/A') . '
            जिला: ' . ($complaint->district->district_name ?? 'N/A') . '
            विधानसभा: ' . ($complaint->vidhansabha->vidhansabha ?? 'N/A') . '
            मंडल: ' . ($complaint->mandal->mandal_name ?? 'N/A') . '
            नगर/ग्राम: ' . ($complaint->gram->nagar_name ?? 'N/A') . '
            मतदान केंद्र: ' . ($complaint->polling->polling_name ?? 'N/A') . ' (' . ($complaint->polling->polling_no ?? 'N/A') . ')
            क्षेत्र: ' . ($complaint->area->area_name ?? 'N/A') . '">
            ' . ($complaint->division->division_name ?? 'N/A') . '<br>' .
                    ($complaint->district->district_name ?? 'N/A') . '<br>' .
                    ($complaint->vidhansabha->vidhansabha ?? 'N/A') . '<br>' .
                    ($complaint->mandal->mandal_name ?? 'N/A') . '<br>' .
                    ($complaint->gram->nagar_name ?? 'N/A') . '<br>' .
                    ($complaint->polling->polling_name ?? 'N/A') . ' (' . ($complaint->polling->polling_no ?? 'N/A') . ')<br>' .
                    ($complaint->area->area_name ?? 'N/A') .
                    '</td>';

                $html .= '<td>' . ($complaint->complaint_department ?? 'N/A') . '</td>';
                $html .= '<td>
                 <strong>तिथि: ' . \Carbon\Carbon::parse($complaint->posted_date)->format('d-m-Y h:i A') . '</strong><br>';
                if ($complaint->complaint_status == 4) {
                    $html .= 'पूर्ण';
                } elseif ($complaint->complaint_status == 5) {
                    $html .= 'रद्द';
                } else {
                    $html .= $complaint->pending_days . ' दिन';
                }
                $html .= '</td>';

                $html .= '<td>' . ($latestReply->review_date ?? 'N/A') . '</td>';

                // Importance
                $html .= '<td>' . ($complaint->latestReply?->importance ?? 'N/A') . '</td>';

                // Criticality
                $html .= '<td>' . ($complaint->latestReply?->criticality ?? 'N/A') . '</td>';

                // Status Text
                // $html .= '<td>' . strip_tags($complaint->statusTextPlain()) . '</td>';
                $html .= '<td>' . ($complaint->registrationDetails->name ?? '') . '</td>';

                $html .= '<td>' . $complaint->forwarded_to_name . '<br>' . $complaint->forwarded_reply_date . '</td>';

                $html .= '<td><a href="' . route('complaint.show', $complaint->complaint_id) . '" class="btn btn-sm btn-primary" style="white-space: nowrap;">क्लिक करें</a></td>';

                $html .= '</tr>';
            }

            return response()->json([
                'html' => $html,
                'count' => $complaints->count(),
            ]);
        }

        $mandals = Mandal::where('vidhansabha_id', 49)->get();
        $grams = $request->mandal_id ? Nagar::where('mandal_id', $request->mandal_id)->get() : collect();
        $pollings = $request->gram_id ? Polling::where('nagar_id', $request->gram_id)->get() : collect();
        $areas = $request->polling_id ? Area::where('polling_id', $request->polling_id)->get() : collect();
        $departments = Department::all();
        $replyOptions = ComplaintReply::all();
        $subjects = $request->department_id ? Subject::where('department_id', $request->department_id)->get() : collect();
        $managers = User::where('role', 2)->get();

        return view('member/view_complaints', compact(
            'complaints',
            'mandals',
            'grams',
            'pollings',
            'areas',
            'departments',
            'subjects',
            'replyOptions',
            'managers'
        ));
    }

    public function complaint_show($id)
    {
        $complaint = Complaint::with(
            'replies.predefinedReply',
            'replies.forwardedToManager',
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

        $replyOptions = ComplaintReply::all();
        $managers = User::where('role', 2)->get();

        return view('member/details_complaints', [
            'complaint' => $complaint,
            'replyOptions' => $replyOptions,
            'managers' => $managers,
        ]);
    }

    public function postReply(Request $request, $id)
    {
        $request->validate([
            'cmp_reply' => 'required|string',
            'cmp_status' => 'required|in:1,2,3,4,5',
            'forwarded_to' => [
                'nullable',
                'exists:admin_master,admin_id'
            ],
            'selected_reply' => [
                'nullable',
                function ($attribute, $value, $fail) {
                    if ((int)$value !== 0 && !\App\Models\ComplaintReply::where('reply_id', $value)->exists()) {
                        $fail('The selected reply is invalid.');
                    }
                }
            ],
            'cb_photo.*' => 'nullable|image|mimes:jpeg,png,jpg,bmp,gif|max:2048',
            'ca_photo.*' => 'nullable|image|mimes:jpeg,png,jpg,bmp,gif|max:2048',
            'c_video' => 'nullable|url|max:255',
            'contact_status' => 'nullable|string',
            'review_date' => 'nullable|date',
            'importance' => 'nullable|string',
            'criticality' => 'nullable|string',
        ]);

        $reply = new Reply();
        $reply->complaint_id = $id;
        $reply->complaint_reply = $request->cmp_reply;
        $reply->selected_reply = $request->selected_reply ?? 0;
        $reply->reply_from = auth()->id() ?? 2;
        $reply->reply_date = now();
        $reply->complaint_status = $request->cmp_status;
        $reply->review_date = $request->review_date ?? null;
        $reply->importance = $request->importance ?? null;
        $reply->criticality = $request->criticality ?? null;

        if ($request->filled('c_video')) {
            $reply->c_video = $request->c_video;
        }

        if (in_array((int)$request->cmp_status, [4, 5])) {
            $reply->forwarded_to = 0;
        } else {
            $reply->forwarded_to = $request->forwarded_to;
        }

        if ($request->filled('contact_status')) {
            $reply->contact_status = $request->contact_status;
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

        return redirect()->route('complaints.view', $id)
            ->with('success', 'जवाब प्रस्तुत किया गया और शिकायत अपडेट की गई');
    }
    
}
