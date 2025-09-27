<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Intervention\Image\Laravel\Facades\Image;
use Intervention\Image\Encoders\JpegEncoder;
use Intervention\Image\Encoders\PngEncoder;
use Intervention\Image\ImageManager;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use App\Models\District;
use App\Models\RegistrationForm;
use App\Models\Step2;
use App\Models\Step3;
use App\Models\Step4;
use App\Models\ComplaintAttachment;
use setasign\Fpdi\Fpdi;
use setasign\Fpdi\PdfReader\PdfReader;
use App\Models\Department;
use App\Models\ComplaintReply;
use App\Models\FollowupStatus;
use App\Models\Adhikari;
use App\Models\Subject;
use App\Models\Designation;
use App\Models\Mandal;
use App\Models\Nagar;
use App\Models\Area;
use App\Models\Polling;
use App\Models\Jati;
use App\Models\Reply;
use App\Models\Level;
use App\Models\Position;
use App\Models\User;
use Illuminate\Support\Str;
use App\Models\Complaint;
use App\Models\City;
use App\Models\State;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;
use App\Models\Division;
use Mpdf\Mpdf;
use App\Models\VidhansabhaLokSabha;
use Illuminate\Support\Facades\Response;

class OperatorController extends Controller
{
    public function dashboard()
    {
        return view('operator/dashboard');
    }

    public function getComplaintSummary(Request $request)
    {
        $from = $request->query('from');
        $to = $request->query('to');
        $now = Carbon::now();
        $userId = session('user_id');

        $ranges = [];

        // If custom date filter is provided, use it as a single range
        if ($from && $to) {
            $start = Carbon::parse($from)->startOfDay();
            $end = Carbon::parse($to)->endOfDay();
            $label = "$from – $to";
            $ranges[$label] = [$start, $end];
        } else {
            // Default ranges
            $ranges = [
                'आज' => [$now->copy()->startOfDay(), $now->copy()->endOfDay()],
                'कल' => [$now->copy()->subDay()->startOfDay(), $now->copy()->subDay()->endOfDay()],
                'इस सप्ताह' => [$now->copy()->startOfWeek(), $now->copy()->endOfWeek()],
                'इस माह' => [$now->copy()->startOfMonth(), $now],
                'कुल' => [
                    Carbon::create(2000, 1, 1)->format('Y-m-d H:i:s'),
                    $now->format('Y-m-d H:i:s')
                ]
            ];
        }

        $sectionMap = [
            'आज' => 'today',
            'कल' => 'yesterday',
            'इस सप्ताह' => 'current-week',
            'इस माह' => 'current-month',
            'कुल' => 'all',
        ];

        $result = [];

        foreach ($ranges as $label => [$start, $end]) {
            $records = DB::table('complaint')
                ->selectRaw("
                SUM(CASE WHEN complaint_type = 'समस्या' THEN 1 ELSE 0 END) as samasya,
                SUM(CASE WHEN complaint_type = 'विकास' THEN 1 ELSE 0 END) as vikash,
                SUM(CASE WHEN complaint_type = 'अशुभ सुचना' THEN 1 ELSE 0 END) as asubh,
                SUM(CASE WHEN complaint_type = 'शुभ सुचना' THEN 1 ELSE 0 END) as subh
            ")
                ->where('complaint_created_by', $userId)
                ->where('type', 2)
                ->whereBetween('posted_date', [$start, $end])
                ->first();

            $entry = [
                'samay' => $label,
                'section' => $sectionMap[$label] ?? null,
                'samasya' => $records ? ($records->samasya + $records->vikash) : 0,
                'samasya_details' => [
                    'samasya' => $records->samasya ?? 0,
                    'vikash'  => $records->vikash ?? 0,
                ],
                'suchna' => $records ? ($records->subh + $records->asubh) : 0,
                'suchna_details' => [
                    'subh'  => $records->subh ?? 0,
                    'asubh' => $records->asubh ?? 0,
                ],
            ];

            $result[] = $entry;
        }

        return response()->json($result);
    }



    public function getTodaysFollowups()
    {
        $todayStart = Carbon::today()->startOfDay();
        $todayEnd   = Carbon::today()->endOfDay();
        $userId     = session('user_id');

        $todaysFollowups = FollowupStatus::whereBetween('followup_date', [$todayStart, $todayEnd])
            ->where('followup_created_by', $userId)
            ->get()
            ->groupBy('complaint_reply_id');

        $newFollowups = 0;
        $newPending = 0;
        $newCompleted = 0;
        $oldFollowups = 0;
        $oldPending = 0;
        $oldCompleted = 0;

        foreach ($todaysFollowups as $replyId => $followups) {
            $firstFollowupToday = $followups->sortBy('followup_date')->first();

            $hadBefore = FollowupStatus::where('complaint_reply_id', $replyId)
                ->where('followup_date', '<', $todayStart)
                ->exists();

            if ($hadBefore) {
                $oldFollowups++;
                if ($firstFollowupToday->followup_status == 1) {
                    $oldPending++;
                }

                if ($firstFollowupToday->followup_status == 2) {
                    $oldCompleted++;
                }
            } else {
                $newFollowups++;
                if ($firstFollowupToday->followup_status == 1) {
                    $newPending++;
                }

                if ($firstFollowupToday->followup_status == 2) {
                    $newCompleted++;
                }
            }
        }

        return response()->json([
            'new_followups' => $newFollowups,
            'new_pending'   => $newPending,
            'new_completed'   => $newCompleted,
            'old_followups' => $oldFollowups,
            'old_pending'   => $oldPending,
            'old_completed'   => $oldCompleted,
        ]);
    }

    public function getFollowupSummary(Request $request)
    {
        $from = $request->query('from');
        $to = $request->query('to');
        $now = Carbon::now();
        $userId = session('user_id');

        $ranges = [];

        if ($from && $to) {
            $start = Carbon::parse($from)->startOfDay();
            $end = Carbon::parse($to)->endOfDay();
            $ranges["$from – $to"] = [$start, $end];
        } else {
            $ranges = [
                'आज' => [$now->copy()->startOfDay(), $now->copy()->endOfDay()],
                'कल' => [$now->copy()->subDay()->startOfDay(), $now->copy()->subDay()->endOfDay()],
                'इस सप्ताह' => [$now->copy()->startOfWeek(), $now->copy()->endOfWeek()],
                'इस माह' => [$now->copy()->startOfMonth(), $now],
                'कुल' => [Carbon::create(2000, 1, 1)->format('Y-m-d H:i:s'), $now->format('Y-m-d H:i:s')],
            ];
        }

        $result = [];

        foreach ($ranges as $label => [$start, $end]) {
            $latestFollowups = FollowupStatus::select('complaint_reply_id', 'followup_status')
                ->where('followup_created_by', $userId)
                ->whereBetween('followup_date', [$start, $end])
                ->orderBy('followup_date', 'desc')
                ->get()
                ->groupBy('complaint_reply_id')
                ->map(function ($group) {
                    return $group->first();
                });

            $completed = $latestFollowups->where('followup_status', 2)->count();
            $pending = $latestFollowups->where('followup_status', 1)->count();

            $result[] = [
                'samay' => $label,
                'completed' => $completed,
                'pending' => $pending,
            ];
        }

        return response()->json($result);
    }





    public function index()
    {
        $states = State::orderBy('name')->get();
        $divisions = Division::all();
        $defaultVidhansabha = 49;
        $mandalIds = Mandal::where('vidhansabha_id', $defaultVidhansabha)->pluck('mandal_id');

        $nagars = Nagar::with('mandal')
            ->whereIn('mandal_id', $mandalIds)
            ->orderBy('nagar_name')
            ->get();

        $departments = Department::all();
        $jatis = Jati::all();

        return view('operator/complaints', compact('states', 'nagars',  'departments', 'jatis', 'divisions'));
    }

    public function suchnaIndex()
    {
        $states = State::orderBy('name')->get();
        $divisions = Division::all();
        $defaultVidhansabha = 49;
        $mandalIds = Mandal::where('vidhansabha_id', $defaultVidhansabha)->pluck('mandal_id');

        $nagars = Nagar::with('mandal')
            ->whereIn('mandal_id', $mandalIds)
            ->orderBy('nagar_name')
            ->get();

        $departments = Department::all();
        $jatis = Jati::all();


        return view('operator/suchna', compact('states', 'nagars',  'departments', 'jatis', 'divisions'));
    }

    public function getVoter(Request $request)
    {
        $request->validate([
            'name'        => 'required|string',
            'father_name' => 'required|string',
            'area_id'     => 'required|integer',
        ]);

        $voter = DB::table('registration_form')
            ->join('step2', 'registration_form.registration_id', '=', 'step2.registration_id')
            ->where('registration_form.name', $request->name)
            ->where('registration_form.father_name', $request->father_name)
            ->where('step2.area_id', $request->area_id)
            ->select('registration_form.voter_id', 'registration_form.name', 'registration_form.father_name')
            ->first();

        if (!$voter) {
            return response()->json([
                'status'  => 'success',
                'data'    => null,
                'message' => 'No voter found, you can continue manually.'
            ]);
        }

        return response()->json([
            'status' => 'success',
            'data'   => $voter
        ]);
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'txtname' => 'required|string|max:255',
                'mobile' => 'required|string|regex:/^[0-9]{10,15}$/',
                'father_name' => 'required|string|max:255',
                'reference' => 'nullable|string|max:255',
                'division_id' => 'required|integer',
                'voter' => 'required|string|max:255',
                'txtdistrict_name' => 'required|integer',
                'txtvidhansabha' => 'required|integer',
                'txtgram' => 'nullable|integer',
                'txtpolling' => 'nullable|integer',
                'txtaddress' => 'nullable|string|max:1000',
                'CharCounter' => 'nullable|string|max:100',
                'NameText' => 'required|string|max:2000',
                'type' => 'required|string',
                'department' => 'nullable',
                'jati' => 'nullable',
                'post' => 'nullable',
                'from_date' => 'nullable|date',
                'program_date' => 'nullable|date',
                'to_date' => 'nullable',
                'file_attach' => 'nullable|file|mimes:jpg,jpeg,png,mp4,mov,avi',
            ], [
                'file_attach.max' => 'फ़ाइल का आकार 15MB से अधिक नहीं होना चाहिए।',
                'file_attach.mimes' => 'केवल JPG, PNG या वीडियो फाइलें ही अपलोड करें।'
            ]);
        } catch (ValidationException $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'errors' => $e->errors()
                ], 422);
            }
            throw $e;
        }

        $userId = session('user_id');

        if (!$userId) {
            return response()->json([
                'success' => false,
                'errors' => ['session' => ['User session expired. Please log in again.']]
            ], 401);
        }

        $nagar = Nagar::with('mandal')->find($request->txtgram);
        $mandal_id = $nagar?->mandal?->mandal_id;

        $polling = Polling::with('area')->where('gram_polling_id', $request->txtpolling)->first();
        $area_id = $polling?->area?->area_id;

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
            $extension = strtolower($file->getClientOriginalExtension());

            // Block dangerous file types
            $blocked = ['exe', 'php', 'js', 'sh', 'bat'];
            if (in_array($extension, $blocked)) {
                return response()->json([
                    'success' => false,
                    'errors' => ['file_attach' => ['यह फ़ाइल प्रकार अनुमति नहीं है।']]
                ], 422);
            }

            $filename = time() . '_' . Str::random(6) . '.' . $extension;
            $outputPath = public_path('assets/upload/complaints/' . $filename);

            // Handle Images (compress)
            if (in_array($extension, ['jpg', 'jpeg', 'png'])) {
                if ($file->getSize() > 2 * 1024 * 1024) {
                    return response()->json([
                        'success' => false,
                        'errors' => ['file_attach' => ['छवि फ़ाइल अधिकतम 2MB हो सकती है।']]
                    ], 422);
                }

                $image = Image::read($file->getRealPath());

                if ($extension === 'png') {
                    $encoder = new PngEncoder(6);
                } else {
                    $encoder = new JpegEncoder(40);
                }

                $image->encode($encoder)->save($outputPath);
            }

            // Handle Videos (compress)
            elseif (in_array($extension, ['mp4', 'mov', 'avi', 'mkv'])) {
                if ($file->getSize() > 15 * 1024 * 1024) { // 15 MB
                    return response()->json([
                        'success' => false,
                        'errors' => ['file_attach' => ['वीडियो फ़ाइल अधिकतम 15MB हो सकती है।']]
                    ], 422);
                }

                $tempPath = $file->store('temp_videos');

                $ffmpeg = \FFMpeg\FFMpeg::create([
                    'ffmpeg.binaries'  => base_path(env('FFMPEG_PATH')),
                    'ffprobe.binaries' => base_path(env('FFPROBE_PATH')),
                    'timeout'          => 3600,
                    'ffmpeg.threads'   => 2,
                ]);

                $video = $ffmpeg->open(storage_path('app/' . $tempPath));
                $format = new \FFMpeg\Format\Video\X264('aac', 'libx264');
                $format->setKiloBitrate(300);
                $format->setAdditionalParameters(['-preset', 'ultrafast']);

                $video->save($format, $outputPath);

                Storage::delete($tempPath);
            }

            // Unsupported file types
            else {
                return response()->json([
                    'success' => false,
                    'errors' => ['file_attach' => ['केवल छवि (JPG, PNG) या वीडियो (MP4, MOV, AVI, MKV) फ़ाइलें अपलोड की जा सकती हैं।']]
                ], 422);
            }

            $attachment = $filename;
        }


        $complaint = Complaint::create([
            'user_id' => $userId,
            'name' => $request->txtname,
            'mobile_number' => $request->mobile,
            'father_name' => $request->father_name,
            'reference_name' => $request->reference,
            'email' => $request->mobile,
            'voter_id' => $request->voter,
            'complaint_type' => $request->type,
            'issue_title' => $request->CharCounter ?? 'N/A',
            'issue_description' => $request->NameText,
            'address' => $request->txtaddress,
            'division_id' => $request->division_id,
            'district_id' => $request->txtdistrict_name,
            'vidhansabha_id' => $vidhansabha,
            'mandal_id' => $mandal_id ?? null,
            'gram_id' => $request->txtgram ?? null,
            'polling_id' => $request->txtpolling ?? null,
            'area_id' => $area_id ?? null,
            'issue_attachment' => $attachment,
            'complaint_number' => $complaint_number,
            'complaint_department' => $request->department ?? '',
            'jati_id' => $request->jati ?? null,
            'complaint_designation' => $request->post ?? '',
            'news_date' => $request->from_date,
            'complaint_status' => 1,
            'program_date' => $request->program_date,
            'complaint_created_by'  => $userId,
            'type' => 2,
            'news_time' => $request->filled('to_date') ? $request->to_date : '00:00',
            'posted_date' => now(),
        ]);

        Reply::create([
            'complaint_id' => $complaint->complaint_id,
            'forwarded_to' => 6,
            'complaint_status' => 1,
            'reply_from' => null,
            'reply_date' => now(),
            'complaint_reply' => 'शिकायत दर्ज की गई है।',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'शिकायत सफलतापूर्वक दर्ज की गई है। आपकी शिकायत संख्या है: ' . $complaint_number,
        ]);
    }


    // public function suchnaStore(Request $request)
    // {
    //     $request->validate([
    //         'txtname' => 'required|string|max:255',
    //         'mobile' => 'required|string|regex:/^[0-9]{10,15}$/',
    //         'father_name' => 'required|string|max:255',
    //         'reference' => 'nullable|string|max:255',
    //         'division_id' => 'required|integer',
    //         'voter' => 'required|string|max:255',
    //         'txtdistrict_name' => 'required|integer',
    //         'txtvidhansabha' => 'required|integer',
    //         // 'txtmandal' => 'required|integer',
    //         'txtgram' => 'required|integer',
    //         'txtpolling' => 'required|integer',
    //         // 'txtarea' => 'required|integer',
    //         'txtaddress' => 'nullable|string|max:1000',
    //         'CharCounter' => 'nullable|string|max:100',
    //         'NameText' => 'required|string|max:2000',
    //         'type' => 'required|string',
    //         'department' => 'nullable',
    //         'post' => 'nullable',
    //         'from_date' => 'nullable|date',
    //         'program_date' => 'nullable|date',
    //         'to_date' => 'nullable',
    //         'file_attach' => 'nullable|file|max:20480'
    //     ]);

    //     $userId = session('user_id');

    //     if (!$userId) {
    //         return back()->with('error', 'User session expired. Please log in again.');
    //     }


    //     $nagar = Nagar::with('mandal')->find($request->txtgram);
    //     $mandal_id = $nagar?->mandal?->mandal_id;

    //     $polling = Polling::with('area')->where('gram_polling_id', $request->txtpolling)->first();
    //     $area_id = $polling?->area?->area_id;

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


    //     $complaint = Complaint::create([
    //         'user_id' => session('user_id'),
    //         'name' => $request->txtname,
    //         'mobile_number' => $request->mobile,
    //         'father_name' => $request->father_name,
    //         'reference_name' => $request->reference,
    //         'email' => $request->mobile,
    //         'voter_id' => $request->voter,
    //         'complaint_type' => $request->type,
    //         'issue_title' => $request->CharCounter ?? 'N/A',
    //         'issue_description' => $request->NameText,
    //         'address' => $request->txtaddress,
    //         'division_id' => $request->division_id,
    //         'district_id' => $request->txtdistrict_name,
    //         'vidhansabha_id' => $vidhansabha,
    //         'mandal_id' => $mandal_id,
    //         'gram_id' => $request->txtgram,
    //         'polling_id' => $request->txtpolling,
    //         'area_id' => $area_id,
    //         'issue_attachment' => $attachment,
    //         'complaint_number' => $complaint_number,
    //         'complaint_department' => $request->department ?? '',
    //         'complaint_designation' => $request->post ?? '',
    //         'news_date' => $request->from_date,
    //         'complaint_status' => 11,
    //         'program_date' => $request->program_date,
    //         'complaint_created_by'  => session('user_id'),
    //         'type' => 2,
    //         'news_time' => $request->filled('to_date')  ? $request->to_date : '00:00',
    //         'posted_date' => now(),
    //     ]);

    //     Reply::create([
    //         'complaint_id' => $complaint->complaint_id,
    //         'forwarded_to' => 6,
    //         'complaint_status' => 11,
    //         'reply_from' => 0,
    //         'reply_date' => now(),
    //         'complaint_reply' => 'सूचना दर्ज की गई है।',
    //     ]);


    //     if ($request->ajax()) {
    //         return response()->json([
    //             'success' => true,
    //             'message' => 'सूचना सफलतापूर्वक दर्ज की गई है। आपकी सूचना संख्या है: ' . $complaint_number,
    //         ]);
    //     }

    //     return redirect()->route('operator_suchna.index')->with('success', 'सूचना सफलतापूर्वक दर्ज की गई है। आपकी सूचना संख्या है: ' . $complaint_number);
    // }

    public function suchnaStore(Request $request)
    {
        try {
            $request->validate([
                'txtname' => 'required|string|max:255',
                'mobile' => 'required|string|regex:/^[0-9]{10,15}$/',
                'father_name' => 'required|string|max:255',
                'reference' => 'nullable|string|max:255',
                'division_id' => 'required|integer',
                'voter' => 'required|string|max:255',
                'txtdistrict_name' => 'required|integer',
                'txtvidhansabha' => 'required|integer',
                'txtgram' => 'nullable|integer',
                'txtpolling' => 'nullable|integer',
                'txtaddress' => 'nullable|string|max:1000',
                'CharCounter' => 'required|string|max:100',
                'NameText' => 'required|string|max:2000',
                'type' => 'required|string',
                'department' => 'nullable',
                'jati' => 'nullable',
                'post' => 'nullable',
                'from_date' => 'nullable|date',
                'program_date' => 'nullable|date',
                'to_date' => 'nullable',
                'file_attach' => 'nullable|file|mimes:jpg,jpeg,png,mp4,mov,avi',
            ], [
                'file_attach.max' => 'फ़ाइल का आकार 15MB से अधिक नहीं होना चाहिए।',
                'file_attach.mimes' => 'केवल JPG, PNG या वीडियो फाइलें ही अपलोड करें।'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'errors' => $e->errors()
                ], 422);
            }
            throw $e;
        }

        $userId = session('user_id');

        if (!$userId) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'errors' => ['session' => ['User session expired. Please log in again.']]
                ], 401);
            }
            return back()->with('error', 'User session expired. Please log in again.');
        }

        $nagar = Nagar::with('mandal')->find($request->txtgram);
        $mandal_id = $nagar?->mandal?->mandal_id;

        $polling = Polling::with('area')->where('gram_polling_id', $request->txtpolling)->first();
        $area_id = $polling?->area?->area_id;

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
            $extension = strtolower($file->getClientOriginalExtension());

            $blocked = ['exe', 'php', 'js', 'sh', 'bat'];
            if (in_array($extension, $blocked)) {
                return response()->json([
                    'success' => false,
                    'errors' => ['file_attach' => ['यह फ़ाइल प्रकार अनुमति नहीं है।']]
                ], 422);
            }

            $filename = time() . '_' . Str::random(6) . '.' . $extension;
            $outputPath = public_path('assets/upload/complaints/' . $filename);

            if (in_array($extension, ['jpg', 'jpeg', 'png'])) {
                if ($file->getSize() > 2 * 1024 * 1024) {
                    return response()->json([
                        'success' => false,
                        'errors' => ['file_attach' => ['छवि फ़ाइल अधिकतम 2MB हो सकती है।']]
                    ], 422);
                }

                $image = Image::read($file->getRealPath());

                if ($extension === 'png') {
                    $encoder = new PngEncoder(6);
                } else {
                    $encoder = new JpegEncoder(40);
                }

                $image->encode($encoder)->save($outputPath);
            }

            elseif (in_array($extension, ['mp4', 'mov', 'avi', 'mkv'])) {
                if ($file->getSize() > 15 * 1024 * 1024) { // 15 MB
                    return response()->json([
                        'success' => false,
                        'errors' => ['file_attach' => ['वीडियो फ़ाइल अधिकतम 15MB हो सकती है।']]
                    ], 422);
                }

                $tempPath = $file->store('temp_videos');

                $ffmpeg = \FFMpeg\FFMpeg::create([
                    'ffmpeg.binaries'  => base_path(env('FFMPEG_PATH')),
                    'ffprobe.binaries' => base_path(env('FFPROBE_PATH')),
                    'timeout'          => 3600,
                    'ffmpeg.threads'   => 2,
                ]);

                $video = $ffmpeg->open(storage_path('app/' . $tempPath));
                $format = new \FFMpeg\Format\Video\X264('aac', 'libx264');
                $format->setKiloBitrate(300);
                $format->setAdditionalParameters(['-preset', 'ultrafast']);

                $video->save($format, $outputPath);

                Storage::delete($tempPath);
            }

            else {
                return response()->json([
                    'success' => false,
                    'errors' => ['file_attach' => ['केवल छवि (JPG, PNG) या वीडियो (MP4, MOV, AVI, MKV) फ़ाइलें अपलोड की जा सकती हैं।']]
                ], 422);
            }

            $attachment = $filename;
        }

        // if ($request->hasFile('file_attach')) {
        //     $file = $request->file('file_attach');
        //     $extension = $file->getClientOriginalExtension();
        //     $blocked = ['exe', 'php', 'js'];

        //     if (in_array(strtolower($extension), $blocked)) {
        //         if ($request->ajax()) {
        //             return response()->json([
        //                 'success' => false,
        //                 'errors' => ['file_attach' => ['This file type is not allowed.']]
        //             ], 422);
        //         }
        //         return back()->with('error', 'This file type is not allowed.');
        //     }

        //     $filename = time() . '_' . Str::random(6) . '.' . $extension;
        //     $file->move(public_path('assets/upload/complaints'), $filename);
        //     $attachment = $filename;
        // }

        $complaint = Complaint::create([
            'user_id' => $userId,
            'name' => $request->txtname,
            'mobile_number' => $request->mobile,
            'father_name' => $request->father_name,
            'reference_name' => $request->reference,
            'email' => $request->mobile,
            'voter_id' => $request->voter,
            'complaint_type' => $request->type,
            'issue_title' => $request->CharCounter ?? 'N/A',
            'issue_description' => $request->NameText,
            'address' => $request->txtaddress,
            'division_id' => $request->division_id,
            'district_id' => $request->txtdistrict_name,
            'vidhansabha_id' => $vidhansabha,
            'mandal_id' => $mandal_id ?? null,
            'gram_id' => $request->txtgram ?? null,
            'polling_id' => $request->txtpolling ?? null,
            'area_id' => $area_id ?? null,
            'issue_attachment' => $attachment,
            'complaint_number' => $complaint_number,
            'complaint_department' => $request->department ?? '',
            'jati_id' => $request->jati ?? null,
            'complaint_designation' => $request->post ?? '',
            'news_date' => $request->from_date,
            'complaint_status' => 11,
            'program_date' => $request->program_date,
            'complaint_created_by'  => $userId,
            'type' => 2,
            'news_time' => $request->filled('to_date')  ? $request->to_date : '00:00',
            'posted_date' => now(),
        ]);

        Reply::create([
            'complaint_id' => $complaint->complaint_id,
            'forwarded_to' => 6,
            'complaint_status' => 11,
            'reply_from' => null,
            'reply_date' => now(),
            'complaint_reply' => 'सूचना दर्ज की गई है।',
        ]);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'सूचना सफलतापूर्वक दर्ज की गई है। आपकी सूचना संख्या है: ' . $complaint_number,
            ]);
        }

        return redirect()->route('operator_suchna.index')
            ->with('success', 'सूचना सफलतापूर्वक दर्ज की गई है। आपकी सूचना संख्या है: ' . $complaint_number);
    }

    public function getNagarsByVidhansabha($vidhansabha_id)
    {
        $mandalIds = Mandal::where('vidhansabha_id', $vidhansabha_id)->pluck('mandal_id');

        $nagars = Nagar::with('mandal')
            ->whereIn('mandal_id', $mandalIds)
            ->orderBy('nagar_name')
            ->get();

        return response()->json($nagars->map(function ($n) {
            return "<option value='{$n->nagar_id}'>{$n->nagar_name} - {$n->mandal->mandal_name}</option>";
        }));
    }

    public function getPollingAndArea($nagarId)
    {
        $pollings = Polling::with('area')
            ->where('nagar_id', $nagarId)
            ->get(['gram_polling_id', 'polling_name', 'polling_no']);

        return response()->json($pollings);
    }

    public function getDesignations($department_name)
    {
        $department = Department::where('department_name', $department_name)->first();

        if (!$department) {
            return response()->json([]);
        }

        $designations = Designation::where('department_id', $department->department_id)->get();

        return response()->json($designations);
    }

    public function getDesignation(Request $request)
    {
        $designations = Designation::where('department_id', $request->id)->get();

        $options = "<option value=''>--Select Designation--</option>";
        foreach ($designations as $v) {
            $options .= '<option value="' . $v->designation_id . '">' . $v->designation_name . '</option>';
        }

        return response()->json(['options' => $options]);
    }

    public function getSubjectsByDepartment($departmentName)
    {
        $department = Department::where('department_name', $departmentName)->first();

        if (!$department) {
            return response()->json([]);
        }

        $subjects = Subject::where('department_id', $department->department_id)->get(['subject']);

        return response()->json($subjects);
    }

    public function view_complaints(Request $request)
    {
        $userId = session('user_id');

        if (!$userId) {
            return redirect()->route('login')->with('error', 'कृपया पहले लॉगिन करें।');
        }


        $query = Complaint::with(['polling', 'area', 'admin', 'vidhansabha', 'mandal', 'gram', 'replies.forwardedToManager'])
            ->where('complaint_created_by', $userId)
            ->where('type', 2)
            ->whereIn('complaint_type', ['समस्या', 'विकास']);

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

        // if ($request->filled('admin_id')) {
        //     $query->whereHas('latestReply', function ($q) use ($request) {
        //         $q->where('forwarded_to', $request->admin_id);
        //     });
        // }

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

        $start = $request->input('start', 0);
        $length = $request->input('length', 10);

        $recordsFiltered = $query->count();
        $recordsTotal = $query->count();

        $complaints = $query->orderBy('posted_date', 'desc')
            ->offset($start)
            ->limit($length)
            ->get();

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
            $data = [];
            foreach ($complaints as $index => $complaint) {

                $pendingText = $complaint->complaint_status == 4 ? 'पूर्ण' : ($complaint->complaint_status == 5 ? 'रद्द' : $complaint->pending_days . ' दिन');


                $data[] = [
                    'index' => $start + $index + 1,
                    'name' => "<strong>शिकायत क्र.: </strong>{$complaint->complaint_number}<br>" .
                        "<strong>नाम: </strong>{$complaint->name}<br>" .
                        "<strong>मोबाइल: </strong>{$complaint->mobile_number}<br>" .
                        "<strong>पुत्र श्री: </strong>{$complaint->father_name}<br>" .
                        "<strong>जाति: </strong>" . ($complaint->jati->jati_name ?? '-') . "<br>" .
                        "<strong>स्थिति: </strong>{$complaint->statusTextPlain()}",
                    'reference_name' => $complaint->reference_name ?? '',
                    'area_details' => "<strong>संभाग: </strong>" . ($complaint->division?->division_name ?? '') . ",<br>" .
                        "<strong>जिला: </strong>" . ($complaint->district?->district_name ?? '') . ",<br>" .
                        "<strong>विधानसभा: </strong>" . ($complaint->vidhansabha?->vidhansabha ?? '') . ",<br>" .
                        "<strong>मंडल: </strong>" . ($complaint->mandal?->mandal_name ?? '') . ",<br>" .
                        "<strong>नगर/ग्राम: </strong>" . ($complaint->gram?->nagar_name ?? '') . ",<br>" .
                        "<strong>मतदान केंद्र: </strong>" . ($complaint->polling?->polling_name ?? '') .
                        " (" . ($complaint->polling?->polling_no ?? '') . ") ,<br>" .
                        "<strong>ग्राम/वार्ड: </strong>" . ($complaint->area?->area_name ?? '') . ",<br>",
                    'issue_description' => $complaint->issue_description,
                    'complaint_department' => $complaint->complaint_department,
                    'posted_date' => "<strong>तिथि: " . \Carbon\Carbon::parse($complaint->posted_date)->format('d-m-Y h:i A') . "</strong><br>" . $pendingText,

                    'review_date' => optional($complaint->replies->sortByDesc('reply_date')->first())->review_date ?? 'N/A',
                    'importance' => $complaint->latestReply?->importance ?? 'N/A',
                    'applicant_name' => $complaint->admin->admin_name ?? '',
                    'forwarded_to_name' => ($complaint->forwarded_to_name ?? '-') . '<br>' . ($complaint->forwarded_reply_date ?? '-'),
                    'action' => '
                        <div class="d-flex" style="gap: 5px;">
                            <a href="' . route('operatorcomplaints.summary', $complaint->complaint_id) . '" class="btn btn-sm btn-warning" style="white-space: nowrap;">विवरण देखें</a>
                        </div>',

                    'voter_id' => $complaint->voter_id ?? ''

                ];
            }

            return response()->json([
                'draw' => intval($request->input('draw')),
                'recordsTotal' => $recordsTotal,
                'recordsFiltered' => $recordsFiltered,
                'data' => $data
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

        return view('operator/view_complaint', compact(
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


    public function view_suchna(Request $request)
    {
        $userId = session('user_id');

        if (!$userId) {
            return redirect()->route('login')->with('error', 'कृपया पहले लॉगिन करें।');
        }


        $query = Complaint::with(['polling', 'area', 'admin', 'vidhansabha', 'mandal', 'gram', 'replies.forwardedToManager'])
            ->where('complaint_created_by', $userId)
            ->where('type', 2)
            ->whereIn('complaint_type', ['अशुभ सुचना', 'शुभ सुचना']);

        if ($request->filled('complaint_status')) {
            $query->where('complaint_status', $request->complaint_status);
        }

        if ($request->filled('complaint_type')) {
            $query->where('complaint_type', $request->complaint_type);
        } else {
            // Apply default filter for initial load or sabhi
            $query->where('complaint_type', 'शुभ सुचना');
        }



        if ($request->filled('issue_title')) {
            $query->where('issue_title', $request->issue_title);
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

        if ($request->filled('programfrom_date')) {
            $query->whereDate('program_date', '>=', $request->programfrom_date);
        }

        if ($request->filled('programto_date')) {
            $query->whereDate('program_date', '<=', $request->programto_date);
        }

        $start = $request->input('start', 0);
        $length = $request->input('length', 10);

        $recordsFiltered = $query->count();
        $recordsTotal = $query->count();

        $complaints = $query->orderBy('posted_date', 'desc')
            ->offset($start)
            ->limit($length)
            ->get();

        foreach ($complaints as $complaint) {
            if (!in_array($complaint->complaint_status, [13, 14, 15, 16, 17, 18])) {
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
            $data = [];

            foreach ($complaints as $index => $complaint) {
                if ($complaint->complaint_status == 13) {
                    $pendingText = 'सम्मिलित हुए';
                } elseif ($complaint->complaint_status == 14) {
                    $pendingText = 'सम्मिलित नहीं हुए';
                } elseif ($complaint->complaint_status == 15) {
                    $pendingText = 'फोन पर संपर्क किया';
                } elseif ($complaint->complaint_status == 16) {
                    $pendingText = 'ईमेल पर संपर्क किया';
                } elseif ($complaint->complaint_status == 17) {
                    $pendingText = 'व्हाट्सएप पर संपर्क किया';
                } elseif ($complaint->complaint_status == 18) {
                    $pendingText = 'रद्द';
                } else {
                    $pendingText = $complaint->pending_days . ' दिन';
                }

                $data[] = [
                    'index' => $start + $index + 1,
                    'name' => "<strong>शिकायत क्र.: </strong>{$complaint->complaint_number}<br>" .
                        "<strong>नाम: </strong>{$complaint->name}<br>" .
                        "<strong>मोबाइल: </strong>{$complaint->mobile_number}<br>" .
                        "<strong>पुत्र श्री: </strong>{$complaint->father_name}<br>" .
                        "<strong>जाति: </strong>" . ($complaint->jati->jati_name ?? '-') . "<br>" .
                        "<strong>स्थिति: </strong>{$complaint->statusTextPlain()}",

                    'reference_name' => $complaint->reference_name ?? '',

                    'area_details' => "<strong>संभाग: </strong>" . ($complaint->division?->division_name ?? '') . ",<br>" .
                        "<strong>जिला: </strong>" . ($complaint->district?->district_name ?? '') . ",<br>" .
                        "<strong>विधानसभा: </strong>" . ($complaint->vidhansabha?->vidhansabha ?? '') . ",<br>" .
                        "<strong>मंडल: </strong>" . ($complaint->mandal?->mandal_name ?? '') . ",<br>" .
                        "<strong>नगर/ग्राम: </strong>" . ($complaint->gram?->nagar_name ?? '') . ",<br>" .
                        "<strong>मतदान केंद्र: </strong>" . ($complaint->polling?->polling_name ?? '') .
                        " (" . ($complaint->polling?->polling_no ?? '') . ") ,<br>" .
                        "<strong>ग्राम/वार्ड: </strong>" . ($complaint->area?->area_name ?? '') . ",<br>",

                    'posted_date' => "<strong>तिथि: " . \Carbon\Carbon::parse($complaint->posted_date)->format('d-m-Y h:i A') . "</strong><br>" . $pendingText,


                    'applicant_name' => $complaint->admin->admin_name ?? '',

                    'forwarded_to_name' => ($complaint->forwarded_to_name ?? '-') . '<br>' . ($complaint->forwarded_reply_date ?? '-'),

                    'issue_title' => $complaint->issue_title,
                    'program_date' => $complaint->program_date,
                    'action' => '
                        <div class="d-flex" style="gap: 5px;">
                            <a href="' . route('operatorcomplaints.summary', $complaint->complaint_id) . '" class="btn btn-sm btn-warning" style="white-space: nowrap;">विवरण देखें</a>
                        </div>',

                    'voter_id' => $complaint->voter_id ?? ''
                ];
            }

            return response()->json([
                'draw' => intval($request->input('draw')),
                'recordsTotal' => $recordsTotal,
                'recordsFiltered' => $recordsFiltered,
                'data' => $data
            ]);
        }


        $mandals = Mandal::where('vidhansabha_id', 49)->get();
        $grams = $request->mandal_id ? Nagar::where('mandal_id', $request->mandal_id)->get() : collect();
        $pollings = $request->gram_id ? Polling::where('nagar_id', $request->gram_id)->get() : collect();
        $areas = $request->polling_id ? Area::where('polling_id', $request->polling_id)->get() : collect();
        $departments = Department::all();
        $replyOptions = ComplaintReply::all();
        $managers = User::where('role', 2)->get();

        return view('operator/view_suchna', compact(
            'complaints',
            'mandals',
            'grams',
            'pollings',
            'areas',
            'departments',
            'replyOptions',
            'managers'
        ));
    }


    public function getSubjects($department_id)
    {
        $subjects = Subject::where('department_id', $department_id)->get(['subject_id', 'subject']);
        return response()->json($subjects);
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

    public function getgramPollings($nagar_id)
    {
        $pollings = Polling::where('nagar_id', $nagar_id)->get([
            'gram_polling_id',
            'polling_name',
            'polling_no'
        ]);

        return response()->json($pollings);
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


    public function getPollingsByNagar($nagarId)
    {
        $pollings = Polling::where('nagar_id', $nagarId)->with('area')->get();

        $result = $pollings->map(function ($p) {
            return [
                'id' => $p->gram_polling_id,
                'label' => "{$p->polling_name} ({$p->polling_no}) - " . ($p->area->area_name ?? ''),
                'area_id' => $p->area->area_id,
            ];
        });

        return response()->json($result);
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
            'replies.predefinedReply',
            'replies.forwardedToManager',
            'replies.replyfrom',
            'registration',
            'division',
            'district',
            'vidhansabha',
            'mandal',
            'gram',
            'polling',
            'area'
        )->findOrFail($id);

        $replyOptions = ComplaintReply::all();
        $managers = User::where('role', 2)->get();

        $latestReply = $complaint->replies()->latest('reply_date')->first();
        $disableReply = false;

        if ($latestReply && in_array($latestReply->complaint_status, [4, 5, 13, 14, 15, 16, 17, 18])) {
            $disableReply = true;
        }

        return view('operator/details_complaints', [
            'complaint' => $complaint,
            'replyOptions' => $replyOptions,
            'managers' => $managers,
            'disableReply' => $disableReply
        ]);
    }

    public function operatorReply(Request $request, $id)
    {
        $request->validate([
            'cmp_reply' => 'required|string',
            'cmp_status' => 'required|integer',
            'forwarded_to' => [
                'required_if:cmp_status,1,2,3,11,12',
                'nullable',
                'exists:admin_master,admin_id'
            ],
            'selected_reply' => [
                'nullable',
                function ($attribute, $value, $fail) {
                    if ($value !== null && (int)$value !== 0) {
                        if (!\App\Models\ComplaintReply::where('reply_id', $value)->exists()) {
                            $fail('The selected reply is invalid.');
                        }
                    }
                },
            ],
            'cb_photo.*' => 'nullable|image|mimes:jpeg,png,jpg,bmp,gif|max:2048',
            'ca_photo.*' => 'nullable|image|mimes:jpeg,png,jpg,bmp,gif|max:2048',
            'c_video' => 'nullable|url|max:255',
            'contact_status' => 'nullable|string',
            'review_date' => 'nullable|date',
            'importance' => 'nullable|string',
            'criticality' => 'nullable|string',
        ]);

        $complaint = Complaint::findOrFail($id);

        $reply = new Reply();
        $reply->complaint_id = $id;
        $reply->complaint_reply = $request->cmp_reply;
        $reply->selected_reply = $request->filled('selected_reply')
            ? (int) $request->selected_reply
            : null;
        $reply->reply_from = session('user_id') ?? null;
        $reply->reply_date = now();
        $reply->complaint_status = $request->cmp_status;
        $reply->review_date = $request->review_date ?? null;
        $reply->importance = $request->importance ?? null;
        $reply->criticality = $request->criticality ?? null;

        if ($request->filled('c_video')) {
            $reply->c_video = $request->c_video;
        }

        if (in_array((int)$request->cmp_status, [4, 5, 18, 17, 16, 15, 14, 13])) {
            $reply->forwarded_to = null;
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

        if ($request->ajax()) {
            $message = 'शिकायत का उत्तर सफलतापूर्वक दर्ज किया गया और स्थिति अपडेट हो गई।';

            if ($complaint->complaint_type === 'शुभ सुचना' || $complaint->complaint_type === 'अशुभ सुचना') {
                $message = 'सूचना का उत्तर सफलतापूर्वक दर्ज किया गया और स्थिति अपडेट हो गई।';
            }

            return response()->json([
                'message' => $message
            ]);
        }

        $successMessage = 'शिकायत का उत्तर सफलतापूर्वक दर्ज किया गया और स्थिति अपडेट हो गई।';

        if ($complaint->complaint_type === 'शुभ सुचना' || $complaint->complaint_type === 'अशुभ सुचना') {
            $successMessage = 'सूचना का उत्तर सफलतापूर्वक दर्ज किया गया और स्थिति अपडेट हो गई।';
        }

        return redirect()->route('operator_complaint.view', $id)
            ->with('success', $successMessage);
    }



    // public function nextFollowup() {
    //     $complaints = Complaint::with([
    //         'division',
    //         'district',
    //         'vidhansabha',
    //         'mandal',
    //         'gram',
    //         'polling',
    //         'area',
    //         'admin',
    //         'registrationDetails',
    //         'latestNonDefaultReply',
    //         'latestNonDefaultReply.predefinedReply',
    //         'latestNonDefaultReply.forwardedToManager'
    //     ])->whereIn('complaint_type', ['समस्या', 'विकास'])
    //         ->whereHas('latestNonDefaultReply')
    //     ->orderBy('posted_date', 'desc')
    //     ->get();

    //     return view('operator/next_followup', compact('complaints'));
    // }

    // public function nextFollowup(Request $request)
    // {
    //     $today = now()->toDateString();

    //     $complaints = Complaint::with([
    //         'division',
    //         'district',
    //         'vidhansabha',
    //         'mandal',
    //         'gram',
    //         'polling',
    //         'area',
    //         'admin',
    //         'registrationDetails',
    //         'latestNonDefaultReply',
    //         'latestNonDefaultReply.predefinedReply',
    //         'latestNonDefaultReply.latestFollowupAlways',
    //         'latestNonDefaultReply.latestFollowupNotCompleted',
    //         'latestNonDefaultReply.forwardedToManager',
    //         'latestNonDefaultReply.replyfrom',
    //         'latestNonDefaultReply.latestFollowup'
    //     ])
    //         ->whereIn('complaint_type', ['समस्या', 'विकास'])
    //         ->whereHas('latestNonDefaultReply') 
    //         ->whereNotIn('complaint_status', [4, 5]); 

    //     if ($request->filled('from_date')) {
    //         $complaints->whereDate('followup_date', '>=', $request->from_date);
    //     }
    //     if ($request->filled('to_date')) {
    //         $complaints->whereDate('followup_date', '<=', $request->to_date);
    //     }

    //     // Filter by followup status
    //     if ($request->filled('followup_status_filter')) {
    //         $filter = $request->followup_status_filter;

    //         $complaints->whereHas('latestNonDefaultReply.latestFollowupAlways', function ($q) use ($filter, $today) {
    //             if ($filter == 'upcoming') {
    //                 $q->where('followup_status', 1)
    //                     ->where('followup_date', '>', $today);
    //             } elseif ($filter == 'completed') {
    //                 $q->where('followup_status', 2);
    //             } elseif ($filter == 'not_done') {
    //                 $q->where('followup_status', 0);
    //             } elseif ($filter == 'no_followup_latest') {
    //                 $q->whereNull('followup_status');
    //             }
    //         });
    //     }

    //     $complaints = $complaints->get()->sortByDesc(fn($c) => optional($c->latestNonDefaultReply)->reply_date)->values();

    //     // If AJAX, return HTML
    //     if ($request->ajax()) {
    //         $html = view('operator.partials.next_followup_table', compact('complaints'))->render();
    //         return response()->json(['html' => $html]);
    //     }


    //     return view('operator/next_followup', compact('complaints'));
    // }



    public function nextFollowup(Request $request)
    {
        $complaints = Complaint::with([
            'division',
            'district',
            'vidhansabha',
            'mandal',
            'gram',
            'polling',
            'area',
            'admin',
            'registrationDetails',
            'latestReplyWithoutFollowup' => function ($query) {
                $query->where('need_followup', 0);
            },
            'latestReplyWithoutFollowup.predefinedReply',
            'latestReplyWithoutFollowup.forwardedToManager',
            'latestReplyWithoutFollowup.replyfrom',
            'latestNonDefaultReply.latestFollowupAlways',
            'latestNonDefaultReply.replyfrom',
            'latestNonDefaultReply.forwardedToManager',
        ])
            ->whereIn('complaint_type', ['समस्या', 'विकास'])
            ->where('complaint_status', '!=', 5)
            ->whereHas('latestReplyWithoutFollowup', function ($q) {
                $q->where('need_followup', 0);
            })
            ->get()
            ->sortByDesc(fn($c) => optional($c->latestReplyWithoutFollowup)->reply_date)
            ->values();

        // if ($request->ajax()) {
        //     $html = '';
        //     foreach ($complaints as $index => $complaint) {
        //         $latestReply = $complaint->latestReplyWithoutFollowup;
        //         $html .= '<tr>';
        //         $html .= '<td>' . ($index + 1) . '</td>';
        //         $html .= '<td><strong>शिकायत क्र.: </strong>' . $complaint->complaint_number . '<br>
        //          <strong>शिकायत प्रकार: </strong>' . $complaint->complaint_type . '<br>
        //               <strong>नाम: </strong>' . $complaint->name . '<br>
        //               <strong>मोबाइल: </strong>' . $complaint->mobile_number . '<br>
        //               <strong>पुत्र श्री: </strong>' . $complaint->father_name . '<br>
        //               <strong>आवेदक: </strong>' . ($complaint->type == 2 ? $complaint->admin->admin_name : $complaint->registrationDetails->name) . '<br>
        //               <strong>विभाग: </strong>' . $complaint->complaint_department . '<br><br>
        //               <strong>शिकायत तिथि: </strong>' . \Carbon\Carbon::parse($complaint->posted_date)->format('d-m-Y h:i A') . '<br><br>
        //               <strong>स्थिति: </strong>' . $complaint->statusTextPlain() . '</td>';

        //         $html .= '<td>
        //                 <strong>भेजने वाला:</strong> ' . ($latestReply->replyfrom->admin_name ?? 'N/A') . '<br>
        //                 <strong>फॉरवर्ड:</strong> ' . ($latestReply->forwardedToManager->admin_name ?? 'N/A') . '<br>

        //                 <strong>जवाब:</strong> ' . ($latestReply->complaint_reply ?? '') . '<br><br>
        //                  <strong>तिथि:</strong> ' . ($latestReply->reply_date ?? '') . '
        //              </td>';

        //         $latestFollowup = optional($complaint->latestNonDefaultReply)->latestFollowupAlways;
        //         if ($latestFollowup && $latestFollowup->followup_id) {
        //             $html .= '<td>
        //             <strong>फ़ॉलोअप तिथि:</strong> ' . \Carbon\Carbon::parse($latestFollowup->followup_date)->format('d-m-Y h:i A') . '<br>
        //              <strong>फ़ॉलोअप दिया:</strong> ' . ($latestFollowup->createdByAdmin->admin_name ?? 'N/A') . '<br>
        //             <strong>संपर्क स्थिति:</strong> ' . ($latestFollowup->followup_contact_status ?? 'N/A') . '<br>
        //             <strong>संपर्क विवरण:</strong> ' . ($latestFollowup->followup_contact_description ?? 'N/A') . '<br><br>
        //             <strong>स्थिति:</strong> ' . $latestFollowup->followup_status_text() . '<br>
        //          </td>';
        //         } else {
        //             $html .= '<td><span class="text-muted">कोई फ़ॉलोअप उपलब्ध नहीं</span></td>';
        //         }

        //         $html .= '</td>';
        //         $html .= '<td>
        //                 <button class="btn btn-sm btn-warning" data-toggle="modal" data-target="#contactStatusModal' . $latestReply->complaint_reply_id . '">फ़ॉलोअप</button>
        //               </td>';
        //         $html .= '<td>
        //                 <a href="' . route('follow_up.show', $complaint->complaint_id) . '" class="btn btn-sm btn-primary">क्लिक करें</a>
        //               </td>';
        //         $html .= '</tr>';
        //     }

        //     return response()->json(['html' => $html, 'count' => $complaints->count()]);
        // }

        return view('operator.next_followup', compact('complaints'));
    }

    public function nextFollowupFilter(Request $request)
    {
        $today = now()->toDateString();
        $userId = session('user_id');

        $complaints = Complaint::with([
            'division',
            'district',
            'vidhansabha',
            'mandal',
            'gram',
            'polling',
            'area',
            'admin',
            'registrationDetails',
            'latestNonDefaultReply.latestFollowupForFilter',
            'latestNonDefaultReply.forwardedToManager',
            'latestNonDefaultReply.replyfrom',
        ])
            ->whereIn('complaint_type', ['समस्या', 'विकास'])
            ->where('complaint_status', '!=', 5)
            ->whereExists(function ($query) use ($request, $userId, $today) {
                $query->selectRaw(1)
                    ->from('complaint_reply as cr')
                    ->whereColumn('cr.complaint_id', 'complaint.complaint_id')
                    ->where('cr.complaint_reply', '!=', 'शिकायत दर्ज की गई है।')
                    ->where('need_followup', 0)
                    ->whereRaw('cr.reply_date = (
                    SELECT MAX(cr2.reply_date) 
                    FROM complaint_reply cr2 
                    WHERE cr2.complaint_id = complaint.complaint_id 
                    AND cr2.complaint_reply != "शिकायत दर्ज की गई है।"
                )')
                    ->when($request->filled('operator_followup_status') || $request->filled('followup_status_filter'), function ($q) use ($request, $userId, $today) {
                        $q->whereExists(function ($f) use ($request, $userId, $today) {
                            $f->selectRaw(1)
                                ->from('followup_status as fs')
                                ->whereColumn('fs.complaint_reply_id', 'cr.complaint_reply_id')
                                ->whereRaw('fs.followup_date = (
                                SELECT MAX(fs2.followup_date) 
                                FROM followup_status fs2 
                                WHERE fs2.complaint_reply_id = cr.complaint_reply_id
                            )')
                                ->when($request->filled('operator_followup_status'), function ($f2) use ($request, $userId, $today) {
                                    $status = $request->operator_followup_status;
                                    $f2->where('fs.followup_created_by', $userId)
                                        ->when($status === 'completed_by_me', fn($q) => $q->where('fs.followup_status', 2))
                                        ->when($status === 'pending_by_me', fn($q) => $q->where('fs.followup_status', 1)->whereDate('fs.followup_date', $today))
                                        ->when($status === 'upcoming_by_me', fn($q) => $q->where('fs.followup_status', 1)->whereDate('fs.followup_date', '<', $today));
                                })
                                ->when($request->filled('followup_status_filter'), function ($f2) use ($request, $today) {
                                    $status = $request->followup_status_filter;
                                    $f2->when($status === 'completed', fn($q) => $q->where('fs.followup_status', 2))
                                        ->when($status === 'upcoming', fn($q) => $q->where('fs.followup_status', 1)->whereDate('fs.followup_date', '<', $today))
                                        ->when($status === 'not_done', fn($q) => $q->where('fs.followup_status', 1)->whereDate('fs.followup_date', $today));
                                });
                        });
                    });
            });

        if ($request->filled('from_date') || $request->filled('to_date')) {
            $complaints = $complaints->get()->filter(function ($complaint) use ($request) {
                $from = $request->from_date;
                $to = $request->to_date;

                $latestReply = $complaint->latestNonDefaultReply;
                if (!$latestReply) {
                    return false;
                }

                $latestFollowup = $latestReply->latestFollowupForFilter;

                // Check reply date
                $replyDateValid = true;
                if ($from && $latestReply->reply_date < $from) {
                    $replyDateValid = false;
                }
                if ($to && $latestReply->reply_date > $to) {
                    $replyDateValid = false;
                }

                // Check latest followup date (if exists)
                $followupDateValid = false;
                if ($latestFollowup) {
                    $followupDateValid = true;
                    if ($from && $latestFollowup->followup_date->toDateString() < $from) {
                        $followupDateValid = false;
                    }
                    if ($to && $latestFollowup->followup_date->toDateString() > $to) {
                        $followupDateValid = false;
                    }
                }

                // Include if either reply date OR latest followup date is in range
                return $replyDateValid || $followupDateValid;
            });
        } else {
            $complaints = $complaints->get();
        }

        $complaints = $complaints->sortByDesc(function ($c) {
            return optional(optional($c->latestNonDefaultReply)->latestFollowupForFilter)->followup_date;
        })->values();


        if ($request->ajax()) {
            $html = '';
            foreach ($complaints as $index => $complaint) {
                $latestFollowup = optional($complaint->latestNonDefaultReply->latestFollowupForFilter);


                $disableButton = false;
                if ($latestFollowup) {
                    if ($latestFollowup->followup_status == 2) {
                        $disableButton = true;
                    } elseif ($latestFollowup->followup_status == 1 && $latestFollowup->followup_date->toDateString() == now()->toDateString()) {
                        $disableButton = true;
                    }
                }

                $html .= '<tr>';
                $html .= '<td>' . ($index + 1) . '</td>';
                $html .= '<td><strong>शिकायत क्र.: </strong>' . $complaint->complaint_number . '<br>
                 <strong>शिकायत प्रकार: </strong>' . $complaint->complaint_type . '<br>
                      <strong>नाम: </strong>' . $complaint->name . '<br>
                      <strong>मोबाइल: </strong>' . $complaint->mobile_number . '<br>
                      <strong>पुत्र श्री: </strong>' . $complaint->father_name . '<br>
                       <strong>आवेदक: </strong>' . ($complaint->type == 2 ? ($complaint->admin->admin_name ?? 'N/A') : ($complaint->registrationDetails->name ?? 'N/A')) . '<br>
                      <strong>विभाग: </strong>' . $complaint->complaint_department . '<br><br>
                      <strong>शिकायत तिथि: </strong>' . \Carbon\Carbon::parse($complaint->posted_date)->format('d-m-Y h:i A') . '<br><br>
                      <strong>स्थिति: </strong>' . $complaint->statusTextPlain() . '</td>';

                $html .= '<td>
                        <strong>भेजने वाला:</strong> ' . ($complaint->latestNonDefaultReply->replyfrom->admin_name ?? 'N/A') . '<br>
                        <strong>फॉरवर्ड:</strong> ' . ($complaint->latestNonDefaultReply->forwardedToManager->admin_name ?? 'N/A') . '<br>
                       
                        <strong>जवाब:</strong> ' . ($complaint->latestNonDefaultReply->complaint_reply ?? '') . '<br><br>
                         <strong>तिथि:</strong> ' . ($complaint->latestNonDefaultReply->reply_date ?? '') . '
                     </td>';

                if ($latestFollowup && $latestFollowup->followup_id) {
                    $html .= '<td>
                    <strong>फ़ॉलोअप तिथि:</strong> ' . \Carbon\Carbon::parse($latestFollowup->followup_date)->format('d-m-Y h:i A') . '<br>
                     <strong>फ़ॉलोअप दिया:</strong> ' . ($latestFollowup->createdByAdmin->admin_name ?? 'N/A') . '<br>
                    <strong>संपर्क स्थिति:</strong> ' . ($latestFollowup->followup_contact_status ?? 'N/A') . '<br>
                    <strong>संपर्क विवरण:</strong> ' . ($latestFollowup->followup_contact_description ?? 'N/A') . '<br><br>
                    <strong>स्थिति:</strong> ' . $latestFollowup->followup_status_text() . '<br>
                 </td>';
                } else {
                    $html .= '<td><span class="text-muted">कोई फ़ॉलोअप उपलब्ध नहीं</span></td>';
                }

                $html .= '</td>';



                $html .= '<td>
                        <button class="btn btn-sm btn-warning openModalBtn" data-complaint-id="' . $complaint->complaint_id . '" data-complaint-reply-id="' . $complaint->latestNonDefaultReply->complaint_reply_id . '"' . ($disableButton ? ' disabled' : '') . '>फ़ॉलोअप</button>
                      </td>';


                $html .= '<td>
                        <a href="' . route('operatorcomplaints.summary', $complaint->complaint_id) . '" class="btn btn-sm btn-primary">क्लिक करें</a>
                      </td>';
                $html .= '</tr>';
            }

            return response()->json(['html' => $html, 'count' => $complaints->count()]);
        }

        return view('operator.next_followup', compact('complaints'));
    }



    public function updateContactStatus(Request $request, $id)
    {
        $request->validate([
            'contact_status' => 'nullable|string|max:255',
            'contact_update' => 'nullable|string|max:500',
            'complaint_id' => 'required|exists:complaint,complaint_id',
        ]);

        $reply = Reply::findOrFail($id);

        if (!$reply) {
            return redirect()->back()->withErrors('Reply not found.');
        }


        $contactStatus = $request->contact_status;
        if (!$contactStatus) {
            $followupStatus = 0;
        } elseif ($contactStatus === 'सूचना दे दी गई है') {
            $followupStatus = 2;
        } else {
            $followupStatus = 1;
        }

        FollowupStatus::create([
            'complaint_reply_id' => $reply->complaint_reply_id,
            'complaint_id' => $request->complaint_id,
            'followup_contact_status'      => $request->contact_status,
            'followup_contact_description' => $request->contact_update,
            'followup_status'              => $followupStatus,
            'followup_created_by'          => session('user_id'),
        ]);

        return redirect()->back()->with('success', 'फॉलो-अप सफलतापूर्वक दर्ज किया गया।');
    }

    public function followup_show($id)
    {
        $complaint = Complaint::with(
            'replies.predefinedReply',
            'replies.forwardedToManager',
            'replies.followups.createdByAdmin',
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

        return view('operator/followup_details', [
            'complaint' => $complaint,
            'replyOptions' => $replyOptions,
            'managers' => $managers,
        ]);
    }



    public function summary($id, Request $request)
    {
        $complaint = Complaint::with(['replies.followups'])->findOrFail($id);

        if ($request->query('reason') === 'status_check') {
            \DB::table('incoming_calls')->insert([
                'complaint_id'        => $complaint->complaint_id,
                'complaint_reply_id'  => null,
                'reason'              => 2,
                'incoming_created_by' => session('user_id'), 
                'created_at'          => now(),
            ]);
        }

        $complaint->replies = $complaint->replies->sortByDesc('reply_date');

        $complaint->replies->each(function ($reply) {
            $reply->followups = $reply->followups->sortByDesc('followup_date');
        });

        $totalReplies = $complaint->replies->count();

        $totalFollowups = $complaint->replies->reduce(function ($carry, $reply) {
            return $carry + $reply->followups->count();
        }, 0);

        return view('operator/details_summary', compact('complaint', 'totalReplies', 'totalFollowups'));
    }





    // incoming calls functions
    public function incoming(Request $request)
    {
        $departments = Department::all();
        $mandals = Mandal::where('vidhansabha_id', 49)->get();
        $grams = $request->mandal_id ? Nagar::where('mandal_id', $request->mandal_id)->get() : collect();
        $pollings = $request->gram_id ? Polling::where('nagar_id', $request->gram_id)->get() : collect();
        $areas = $request->polling_id ? Area::where('polling_id', $request->polling_id)->get() : collect();

        $query = Complaint::with([
            'latestNonDefaultReply.latestFollowup',
            'latestNonDefaultReply.latestFollowupNotCompleted',
            'latestReplyWithoutFollowup',
            'registrationDetails',
            'latestReply',
            'admin'
        ])->whereIn('complaint_type', ['समस्या', 'विकास']);;

        // if ($request->filled('from_date')) $query->whereDate('posted_date', '>=', $request->from_date);
        // if ($request->filled('to_date')) $query->whereDate('posted_date', '<=', $request->to_date);
        if ($request->filled('department_id')) $query->where('complaint_department', $request->department_id);
        if ($request->filled('mandal_id')) $query->where('mandal_id', $request->mandal_id);
        if ($request->filled('gram_id')) $query->where('gram_id', $request->gram_id);
        if ($request->filled('polling_id')) $query->where('polling_id', $request->polling_id);
        if ($request->filled('area_id')) $query->where('area_id', $request->area_id);
        if ($request->filled('mobile')) $query->where('mobile_number', 'like', '%' . $request->mobile . '%');
        if ($request->filled('filter')) {
            $search = $request->filter;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%$search%")
                    ->orWhere('father_name', 'like', "%$search%")
                    ->orWhere('reference_name', 'like', "%$search%")
                    ->orWhere('voter_id', 'like', "%$search%");
            });
        }

        $complaints = $query->orderBy('posted_date', 'desc')->get();

        if ($request->ajax()) {
            $html = '';
            foreach ($complaints as $index => $complaint) {
                $latestFollowup = optional($complaint->latestNonDefaultReply)->latestFollowup;
                $latestFollowupNonCompleted = optional($complaint->latestNonDefaultReply)->latestFollowupNotCompleted;
                $today = now()->toDateString();

                if (optional($latestFollowup)->followup_status == 2) {
                    $rowStatus = 'completed';
                } elseif (
                    optional($latestFollowupNonCompleted)->followup_status == 1 &&
                    optional($latestFollowupNonCompleted)->followup_date != $today
                ) {
                    $rowStatus = 'update_followup';
                } elseif (
                    optional($latestFollowupNonCompleted)->followup_status == 1 &&
                    optional($latestFollowupNonCompleted)->followup_date == $today
                ) {
                    $rowStatus = 'done_not_completed';
                } else {
                    $rowStatus = 'not_done';
                }

                $html .= '<tr data-followup-status="' . $rowStatus . '">';
                $html .= '<td>' . ($index + 1) . '</td>';
                $html .= '<td><strong>शिकायत क्र.: </strong>' . ($complaint->complaint_number ?? 'N/A') . '<br>';
                $html .= '<strong>शिकायत प्रकार: </strong>' . ($complaint->complaint_type ?? '') . '<br>';
                $html .= '<strong>नाम: </strong>' . ($complaint->name ?? 'N/A') . '<br>';
                $html .= '<strong>मोबाइल: </strong>' . ($complaint->mobile_number ?? '') . '<br>';
                $html .= '<strong>पुत्र श्री: </strong>' . ($complaint->father_name ?? '') . '<br>';
                $html .= '<strong>आवेदक: </strong>' . ($complaint->type == 2 ? ($complaint->admin->admin_name ?? '-') : ($complaint->registrationDetails->name ?? '-')) . '<br>';
                $html .= '<strong>विभाग: </strong>' . ($complaint->complaint_department ?? 'N/A') . '<br>';
                $html .= '<strong>जाति: </strong>' . ($complaint->jati->jati_name ?? 'N/A') . '<br>';
                $html .= '<strong>शिकायत तिथि: </strong>' . \Carbon\Carbon::parse($complaint->posted_date)->format('d-m-Y h:i A') . '<br>';
                $html .= '<strong>स्थिति: </strong>' . $complaint->statusTextPlain() . '</td>';

                $latestReply = $complaint->latestReply;
                $html .= '<td><strong>भेजने वाला: </strong>' . ($latestReply->replyfrom->admin_name ?? 'N/A') . '<br>';
                $html .= '<strong>फॉरवर्ड: </strong>' . ($latestReply->forwardedToManager->admin_name ?? 'N/A') . '<br>';
                $html .= '<strong>जवाब: </strong>' . ($latestReply->complaint_reply ?? '') . '<br>';
                $html .= '<strong>तिथि: </strong>' . ($latestReply->reply_date ?? '') . '</td>';

                $html .= '<td>';
                if ($latestFollowup) {
                    $html .= '<strong>फ़ॉलोअप तिथि: </strong>' . \Carbon\Carbon::parse($latestFollowup->followup_date)->format('d-m-Y h:i A') . '<br>';
                    $html .= '<strong>फ़ॉलोअप दिया: </strong>' . ($latestFollowup->createdByAdmin->admin_name ?? 'N/A') . '<br>';
                    $html .= '<strong>संपर्क स्थिति: </strong>' . ($latestFollowup->followup_contact_status ?? 'N/A') . '<br>';
                    $html .= '<strong>संपर्क विवरण: </strong>' . ($latestFollowup->followup_contact_description ?? 'N/A') . '<br>';
                    $html .= '<strong>स्थिति: </strong>' . $latestFollowup->followup_status_text() . '<br>';
                } else {
                    $html .= '<span class="text-muted">कोई फ़ॉलोअप उपलब्ध नहीं</span>';
                }
                $html .= '</td>';

                $html .= '<td>';
                if ($complaint->latestNonDefaultReply) {
                    $latestFollowup = optional($complaint->latestNonDefaultReply)->latestFollowup;

                    if ($latestFollowup && $latestFollowup->followup_status == 2) {

                        $html .= '<button type="button" class="btn btn-sm btn-warning text-dark" disabled>फ़ॉलोअप पूर्ण</button>';
                    } elseif ($latestFollowup) {
                        $html .= '<button type="button" class="btn btn-sm btn-warning openModalBtn" 
                            data-complaint-id="' . $complaint->complaint_id . '" 
                            data-complaint-reply-id="' . $complaint->latestNonDefaultReply->complaint_reply_id . '">
                            फ़ॉलोअप</button>';
                    } else {
                        $html .= '<span class="badge" style="background-color:#f8d7da; color:#721c24;">फ़ॉलोअप नहीं किया गया</span>';
                    }
                } else {
                    $html .= '<span class="text-muted">कोई जवाब उपलब्ध नहीं</span>';
                }
                $html .= '</td>';

                // $html .= '<td><a href="' . route('operatorcomplaints.summary', $complaint->complaint_id) . '" class="btn btn-sm btn-primary" style="white-space: nowrap;">क्लिक करें</a></td>';

                $latestReplyId = optional($complaint->latestNonDefaultReply)->complaint_reply_id ?? 'null';
                $latestFollowup = optional($complaint->latestNonDefaultReply)->latestFollowup;

                $followupAvailable = ($complaint->latestNonDefaultReply && $latestFollowup && $latestFollowup->followup_status != 2);

                $html .= '<td>
                    <div class="form-check">
                        <input class="form-check-input followup-radio" type="radio" name="reason" value="followup_response" id="followup_status_' . $complaint->complaint_id . '"
                            ' . ($followupAvailable ? '' : 'disabled') . '>
                        <label for="followup_status_' . $complaint->complaint_id . '" class="form-check-label">फ़ॉलोअप प्रतिक्रिया</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="reason" value="status_check" id="view_status_' . $complaint->complaint_id . '"
                            onchange="handleViewReason(' . $complaint->complaint_id . ')">
                        <label for="view_status_' . $complaint->complaint_id . '" class="form-check-label">समस्या स्थिति जानने</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="reason" value="status_update" id="update_status_' . $complaint->complaint_id . '"
                            onchange="handleReasonAndRedirect(' . $complaint->complaint_id . ')">
                        <label for="update_status_' . $complaint->complaint_id . '" class="form-check-label">समस्या स्थिति अपडेट करने</label>
                    </div>
                </td>';


                $html .= '</tr>';
            }

            return response()->json([
                'html' => $html,
                'count' => $complaints->count()
            ]);
        }

        return view('operator.incoming_calls', compact('departments', 'mandals', 'grams', 'pollings', 'areas', 'complaints'));
    }


    // public function storeIncomingReason(Request $request)
    // {
    //     $request->validate([
    //         'complaint_id' => 'required|exists:complaint,complaint_id',
    //         'reason' => 'required|string',
    //     ]);

    //     $reasonMap = [
    //         'followup_response' => 1,
    //         'status_check' => 2,
    //         'status_update' => 3,
    //     ];

    //     $reason = $reasonMap[$request->reason] ?? null;

    //     if (!$reason) {
    //         return response()->json(['success' => false, 'message' => 'Invalid reason'], 400);
    //     }

    //     \DB::table('incoming_calls')->insert([
    //         'complaint_id' => $request->complaint_id,
    //         'complaint_reply_id' => $request->complaint_reply_id ?? null,
    //         'reason' => $reason,
    //         'incoming_created_by' => session('user_id'),
    //         'created_at' => now(),
    //     ]);

    //     return response()->json(['success' => true]);
    // }


    public function updateIncomingContactStatus(Request $request, $id)
    {
        $request->validate([
            'contact_status' => 'nullable|string|max:255',
            'contact_update' => 'nullable|string|max:500',
            'complaint_id' => 'required|exists:complaint,complaint_id',
        ]);

        $reply = Reply::findOrFail($id);

        if (!$reply) {
            return redirect()->back()->withErrors('Reply not found.');
        }

        $contactStatus = $request->contact_status;
        $followupStatus = 0;
        if ($contactStatus === 'सूचना दे दी गई है') {
            $followupStatus = 2;
        } elseif ($contactStatus) {
            $followupStatus = 1;
        }

        $followup = FollowupStatus::create([
            'complaint_reply_id' => $reply->complaint_reply_id,
            'complaint_id' => $request->complaint_id,
            'followup_contact_status' => $request->contact_status,
            'followup_contact_description' => $request->contact_update,
            'followup_status' => $followupStatus,
            'followup_created_by' => session('user_id'),
        ]);

        if ($followup) {
            \DB::table('incoming_calls')->insert([
                'complaint_id' => $request->complaint_id,
                'complaint_reply_id' => $reply->complaint_reply_id,
                'reason' => 1,
                'incoming_created_by' => session('user_id'),
                'created_at' => now(),
            ]);
        }

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'प्राप्त कॉल का फॉलो-अप सफलतापूर्वक दर्ज किया गया।'
            ]);
        }
    }


    public function allcomplaints_show($id)
    {
        $complaint = Complaint::with(
            'replies.predefinedReply',
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
        $divisions = Division::all();
        $mandals = Mandal::where('vidhansabha_id', $complaint->vidhansabha_id)->pluck('mandal_id');

        $nagars = Nagar::with('mandal')
            ->whereIn('mandal_id', $mandals)
            ->orderBy('nagar_name')
            ->get();
        $departments = Department::all();
        $jatis = Jati::all();

        return view('operator/update_complaint', [
            'complaint' => $complaint,
            'nagars' => $nagars,
            'departments' => $departments,
            'jatis' => $jatis,
            'divisions' => $divisions
        ]);
    }


    public function updateComplaint(Request $request, $id)
    {
        try {
            $request->validate([
                'txtname' => 'required|string|max:255',
                'mobile'  => 'required|string|regex:/^[0-9]{10,15}$/',
                'father_name' => 'required|string|max:255',
                'reference' => 'nullable|string|max:255',
                'voter' => 'required|string|max:255',
                'division_id' => 'required|integer',
                'txtdistrict_name' => 'required|integer',
                'txtvidhansabha' => 'required|integer',
                'txtgram' => 'nullable|integer',
                'txtpolling' => 'nullable|integer',
                'type' => 'required|string',
                'CharCounter' => 'nullable|string|max:100',
                'NameText' => 'required|string|max:2000',
                'department' => 'nullable',
                'jati' => 'nullable',
                'post' => 'nullable',
                'from_date' => 'nullable|date',
                'program_date' => 'nullable|date',
                'to_date' => 'nullable',
                'file_attach' => 'nullable|file|mimes:jpg,jpeg,png,mp4,mov,avi',
                'attachments.*' => 'mimes:pdf,jpg,jpeg,png,mp4,mov|max:10240'
            ], [
                'file_attach.max' => 'फ़ाइल का आकार 15MB से अधिक नहीं होना चाहिए।',
                'file_attach.mimes' => 'केवल JPG, PNG या वीडियो फाइलें ही अपलोड करें।'
            ]);
        } catch (ValidationException $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'errors' => $e->errors()
                ], 422);
            }
            throw $e;
        }


        DB::beginTransaction();


        try {
            $complaint = Complaint::findOrFail($id);
            $complaint_number = $complaint->complaint_number;

            $nagar = Nagar::with('mandal')->find($request->txtgram);
            $mandal_id = $nagar?->mandal?->mandal_id;

            $polling = Polling::with('area')->where('gram_polling_id', $request->txtpolling)->first();
            $area_id = $polling?->area?->area_id;



            DB::table('update_complaints')->insert([
                'complaint_id' => $complaint->complaint_id,
                'complaint_type' => $complaint->complaint_type,
                'name' => $complaint->name,
                'mobile_number' => $complaint->mobile_number,
                'father_name' => $complaint->father_name,
                'reference_name' => $complaint->reference_name,
                'email' => $complaint->email,
                'voter_id' => $complaint->voter_id,
                'division_id' => $complaint->division_id,
                'district_id' => $complaint->district_id,
                'vidhansabha_id' => $complaint->vidhansabha_id,
                'mandal_id' => $complaint->mandal_id ?? null,
                'gram_id' => $complaint->gram_id ?? null ,
                'polling_id' => $complaint->polling_id ?? null,
                'area_id' => $complaint->area_id ?? null,
                'complaint_department' => $complaint->complaint_department,
                'jati_id' => $complaint->jati_id,
                'complaint_designation' => $complaint->complaint_designation,
                'issue_title' => $complaint->issue_title,
                'address' => $complaint->address ?? null,
                'issue_description' => $complaint->issue_description,
                'news_date' => $complaint->news_date,
                'program_date' => $complaint->program_date,
                'news_time' => $complaint->news_time,
                'issue_attachment' => $complaint->issue_attachment,
                'updated_by' => session('user_id'),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $complaint->complaint_type = $request->type;
            $complaint->name = $request->txtname;
            $complaint->mobile_number = $request->mobile;
            $complaint->father_name = $request->father_name;
            $complaint->reference_name = $request->reference;
            $complaint->email = $request->mobile;
            $complaint->voter_id = $request->voter;
            $complaint->division_id = $request->division_id;
            $complaint->district_id = $request->txtdistrict_name;
            $complaint->vidhansabha_id = $request->txtvidhansabha;
            $complaint->mandal_id = $mandal_id ?? null;
            $complaint->gram_id = $request->txtgram ?? null;
            $complaint->polling_id = $request->txtpolling ?? null;
            $complaint->area_id = $area_id ?? null;
            $complaint->complaint_department = $request->department ?? '';
            $complaint->jati_id = $request->jati ?? null;
            $complaint->complaint_designation = $request->post ?? '';
            $complaint->issue_title = $request->CharCounter ?? '';
            $complaint->issue_description = $request->NameText;
            $complaint->news_date = $request->from_date;
            $complaint->program_date = $request->program_date;
            $complaint->news_time = $request->to_date;


            if ($request->hasFile('file_attach')) {
                $filename = $this->processFile($request->file('file_attach'));
                $complaint->issue_attachment = $filename;
            }


            $complaint->save();



            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $file) {
                    $filename = $this->processFile($file);
                    ComplaintAttachment::create([
                        'complaint_id' => $complaint->complaint_id,
                        'file_name'    => $filename,
                        'file_type'    => strtolower($file->getClientOriginalExtension()),
                        'created_at'   => now(),
                    ]);
                }
            }


            DB::table('incoming_calls')->insert([
                'complaint_id' => $complaint->complaint_id,
                'complaint_reply_id' => $request->complaint_reply_id ?? null,
                'reason' => 3,
                'incoming_created_by' => session('user_id'),
                'created_at' => now(),
            ]);


            DB::commit();

            $message = "आपकी शिकायत क्रमांक $complaint_number सफलतापूर्वक अपडेट कर दी गई है।";
            if ($complaint->type == 1) {
                $mobile = RegistrationForm::where('registration_id', $complaint->complaint_created_by)->value('mobile1');
                $this->messageSent($message, $mobile);
            }

            if ($request->ajax()) {
                return response()->json(['success' => true, 'message' => $message]);
            }

            if ($complaint->type == 1) {
                return redirect()->route('incoming_calls.index')
                    ->with('success', 'कमांडर शिकायत सफलतापूर्वक अपडेट हुई और संदेश भेजा गया।');
            } else {
                return redirect()->route('incoming_calls.index')
                    ->with('success', 'कार्यालय शिकायत सफलतापूर्वक अपडेट हुई');
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', "Update failed: " . $e->getMessage());
        }
    }


    private function processFile($file)
    {
        $extension = strtolower($file->getClientOriginalExtension());
        $filename = time() . '_' . Str::random(6) . '.' . $extension;
        $outputPath = public_path('assets/upload/complaints/' . $filename);


        $blocked = ['exe', 'php', 'js', 'sh', 'bat'];
        if (in_array($extension, $blocked)) {
            throw new \Exception("यह फ़ाइल प्रकार अनुमति नहीं है।");
        }
      
        if (in_array($extension, ['jpg', 'jpeg', 'png'])) {
            if ($file->getSize() > 2 * 1024 * 1024) {
                throw new \Exception("छवि फ़ाइल अधिकतम 2MB हो सकती है।");
            }
            $image = Image::read($file->getRealPath());
            $encoder = $extension === 'png' ? new PngEncoder(6) : new JpegEncoder(40);
            $image->encode($encoder)->save($outputPath);
        }
      
        elseif (in_array($extension, ['mp4', 'mov', 'avi', 'mkv'])) {
            if ($file->getSize() > 15 * 1024 * 1024) {
                throw new \Exception("वीडियो फ़ाइल अधिकतम 15MB हो सकती है।");
            }
            $tempPath = $file->store('temp_videos');
            $ffmpeg = \FFMpeg\FFMpeg::create([
                'ffmpeg.binaries'  => base_path(env('FFMPEG_PATH')),
                'ffprobe.binaries' => base_path(env('FFPROBE_PATH')),
            ]);
            $video = $ffmpeg->open(storage_path('app/' . $tempPath));
            $format = new \FFMpeg\Format\Video\X264('aac', 'libx264');
            $format->setKiloBitrate(300);
            $format->setAdditionalParameters(['-preset', 'ultrafast']);
            $video->save($format, $outputPath);
            Storage::delete($tempPath);
        }
   
        elseif ($extension === 'pdf') {
            $pdf = new Fpdi();
            $pageCount = $pdf->setSourceFile($file->getRealPath());
            for ($i = 1; $i <= $pageCount; $i++) {
                $tpl = $pdf->importPage($i);
                $size = $pdf->getTemplateSize($tpl);
                $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
                $pdf->useTemplate($tpl, 0, 0, $size['width'], $size['height'], true);
            }
            $pdf->Output('F', $outputPath);
        }
       
        else {
            throw new \Exception("Unsupported file type: $extension");
        }

        return $filename;
    }
}
