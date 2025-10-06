<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Jenssegers\Agent\Agent;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Models\Department;
use App\Models\Adhikari;
use App\Models\Subject;
use App\Models\Designation;
use App\Models\District;
use App\Models\ComplaintReply;
use App\Models\RegistrationForm;
use App\Models\Step2;
use App\Models\Step3;
use Illuminate\Support\Facades\Http;
use App\Models\Step4;
use App\Models\Mandal;
use App\Models\Nagar;
use App\Models\Area;
use App\Models\Polling;
use App\Models\Jati;
use App\Models\Interest;
use App\Models\Business;
use App\Models\Politics;
use App\Models\Education;
use App\Models\Category;
use App\Models\Religion;
use App\Models\Level;
use App\Models\Position;
use App\Models\Complaint;
use App\Models\Reply;
use App\Models\AssignPosition;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Carbon\Carbon;
use Intervention\Image\Facades\Image;
use App\Models\Division;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Mpdf\Mpdf;
use App\Models\VidhansabhaLokSabha;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminController extends Controller
{
    public function page()
    {
        return view('admin/page');
    }

    public function getCalendarData(Request $request)
    {
        $month = (int) $request->month;
        $year = (int) $request->year;

        if (!$month || !$year) {
            return response()->json(['error' => 'Invalid month or year'], 400);
        }

        $start = Carbon::createFromDate($year, $month, 1)->startOfMonth()->toDateString();
        $end = Carbon::createFromDate($year, $month, 1)->endOfMonth()->toDateString();

        $loggedInId = session('user_id');

        $records = DB::table('complaint')
            ->join('complaint_reply as cr', 'complaint.complaint_id', '=', 'cr.complaint_id')
            ->whereRaw('cr.reply_date = (
            SELECT MAX(cr2.reply_date)
            FROM complaint_reply cr2
            WHERE cr2.complaint_id = complaint.complaint_id
        )')
            ->where('cr.forwarded_to', $loggedInId)
            ->whereBetween(DB::raw('DATE(complaint.program_date)'), [$start, $end])
            ->selectRaw("DATE(complaint.program_date) as date,
                     SUM(CASE WHEN complaint.complaint_type = 'शुभ सुचना' THEN 1 ELSE 0 END) as shubh,
                     SUM(CASE WHEN complaint.complaint_type = 'अशुभ सुचना' THEN 1 ELSE 0 END) as asubh")
            ->groupBy(DB::raw("DATE(complaint.program_date)"))
            ->get();

        $data = [];
        foreach ($records as $row) {
            $data[$row->date] = [
                'shubh' => (int) $row->shubh,
                'asubh' => (int) $row->asubh,
            ];
        }

        return response()->json($data);
    }

    public function getComplaintSummary()
    {
        $now = Carbon::now();

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
                    type,
                    SUM(CASE WHEN complaint_type = 'समस्या' THEN 1 ELSE 0 END) as samasya,
                    SUM(CASE WHEN complaint_type = 'विकास' THEN 1 ELSE 0 END) as vikash
                ")
                ->whereBetween('posted_date', [$start, $end])
                ->whereIn('complaint_id', function ($query) {
                    $query->select(DB::raw('cr1.complaint_id'))
                        ->from('complaint_reply as cr1')
                        ->whereRaw('cr1.complaint_reply_id = (
                        SELECT MAX(cr2.complaint_reply_id) 
                        FROM complaint_reply cr2 
                        WHERE cr2.complaint_id = cr1.complaint_id
                    )');
                    // ->whereNotIn('cr1.complaint_status', [4, 5]); // exclude पूर्ण & रद्द
                })
                ->groupBy('type')
                ->get();

            $replyCounts = DB::table('complaint')
                ->join('complaint_reply', 'complaint.complaint_id', '=', 'complaint_reply.complaint_id')
                ->whereBetween('complaint.posted_date', [$start, $end])
                ->where('complaint.complaint_type', 'समस्या')
                ->select('complaint.type', DB::raw('COUNT(*) as reply_count'))
                ->groupBy('complaint.type')
                ->pluck('reply_count', 'complaint.type');

            $replyVikashCounts = DB::table('complaint')
                ->join('complaint_reply', 'complaint.complaint_id', '=', 'complaint_reply.complaint_id')
                ->whereBetween('complaint.posted_date', [$start, $end])
                ->where('complaint.complaint_type', 'विकास')
                ->select('complaint.type', DB::raw('COUNT(*) as reply_count'))
                ->groupBy('complaint.type')
                ->pluck('reply_count', 'complaint.type');

            $entry = [
                'samay' => $label,
                'section' => $sectionMap[$label] ?? null,
                'samasya' => ['operator' => 0, 'commander' => 0],
                'vikash' => ['operator' => 0, 'commander' => 0],
                'replies' => [
                    'operator' => $replyCounts[2] ?? 0,
                    'commander' => $replyCounts[1] ?? 0,
                ],

                'repliesvikash' => [
                    'operator' => $replyVikashCounts[2] ?? 0,
                    'commander' => $replyVikashCounts[1] ?? 0,
                ],
            ];

            foreach ($records as $row) {
                $type = $row->type == 1 ? 'commander' : 'operator';
                $entry['samasya'][$type] = $row->samasya;
                $entry['vikash'][$type] = $row->vikash;
            }

            $result[] = $entry;
        }

        return response()->json($result);
    }



    public function fetchSuchna()
    {
        $loggedInId = session('user_id');
        if (!$loggedInId) {
            return response()->json(['error' => 'User not logged in.'], 401);
        }

        $today = \Carbon\Carbon::today();
        $tomorrow = \Carbon\Carbon::tomorrow();
        $weekEnd = \Carbon\Carbon::today()->addDays(6);

        $latestRepliesSub = \App\Models\Reply::selectRaw('MAX(reply_date) as latest_date, complaint_id')
            ->groupBy('complaint_id');

        $table = (new \App\Models\Complaint)->getTable();

        $records = \App\Models\Complaint::with('area')
            ->joinSub($latestRepliesSub, 'latest', function ($join) use ($table) {
                $join->on("$table.complaint_id", '=', 'latest.complaint_id');
            })
            ->join('complaint_reply as cr', function ($join) use ($table) {
                $join->on("$table.complaint_id", '=', 'cr.complaint_id')
                    ->on('cr.reply_date', '=', 'latest.latest_date');
            })
            ->where('cr.forwarded_to', $loggedInId)
            ->whereIn("$table.complaint_type", ['शुभ सुचना', 'अशुभ सुचना'])
            ->whereBetween("$table.program_date", [$today, $weekEnd])
            ->orderBy("$table.program_date", 'asc')
            ->get([
                "$table.complaint_id",
                "$table.name",
                "$table.mobile_number",
                "$table.area_id",
                "$table.issue_description",
                "$table.complaint_type",
                "$table.program_date",
                "$table.news_time"
            ]);

        $todayData = [];
        $tomorrowData = [];
        $weekData = [];

        foreach ($records as $row) {
            $date = \Carbon\Carbon::parse($row->program_date)->toDateString();

            $recordData = $row->toArray();
            $recordData['area_name'] = $row->area->area_name ?? 'N/A';

            if ($date == $today->toDateString()) {
                $todayData[] = $recordData;
            } elseif ($date == $tomorrow->toDateString()) {
                $tomorrowData[] = $recordData;
            }

            $weekData[] = $recordData;
        }

        return response()->json([
            'today' => $todayData,
            'tomorrow' => $tomorrowData,
            'week' => $weekData
        ]);
    }




    public function fetchVibhaagWiseCount()
    {
        $data = DB::table('complaint')
            ->select(
                'complaint_department as department',
                DB::raw('COUNT(*) as total')
            )
            ->whereNotNull('complaint_department')
            ->where('complaint_department', '!=', '')
            ->where('complaint_department', '!=', '--चुने--')
            ->whereIn('complaint_type', ['समस्या', 'विकास'])
            ->groupBy('complaint_department')
            ->orderBy('total', 'DESC')
            ->get();

        return response()->json($data);
    }

    public function fetchStatus(Request $request)
    {
        $statusLabels = [
            1 => 'शिकायत दर्ज',
            2 => 'प्रक्रिया में',
            3 => 'स्थगित',
            4 => 'पूर्ण',
            5 => 'रद्द',
        ];

        $latestReplyIds = DB::table('complaint_reply')
            ->select('complaint_id', DB::raw('MAX(complaint_reply_id) as latest_id'))
            ->groupBy('complaint_id');

        $query = DB::table('complaint_reply as cr')
            ->joinSub($latestReplyIds, 'latest', function ($join) {
                $join->on('cr.complaint_id', '=', 'latest.complaint_id')
                    ->on('cr.complaint_reply_id', '=', 'latest.latest_id');
            })
            ->join('complaint as c', 'c.complaint_id', '=', 'cr.complaint_id')
            ->whereIn('c.complaint_type', ['समस्या', 'विकास']);

        $filter = $request->input('filter', 'all');
        $dates = match ($filter) {
            'आज' => [Carbon::today()->startOfDay(), Carbon::today()->endOfDay()],
            'कल' => [Carbon::yesterday()->startOfDay(), Carbon::yesterday()->endOfDay()],
            'पिछले सात दिन' => [Carbon::now()->subWeek()->startOfDay(), Carbon::now()->endOfDay()],
            'पिछले तीस दिन' => [Carbon::now()->subMonth()->startOfDay(), Carbon::now()->endOfDay()],
            default => null,
        };

        if ($dates) {
            $query->whereBetween('cr.reply_date', $dates);
        }

        $data = $query
            ->select('cr.complaint_status', DB::raw('COUNT(*) as total'))
            ->groupBy('cr.complaint_status')
            ->orderByDesc('total')
            ->get()
            ->map(function ($item) use ($statusLabels) {
                return [
                    'status' => $statusLabels[$item->complaint_status] ?? 'अन्य',
                    'total' => $item->total,
                ];
            });

        return response()->json($data);
    }

    public function fetchSuchnaStatus(Request $request)
    {
        $statusLabels = [
            11 => 'सूचना प्राप्त',
            12 => 'फॉरवर्ड किया',
            13 => 'सम्मिलित हुए',
            14 => 'सम्मिलित नहीं हुए',
            15 => 'फोन पर संपर्क किया',
            16 => 'ईमेल पर संपर्क किया',
            17 => 'व्हाट्सएप पर संपर्क किया',
            18 => 'रद्द'
        ];

        $latestReplyIds = DB::table('complaint_reply')
            ->select('complaint_id', DB::raw('MAX(complaint_reply_id) as latest_id'))
            ->groupBy('complaint_id');

        $query = DB::table('complaint_reply as cr')
            ->joinSub($latestReplyIds, 'latest', function ($join) {
                $join->on('cr.complaint_id', '=', 'latest.complaint_id')
                    ->on('cr.complaint_reply_id', '=', 'latest.latest_id');
            })
            ->join('complaint as c', 'c.complaint_id', '=', 'cr.complaint_id')
            ->whereIn('c.complaint_type', ['शुभ सुचना', 'अशुभ सुचना']);

        $filter = $request->input('filter', 'all');
        $dates = match ($filter) {
            'आज' => [Carbon::today()->startOfDay(), Carbon::today()->endOfDay()],
            'कल' => [Carbon::yesterday()->startOfDay(), Carbon::yesterday()->endOfDay()],
            'पिछले सात दिन' => [Carbon::now()->subWeek()->startOfDay(), Carbon::now()->endOfDay()],
            'पिछले तीस दिन' => [Carbon::now()->subMonth()->startOfDay(), Carbon::now()->endOfDay()],
            default => null,
        };

        if ($dates) {
            $query->whereBetween('cr.reply_date', $dates);
        }

        $data = $query
            ->select('cr.complaint_status', DB::raw('COUNT(*) as total'))
            ->groupBy('cr.complaint_status')
            ->orderByDesc('total')
            ->get()
            ->map(function ($item) use ($statusLabels) {
                return [
                    'status' => $statusLabels[$item->complaint_status] ?? 'अन्य',
                    'total' => $item->total,
                ];
            });

        return response()->json($data);
    }

    public function fetchDashboardStats()
    {
        $today = now()->toDateString();

        $newVotersToday = DB::table('registration_form')
            ->whereDate('date_time', $today)
            ->where('type', 1)
            ->count();

        $newContactsToday = DB::table('registration_form')
            ->whereDate('date_time', $today)
            ->whereIn('type', [1, 2])
            ->count();

        $totalVoters = DB::table('registration_form')
            ->where('type', 1)
            ->count();

        $totalContacts = DB::table('registration_form')
            ->whereIn('type', [1, 2])
            ->count();

        return response()->json([
            'new_voters'     => $newVotersToday,
            'new_contacts'   => $newContactsToday,
            'total_voters'   => $totalVoters,
            'total_contacts' => $totalContacts,
        ]);
    }

    public function getForwardedCounts()
    {
        $username = session('logged_in_user');

        if (!$username) {
            return response()->json(['error' => 'User not logged in.'], 401);
        }

        $user = \App\Models\User::where('admin_name', $username)->first();

        if (!$user) {
            return response()->json(['error' => 'User not found.'], 404);
        }

        $userId = $user->admin_id;
        $today = now()->toDateString();

        $latestRepliesSub = \App\Models\Reply::selectRaw('MAX(reply_date) as latest_date, complaint_id')
            ->groupBy('complaint_id');

        $latestReplies = \App\Models\Reply::joinSub($latestRepliesSub, 'latest', function ($join) {
            $join->on('complaint_reply.reply_date', '=', 'latest.latest_date')
                ->on('complaint_reply.complaint_id', '=', 'latest.complaint_id');
        })
            ->whereNotNull('forwarded_to')
            ->whereHas('complaint', function ($query) {
                $query->whereNotIn('complaint_status', [4, 5])
                    ->where('complaint_type', 'समस्या');
            })
            ->where(function ($query) use ($today) {
                $query->whereNull('review_date')
                    ->orWhereDate('review_date', '<=', $today);
            })
            ->select('complaint_reply.*');

        $forwardedToYou = (clone $latestReplies)
            ->where('complaint_reply.forwarded_to', $userId)
            ->distinct('complaint_reply.complaint_id')
            ->count('complaint_reply.complaint_id');

        // $forwardedToOthers = (clone $latestReplies)
        //     ->where('complaint_reply.forwarded_to', '!=', $userId)
        //     ->whereNotNull('complaint_reply.forwarded_to')
        //     ->where('complaint_reply.forwarded_to', '!=', 0)
        //     ->distinct('complaint_reply.complaint_id')
        //     ->count('complaint_reply.complaint_id');

        return response()->json([
            'forwarded_to_you' => $forwardedToYou,
            // 'forwarded_to_others' => $forwardedToOthers,
        ]);
    }

    public function getForwardedVikashCounts()
    {
        $username = session('logged_in_user');

        if (!$username) {
            return response()->json(['error' => 'User not logged in.'], 401);
        }

        $user = \App\Models\User::where('admin_name', $username)->first();

        if (!$user) {
            return response()->json(['error' => 'User not found.'], 404);
        }

        $userId = $user->admin_id;
        $today = now()->toDateString();

        $latestRepliesSub = \App\Models\Reply::selectRaw('MAX(reply_date) as latest_date, complaint_id')
            ->groupBy('complaint_id');

        $latestReplies = \App\Models\Reply::joinSub($latestRepliesSub, 'latest', function ($join) {
            $join->on('complaint_reply.reply_date', '=', 'latest.latest_date')
                ->on('complaint_reply.complaint_id', '=', 'latest.complaint_id');
        })
            ->whereNotNull('forwarded_to')
            ->whereHas('complaint', function ($query) {
                $query->whereNotIn('complaint_status', [4, 5])
                    ->where('complaint_type', 'विकास');
            })
            ->where(function ($query) use ($today) {
                $query->whereNull('review_date')
                    ->orWhereDate('review_date', '<=', $today);
            })
            ->select('complaint_reply.*');

        $forwardedToYou = (clone $latestReplies)
            ->where('complaint_reply.forwarded_to', $userId)
            ->distinct('complaint_reply.complaint_id')
            ->count('complaint_reply.complaint_id');

        // $forwardedToOthers = (clone $latestReplies)
        //     ->where('complaint_reply.forwarded_to', '!=', $userId)
        //     ->whereNotNull('complaint_reply.forwarded_to')
        //     ->where('complaint_reply.forwarded_to', '!=', 0)
        //     ->distinct('complaint_reply.complaint_id')
        //     ->count('complaint_reply.complaint_id');

        return response()->json([
            'forwarded_to_you' => $forwardedToYou,
            // 'forwarded_to_others' => $forwardedToOthers,
        ]);
    }



    public function getForwardedComplaintsPerManager()
    {

        $latestRepliesSub = \App\Models\Reply::selectRaw('MAX(reply_date) as latest_date, complaint_id')
            ->groupBy('complaint_id');

        $latestReplies = \App\Models\Reply::joinSub($latestRepliesSub, 'latest', function ($join) {
            $join->on('complaint_reply.reply_date', '=', 'latest.latest_date')
                ->on('complaint_reply.complaint_id', '=', 'latest.complaint_id');
        })
            ->whereNotNull('forwarded_to')
            ->whereHas('complaint', function ($query) {
                $query->whereNotIn('complaint_status', [4, 5]);
            })
            ->with('complaint:complaint_id,complaint_type')
            ->get();

        $counts = $latestReplies->groupBy(['forwarded_to', fn($item) => $item->complaint->complaint_type]);

        $managers = \App\Models\User::where('role', 2)
            ->get(['admin_id', 'admin_name']);

        $result = $managers->map(function ($manager) use ($counts) {
            return [
                'forward' => $manager->admin_name,
                'subh'    => optional($counts[$manager->admin_id]['शुभ सुचना'] ?? null)->count() ?? 0,
                'asubh'   => optional($counts[$manager->admin_id]['अशुभ सुचना'] ?? null)->count() ?? 0,
                'samasya' => optional($counts[$manager->admin_id]['समस्या'] ?? null)->count() ?? 0,
                'vikash'  => optional($counts[$manager->admin_id]['विकास'] ?? null)->count() ?? 0,
            ];
        });

        return response()->json($result);
    }

    public function countUnheardComplaints()
    {
        $validComplaintIdsQuery = DB::table('complaint_reply as cr')
            ->select('cr.complaint_id')
            ->join(DB::raw('(
            SELECT complaint_id, COUNT(*) as reply_count, MIN(complaint_reply_id) as min_id
            FROM complaint_reply
            GROUP BY complaint_id
            HAVING COUNT(*) = 1
        ) as reply_info'), function ($join) {
                $join->on('cr.complaint_id', '=', 'reply_info.complaint_id')
                    ->on('cr.complaint_reply_id', '=', 'reply_info.min_id');
            })
            ->where('cr.complaint_status', 1)
            ->where('cr.complaint_reply', 'शिकायत दर्ज की गई है।')
            ->where('cr.forwarded_to', 6)
            ->whereNull('cr.selected_reply');

        $count = DB::table('complaint')
            ->whereIn('complaint_id', $validComplaintIdsQuery)
            ->where('complaint_type', 'समस्या')
            ->count();

        return response()->json(['count' => $count]);
    }

    public function countUnheardComplaintsVikash()
    {
        $validComplaintIdsQuery = DB::table('complaint_reply as cr')
            ->select('cr.complaint_id')
            ->join(DB::raw('(
            SELECT complaint_id, COUNT(*) as reply_count, MIN(complaint_reply_id) as min_id
            FROM complaint_reply
            GROUP BY complaint_id
            HAVING COUNT(*) = 1
        ) as reply_info'), function ($join) {
                $join->on('cr.complaint_id', '=', 'reply_info.complaint_id')
                    ->on('cr.complaint_reply_id', '=', 'reply_info.min_id');
            })
            ->where('cr.complaint_status', 1)
            ->where('cr.complaint_reply', 'शिकायत दर्ज की गई है।')
            ->where('cr.forwarded_to', 6)
            ->whereNull('cr.selected_reply');

        $count = DB::table('complaint')
            ->whereIn('complaint_id', $validComplaintIdsQuery)
            ->where('complaint_type', 'विकास')
            ->count();

        return response()->json(['count' => $count]);
    }

    public function getNotDoneCounts(Request $request)
    {
        $today = now()->toDateString();

        $complaints = Complaint::with([
            'latestReplyWithoutFollowup',
            'latestNonDefaultReply.latestFollowupAlways',
        ])
            ->whereIn('complaint_type', ['समस्या', 'विकास'])
            ->where('complaint_status', '!=', 5)
            ->whereHas('latestReplyWithoutFollowup')
            ->get()
            ->sortByDesc(fn($c) => optional($c->latestReplyWithoutFollowup)->reply_date)
            ->values();

        $counts = [];

        foreach ($complaints as $complaint) {
            $type = $complaint->complaint_type;

            if (!isset($counts[$type])) {
                $counts[$type] = [
                    'not_done' => 0,
                ];
            }

            // Pick the latest relevant reply
            $latestReply = $complaint->latestReplyWithoutFollowup ?? $complaint->latestNonDefaultReply;
            $latestFollowup = $latestReply?->latestFollowupAlways;

            // Count as not done if no followup exists or followup is today and incomplete
            if (!$latestFollowup || ($latestFollowup->followup_status == 1 && $latestFollowup->followup_date->toDateString() === $today)) {
                $counts[$type]['not_done']++;
            }
        }

        // Format for JSON response
        $result = [];
        foreach ($counts as $type => $val) {
            $result[] = [
                'complaint_type' => $type,
                'not_done' => $val['not_done'],
            ];
        }

        return response()->json($result);
    }

    public function notDoneDetails(Request $request)
    {
        $status = $request->status;
        $type = $request->type ?? null;
        $complaints = Complaint::with([
            'latestReplyWithoutFollowup',
            'latestNonDefaultReply.latestFollowupAlways',
            'registrationDetails',
            'admin',
        ])
            ->whereIn('complaint_type', ['समस्या', 'विकास'])
            ->where('complaint_status', '!=', 5)
            ->when($type, fn($q) => $q->where('complaint_type', $type))
            ->whereHas('latestReplyWithoutFollowup')
            ->get()
            ->sortByDesc(fn($c) => optional($c->latestReplyWithoutFollowup)->reply_date)
            ->values();

        $notDoneComplaints = $complaints->filter(function ($complaint) {
            $latestReply = $complaint->latestReplyWithoutFollowup ?? $complaint->latestNonDefaultReply;
            $latestFollowup = $latestReply?->latestFollowupAlways;

            return !$latestFollowup || ($latestFollowup->followup_status == 1 && $latestFollowup->followup_date->toDateString() === now()->toDateString());
        });

        return view('admin.followup_notdone_details', [
            'complaints' => $notDoneComplaints,
            'status' => $status,
            'type' => $type,
        ]);
    }


    public function getFollowupCounts(Request $request)
    {
        $today = Carbon::today();
        $dateFilter = $request->filter ?? null;
        $operatorId = $request->operator ?? 'सभी';

        $complaints = Complaint::with([
            'latestReplyWithoutFollowup',
            'latestNonDefaultReply.latestFollowup',
            'replies.followups'
        ])
            ->whereIn('complaint_type', ['समस्या', 'विकास'])
            ->where('complaint_status', '!=', 5)
            ->get();

        $dateRanges = [
            'आज' => [Carbon::today()->startOfDay(), Carbon::today()->endOfDay()],
            'कल' => [Carbon::yesterday()->startOfDay(), Carbon::yesterday()->endOfDay()],
            'पिछले सात दिन' => [Carbon::now()->subWeek()->startOfDay(), Carbon::now()->endOfDay()],
            'पिछले तीस दिन' => [Carbon::now()->subMonth()->startOfDay(), Carbon::now()->endOfDay()],
        ];


        if ($dateFilter && isset($dateRanges[$dateFilter])) {
            [$start, $end] = $dateRanges[$dateFilter];

            $complaints = $complaints->filter(function ($complaint) use ($start, $end) {
                $followup = $complaint->latestReplyWithoutFollowup?->latestFollowup
                    ?? $complaint->latestNonDefaultReply?->latestFollowup;

                if (!$followup) return false;

                $followupDate = Carbon::parse($followup->followup_date);

                return $followupDate->between($start, $end);
            });
        }

        if ($operatorId !== 'सभी') {
            $complaints = $complaints->filter(function ($complaint) use ($operatorId) {
                // Get the latest relevant reply
                $latestReply = $complaint->latestReplyWithoutFollowup ?? $complaint->latestNonDefaultReply;
                if (!$latestReply) return false;

                // Get the latest followup for this reply
                $latestFollowup = $latestReply->latestFollowup;
                if (!$latestFollowup) return false;

                // Compare operator ID
                return $latestFollowup->followup_created_by == $operatorId;
            });
        }


        $counts = [];

        foreach ($complaints as $complaint) {
            $type = $complaint->complaint_type;

            if (!isset($counts[$type])) {
                $counts[$type] = [
                    'completed' => 0,
                    'pending' => 0,
                    'in_process' => 0,
                    'not_done' => 0,
                ];
            }

            // Pick **the latest relevant reply**: either without followup or non-default with followup
            $latestReply = $complaint->latestReplyWithoutFollowup ?? $complaint->latestNonDefaultReply;

            // Get latest followup for this reply, if any
            $latestFollowup = $latestReply?->latestFollowup ?? null;

            if (!$latestFollowup) {
                $counts[$type]['not_done']++;
                continue;
            }

            switch ($latestFollowup->followup_status) {
                case 2: // Completed
                    $counts[$type]['completed']++;
                    break;

                case 1: // Followup done
                    if ($latestFollowup->followup_date->toDateString() === $today) {
                        $counts[$type]['pending']++;
                    } else {
                        // Check if there is a newer reply after this latest followup
                        $hasNewReplyAfterFollowup = $complaint->replies()
                            ->where('reply_date', '>', $latestFollowup->followup_date)
                            ->where('complaint_reply', '!=', 'शिकायत दर्ज की गई है।')
                            ->exists();

                        if ($hasNewReplyAfterFollowup) {
                            $counts[$type]['not_done']++;
                        } else {
                            $counts[$type]['in_process']++;
                        }
                    }
                    break;

                case 0: // Not done
                default:
                    $counts[$type]['in_process']++;
                    break;
            }
        }

        $result = [];
        foreach ($counts as $type => $vals) {
            $result[] = [
                'complaint_type' => $type,
                'completed' => $vals['completed'],
                'in_process' => $vals['in_process'],
            ];
        }

        if ($request->ajax()) {
            return response()->json($result);
        }

        return view('admin.page');
    }


    public function followupDetails(Request $request)
    {
        $status = $request->status;
        $type = $request->type ?? null;
        $dateFilter = $request->filter ?? null;
        $operatorId = $request->operator ?? 'सभी';

        $today = Carbon::today();

        $complaints = Complaint::with([
            'latestReplyWithoutFollowup.replyfrom',
            'latestReplyWithoutFollowup.forwardedToManager',
            'latestNonDefaultReply.latestFollowup',
            'latestNonDefaultReply.replyfrom',
        ])
            ->whereIn('complaint_type', ['समस्या', 'विकास'])
            ->when($type, fn($q) => $q->where('complaint_type', $type))
            ->where('complaint_status', '!=', 5)
            ->get()
            ->map(function ($complaint) {
                $complaint->latestRelevantReply = $complaint->latestReplyWithoutFollowup ?? $complaint->latestNonDefaultReply;
                return $complaint;
            });



        $dateRanges = [
            'आज' => [Carbon::today()->startOfDay(), Carbon::today()->endOfDay()],
            'कल' => [Carbon::yesterday()->startOfDay(), Carbon::yesterday()->endOfDay()],
            'पिछले सात दिन' => [Carbon::now()->subWeek()->startOfDay(), Carbon::now()->endOfDay()],
            'पिछले तीस दिन' => [Carbon::now()->subMonth()->startOfDay(), Carbon::now()->endOfDay()],
        ];

        // Apply date filter if selected
        if ($dateFilter && isset($dateRanges[$dateFilter])) {
            [$start, $end] = $dateRanges[$dateFilter];

            $complaints = $complaints->filter(function ($complaint) use ($start, $end) {
                $latestReply = $complaint->latestRelevantReply;
                $latestFollowup = $latestReply?->latestFollowup ?? null;

                if (!$latestFollowup) return false;

                $followupDate = Carbon::parse($latestFollowup->followup_date);

                return $followupDate->between($start, $end);
            });
        }

        if ($operatorId !== 'सभी') {
            $complaints = $complaints->filter(function ($complaint) use ($operatorId) {
                // Get the latest relevant reply
                $latestReply = $complaint->latestReplyWithoutFollowup ?? $complaint->latestNonDefaultReply;
                if (!$latestReply) return false;

                // Get the latest followup for this reply
                $latestFollowup = $latestReply->latestFollowup;
                if (!$latestFollowup) return false;

                // Compare operator ID
                return $latestFollowup->followup_created_by == $operatorId;
            });
        }

        $operatorName = 'सभी';
        if ($operatorId !== 'सभी') {
            $operator = User::find($operatorId);
            if ($operator) {
                $operatorName = $operator->admin_name;
            }
        }

        $complaints = $complaints->filter(function ($complaint) use ($status, $today) {
            $latestReply = $complaint->latestRelevantReply;
            $latestFollowup = $latestReply?->latestFollowup ?? null;

            switch ($status) {
                case 'completed':
                    return $latestFollowup && $latestFollowup->followup_status == 2;
                case 'pending':
                    return $latestFollowup && $latestFollowup->followup_status == 1
                        && $latestFollowup->followup_date->toDateString() === $today;
                case 'in_process':
                    if (!$latestFollowup) return false;
                    if ($latestFollowup->followup_status == 1 && $latestFollowup->followup_date->toDateString() < $today) {
                        // Ensure no newer reply exists after latest followup
                        $hasNewReplyAfterFollowup = $latestReply->complaint->replies()
                            ->where('reply_date', '>', $latestFollowup->followup_date)
                            ->where('complaint_reply', '!=', 'शिकायत दर्ज की गई है।')
                            ->exists();
                        return !$hasNewReplyAfterFollowup;
                    }
                    return $latestFollowup->followup_status == 0;
                case 'not_done':
                    return !$latestFollowup;
                default:
                    return true;
            }
        });

        $complaints = $complaints->sortByDesc(function ($complaint) {
            $latestFollowup = $complaint->latestRelevantReply?->latestFollowup;
            return $latestFollowup?->followup_date?->timestamp ?? 0;
        })->values();

        return view('admin/followup_details', compact('complaints', 'status', 'type', 'dateFilter', 'operatorId', 'operatorName'));
    }





    public function sectionView($section, Request $request)
    {
        $now = Carbon::now();
        $complaints = collect();
        $type = $request->query('type');
        $user = $request->query('user');
        $title = 'शिकायतें';


        $loadForwardedTo = function ($complaints) {
            foreach ($complaints as $complaint) {
                if (!in_array($complaint->complaint_status, [4, 5])) {
                    $complaint->pending_days = Carbon::parse($complaint->posted_date)->diffInDays(now());
                } else {
                    $complaint->pending_days = 0;
                }

                $lastReply = $complaint->replies()
                    ->with('forwardedToManager')
                    ->whereNotNull('forwarded_to')
                    ->orderByDesc('reply_date')
                    ->first();

                $complaint->forwarded_to_name = $lastReply?->forwardedToManager?->admin_name ?? '-';
                $complaint->forwarded_to_date = $lastReply?->reply_date?->format('d-m-Y H:i') ?? '-';
            }

            return $complaints;
        };

        switch ($section) {
            case 'today':
                $start = $now->copy()->startOfDay();
                $end = $now->copy()->endOfDay();
                $complaints = $this->getComplaintsBetween($start, $end, $type, $user);
                foreach ($complaints as $complaint) {
                    if (!in_array($complaint->complaint_status, [4, 5])) {
                        $complaint->pending_days = Carbon::parse($complaint->posted_date)->diffInDays(now());
                    } else {
                        $complaint->pending_days = 0;
                    }
                }
                $title .= ' (आज)';
                break;

            case 'yesterday':
                $start = $now->copy()->subDay()->startOfDay();
                $end = $now->copy()->subDay()->endOfDay();
                $complaints = $this->getComplaintsBetween($start, $end, $type, $user);
                $complaints = $loadForwardedTo($complaints);
                $title .= ' (कल)';
                break;

            case 'current-week':
                $start = $now->copy()->startOfWeek();
                $end = $now->copy()->endOfWeek();
                $complaints = $this->getComplaintsBetween($start, $end, $type, $user);
                $complaints = $loadForwardedTo($complaints);
                $title .= ' (इस सप्ताह)';
                break;

            case 'current-month':
                $start = $now->copy()->startOfMonth();
                $end = $now;
                $complaints = $this->getComplaintsBetween($start, $end, $type, $user);
                $complaints = $loadForwardedTo($complaints);
                $title .= ' (इस माह)';
                break;
            case 'all':
                $start = Carbon::create(2000, 1, 1)->startOfDay();
                $end = Carbon::now()->endOfDay();

                $complaints = $this->getComplaintsBetween($start, $end, $type, $user);
                $complaints = $loadForwardedTo($complaints);
                $title = 'सभी शिकायतें';
                break;


            case 'vibhag-details':
                $department = $request->query('department');

                if (!$department) {
                    abort(400, 'Department not specified');
                }

                $complaints = Complaint::where('complaint_department', $department)
                    ->orderBy('posted_date', 'desc')
                    ->get();

                $complaints = $loadForwardedTo($complaints);
                $title = "शिकायतें (विभाग: " . $department . ")";
                break;

            case 'status-details':
                $statusMap = [
                    'शिकायत दर्ज' => 1,
                    'प्रक्रिया में' => 2,
                    'स्थगित' => 3,
                    'पूर्ण' => 4,
                    'रद्द' => 5,
                ];

                $label = $request->query('status');
                $filter = $request->query('filter', 'सभी');

                $statusCode = collect($statusMap)
                    ->filter(fn($v, $k) => trim($k) === trim($label))
                    ->first();

                if ($statusCode === false) {
                    abort(400, 'Invalid status');
                }

                $latestReplyIds = DB::table('complaint_reply')
                    ->select('complaint_id', DB::raw('MAX(complaint_reply_id) as latest_id'))
                    ->groupBy('complaint_id');

                $query = DB::table('complaint_reply as cr')
                    ->joinSub($latestReplyIds, 'latest', function ($join) {
                        $join->on('cr.complaint_id', '=', 'latest.complaint_id')
                            ->on('cr.complaint_reply_id', '=', 'latest.latest_id');
                    })
                    ->join('complaint as c', 'c.complaint_id', '=', 'cr.complaint_id')
                    ->where('cr.complaint_status', $statusCode)
                    ->whereIn('c.complaint_type', ['समस्या', 'विकास']);

                $dates = match ($filter) {
                    'आज' => [Carbon::today()->startOfDay(), Carbon::today()->endOfDay()],
                    'कल' => [Carbon::yesterday()->startOfDay(), Carbon::yesterday()->endOfDay()],
                    'पिछले सात दिन' => [Carbon::now()->subWeek()->startOfDay(), Carbon::now()->endOfDay()],
                    'पिछले तीस दिन' => [Carbon::now()->subMonth()->startOfDay(), Carbon::now()->endOfDay()],
                    default => null,
                };

                if ($dates) {
                    $query->whereBetween('cr.reply_date', $dates);
                }

                $complaintIds = $query->pluck('c.complaint_id');

                $complaints = Complaint::whereIn('complaint_id', $complaintIds)
                    ->orderBy('posted_date', 'desc')
                    ->get();

                $complaints = $loadForwardedTo($complaints);

                $title = "शिकायतें ({$label}) - {$filter}";
                break;

            case 'suchna-status-details':
                $statusMap = [
                    'सूचना प्राप्त' => 11,
                    'फॉरवर्ड किया' => 12,
                    'सम्मिलित हुए' => 13,
                    'सम्मिलित नहीं हुए' => 14,
                    'फोन पर संपर्क किया' => 15,
                    'ईमेल पर संपर्क किया' => 16,
                    'व्हाट्सएप पर संपर्क किया' => 17,
                    'रद्द' => 18
                ];

                $label = $request->query('status');
                $filter = $request->query('filter', 'सभी');

                $statusCode = collect($statusMap)
                    ->filter(fn($v, $k) => trim($k) === trim($label))
                    ->first();

                if ($statusCode === false) {
                    abort(400, 'Invalid status');
                }

                $latestReplyIds = DB::table('complaint_reply')
                    ->select('complaint_id', DB::raw('MAX(complaint_reply_id) as latest_id'))
                    ->groupBy('complaint_id');

                $query = DB::table('complaint_reply as cr')
                    ->joinSub($latestReplyIds, 'latest', function ($join) {
                        $join->on('cr.complaint_id', '=', 'latest.complaint_id')
                            ->on('cr.complaint_reply_id', '=', 'latest.latest_id');
                    })
                    ->join('complaint as c', 'c.complaint_id', '=', 'cr.complaint_id')
                    ->where('cr.complaint_status', $statusCode)
                    ->whereIn('c.complaint_type', ['शुभ सुचना', 'अशुभ सुचना']);

                $dates = match ($filter) {
                    'आज' => [Carbon::today()->startOfDay(), Carbon::today()->endOfDay()],
                    'कल' => [Carbon::yesterday()->startOfDay(), Carbon::yesterday()->endOfDay()],
                    'पिछले सात दिन' => [Carbon::now()->subWeek()->startOfDay(), Carbon::now()->endOfDay()],
                    'पिछले तीस दिन' => [Carbon::now()->subMonth()->startOfDay(), Carbon::now()->endOfDay()],
                    default => null,
                };

                if ($dates) {
                    $query->whereBetween('cr.reply_date', $dates);
                }

                $complaintIds = $query->pluck('c.complaint_id');

                $complaints = Complaint::whereIn('complaint_id', $complaintIds)
                    ->orderBy('posted_date', 'desc')
                    ->get();

                $complaints = $loadForwardedTo($complaints);

                $title = "सूचनाएँ ({$label}) - {$filter}";
                break;

            case 'date-wise':
                $date = $request->query('date');
                if (!$date) {
                    abort(400, 'Invalid date');
                }

                $start = Carbon::parse($date)->startOfDay();
                $end = Carbon::parse($date)->endOfDay();
                $loggedInId = session('user_id');

                $complaints = Complaint::with('latestReply')
                    ->whereBetween('program_date', [$start, $end])
                    ->whereIn('complaint_type', ['शुभ सुचना', 'अशुभ सुचना'])
                    ->whereHas('latestReply', function ($q) use ($loggedInId) {
                        $q->where('forwarded_to', $loggedInId);
                    })
                    ->get();

                // $complaints = $this->getComplaintsBetween($start, $end, $type, $user)->filter(function ($complaint) {
                //     return in_array($complaint->complaint_type, ['शुभ सुचना', 'अशुभ सुचना']);
                // });
                $complaints = $loadForwardedTo($complaints);
                $title = 'सुचना (' . Carbon::parse($date)->format('d M Y') . ')';
                break;

            case 'forwarded':
                $user = \App\Models\User::where('admin_name', session('logged_in_user'))->first();

                if (!$user) {
                    abort(403, 'User not logged in.');
                }

                $userId = $user->admin_id;
                $today = now()->toDateString();
                $direction = $request->query('direction');

                $latestReplies = \App\Models\Reply::selectRaw('MAX(reply_date) as latest_date, complaint_id')
                    ->groupBy('complaint_id');

                $latestForwardedReplies = \App\Models\Reply::joinSub($latestReplies, 'latest', function ($join) {
                    $join->on('complaint_reply.reply_date', '=', 'latest.latest_date')
                        ->on('complaint_reply.complaint_id', '=', 'latest.complaint_id');
                })
                    ->whereNotNull('forwarded_to')
                    ->whereHas('complaint', function ($query) {
                        $query->whereNotIn('complaint_status', [4, 5])
                            ->where('complaint_type', 'समस्या');
                    })
                    ->where(function ($query) use ($today) {
                        $query->whereNull('review_date')
                            ->orWhereDate('review_date', '<=', $today);
                    })
                    ->with('complaint.replies');

                if ($direction === 'to') {
                    $latestForwardedReplies = $latestForwardedReplies->where('forwarded_to', $userId);
                    $title = 'आपको निर्देशित शिकायतें';
                } elseif ($direction === 'others') {
                    $latestForwardedReplies = $latestForwardedReplies->where('forwarded_to', '!=', $userId)->where('forwarded_to', '!=', 0);
                    $title = 'अन्य को निर्देशित शिकायतें';
                }

                $replies = $latestForwardedReplies->get();


                $complaints = $replies
                    ->pluck('complaint')
                    ->filter()
                    ->unique('complaint_id')
                    ->sortByDesc('posted_date')
                    ->values();
                $complaints = $loadForwardedTo($complaints);

                break;

            case 'forwardedvikash':
                $user = \App\Models\User::where('admin_name', session('logged_in_user'))->first();

                if (!$user) {
                    abort(403, 'User not logged in.');
                }

                $userId = $user->admin_id;
                $direction = $request->query('direction');
                $today = now()->toDateString();

                $latestReplies = \App\Models\Reply::selectRaw('MAX(reply_date) as latest_date, complaint_id')
                    ->groupBy('complaint_id');

                $latestForwardedReplies = \App\Models\Reply::joinSub($latestReplies, 'latest', function ($join) {
                    $join->on('complaint_reply.reply_date', '=', 'latest.latest_date')
                        ->on('complaint_reply.complaint_id', '=', 'latest.complaint_id');
                })
                    ->whereNotNull('forwarded_to')
                    ->whereHas('complaint', function ($query) {
                        $query->whereNotIn('complaint_status', [4, 5])
                            ->where('complaint_type', 'विकास');
                    })
                    ->where(function ($query) use ($today) {
                        $query->whereNull('review_date')
                            ->orWhereDate('review_date', '<=', $today);
                    })
                    ->with('complaint.replies');

                if ($direction === 'to') {
                    $latestForwardedReplies = $latestForwardedReplies->where('forwarded_to', $userId);
                    $title = 'आपको निर्देशित';
                } elseif ($direction === 'others') {
                    $latestForwardedReplies = $latestForwardedReplies->where('forwarded_to', '!=', $userId)->where('forwarded_to', '!=', 0);
                    $title = 'अन्य को निर्देशित';
                }

                $replies = $latestForwardedReplies->get();


                $complaints = $replies
                    ->pluck('complaint')
                    ->filter()
                    ->unique('complaint_id')
                    ->sortByDesc('posted_date')
                    ->values();
                $complaints = $loadForwardedTo($complaints);

                break;

            case 'forward-details':
                $managerName = $request->query('forward');
                $type = $request->query('type'); // complaint type from query string

                if (!$managerName) {
                    abort(400, 'Manager name not provided.');
                }

                $manager = \App\Models\User::where('admin_name', $managerName)
                    ->where('role', 2)
                    ->first();

                if (!$manager) {
                    abort(404, 'Manager not found.');
                }

                $managerId = $manager->admin_id;

                // Subquery to get latest reply per complaint
                $latestReplies = \App\Models\Reply::selectRaw('MAX(reply_date) as latest_date, complaint_id')
                    ->groupBy('complaint_id');

                $latestForwardedReplies = \App\Models\Reply::joinSub($latestReplies, 'latest', function ($join) {
                    $join->on('complaint_reply.reply_date', '=', 'latest.latest_date')
                        ->on('complaint_reply.complaint_id', '=', 'latest.complaint_id');
                })
                    ->where('complaint_reply.forwarded_to', $managerId)
                    ->whereHas('complaint', function ($query) use ($type) {
                        $query->whereNotIn('complaint_status', [4, 5]);

                        if ($type) {
                            // If type filter is provided
                            $query->where('complaint_type', $type);
                        }
                    })
                    ->with('complaint.replies');

                $replies = $latestForwardedReplies->get();

                $complaints = $replies
                    ->pluck('complaint')
                    ->filter()
                    ->unique('complaint_id')
                    ->sortByDesc('posted_date')
                    ->values();

                $complaints = $loadForwardedTo($complaints);

                $title = "निर्देशित शिकायतें (" . $managerName . ")";
                if ($type) {
                    $title .= " - " . $type;
                }
                break;

            case 'not_opened':

                $validComplaintIds = DB::table('complaint_reply as cr')
                    ->select('cr.complaint_id')
                    ->join(DB::raw('(
                            SELECT complaint_id, COUNT(*) as reply_count, MIN(complaint_reply_id) as min_id
                            FROM complaint_reply
                            GROUP BY complaint_id
                            HAVING COUNT(*) = 1
                        ) as reply_info'), function ($join) {
                        $join->on('cr.complaint_id', '=', 'reply_info.complaint_id')
                            ->on('cr.complaint_reply_id', '=', 'reply_info.min_id');
                    })
                    ->where('cr.complaint_status', 1)
                    ->where('cr.complaint_reply', 'शिकायत दर्ज की गई है।')
                    ->where('cr.forwarded_to', 6)
                    ->whereNull('cr.selected_reply');

                $complaints = Complaint::whereIn('complaint_id', $validComplaintIds->pluck('complaint_id'))
                    ->where('complaint_type', 'समस्या')
                    ->orderBy('posted_date', 'desc')
                    ->get();

                $complaints = $loadForwardedTo($complaints);

                $title = "अनसुनी शिकायतें";
                break;

            case 'not_opened_vikash':

                $validComplaintIds = DB::table('complaint_reply as cr')
                    ->select('cr.complaint_id')
                    ->join(DB::raw('(
                            SELECT complaint_id, COUNT(*) as reply_count, MIN(complaint_reply_id) as min_id
                            FROM complaint_reply
                            GROUP BY complaint_id
                            HAVING COUNT(*) = 1
                        ) as reply_info'), function ($join) {
                        $join->on('cr.complaint_id', '=', 'reply_info.complaint_id')
                            ->on('cr.complaint_reply_id', '=', 'reply_info.min_id');
                    })
                    ->where('cr.complaint_status', 1)
                    ->where('cr.complaint_reply', 'शिकायत दर्ज की गई है।')
                    ->where('cr.forwarded_to', 6)
                    ->whereNull('cr.selected_reply');

                $complaints = Complaint::whereIn('complaint_id', $validComplaintIds->pluck('complaint_id'))
                    ->where('complaint_type', 'विकास')
                    ->orderBy('posted_date', 'desc')
                    ->get();

                $complaints = $loadForwardedTo($complaints);

                $title = "अनसुनी";
                break;

            default:
                abort(404);
        }

        return view('admin/dashboard_details', compact('complaints', 'title', 'section'));
    }

    private function getComplaintsBetween($start, $end, $type = null, $user = null)
    {
        $query = Complaint::with(['division', 'district', 'vidhansabha', 'mandal', 'gram', 'polling', 'area', 'registrationDetails', 'admin', 'latestReply'])
            ->whereBetween('posted_date', [$start, $end]);


        if ($type) {
            $query->where('complaint_type', $type);
        }

        if ($user) {
            $userType = $user === 'commander' ? 1 : ($user === 'operator' ? 2 : null);
            if ($userType !== null) {
                $query->where('type', $userType);
            }
        }

        return $query->orderBy('posted_date', 'desc')->get();
    }


    public function voterDetails(Request $request)
    {
        $filter = $request->query('filter');

        $query = RegistrationForm::with(['position', 'step2', 'step2.area', 'step3', 'step4']);

        switch ($filter) {
            case 'today-voters':
                $title = 'आज के मतदाता';
                $query->whereDate('date_time', now())->where('type', 1);
                break;

            case 'today-contacts':
                $title = 'आज के संपर्क';
                $query->whereDate('date_time', now())->whereIn('type', [1, 2]);
                break;

            case 'total-voters':
                $title = 'कुल मतदाता';
                $query->where('type', 1);
                break;

            case 'total-contacts':
                $title = 'कुल संपर्क';
                $query->whereIn('type', [1, 2]);
                break;

            default:
                abort(404);
        }


        if ($request->ajax()) {
            $start = $request->input('start');
            $length = $request->input('length');
            $draw = $request->input('draw');

            $total = $query->count();

            $data = $query->orderBy('date_time', 'desc')
                ->skip($start)
                ->take($length)
                ->get();

            $results = [];

            $serial = $start + 1;

            foreach ($data as $voter) {
                $viewUrl = route('voter.show', $voter->registration_id);
                $deleteUrl = route('register.destroy', $voter->registration_id);

                $actionButtons = '
                    <div class="d-flex gap-1">
                        <a href="' . $viewUrl . '" class="btn btn-sm btn-success mr-2">View</a>
                        <form action="' . $deleteUrl . '" method="POST" onsubmit="return confirm(\'क्या आप वाकई रिकॉर्ड हटाना चाहते हैं?\')">
                            ' . csrf_field() . method_field('DELETE') . '
                            <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                        </form>
                    </div>
                ';

                $results[] = [
                    $serial++,
                    $voter->name,
                    $voter->father_name,
                    $voter->step2->house ?? '',
                    $voter->age,
                    $voter->gender,
                    $voter->voter_id,
                    $voter->step2->area->area_name ?? '-',
                    $voter->jati,
                    $voter->step2->matdan_kendra_no ?? '',
                    $voter->step3->total_member ?? '',
                    $voter->step3->mukhiya_mobile ?? '',
                    $voter->death_left ?? '',
                    \Carbon\Carbon::parse($voter->date_time)->format('d-m-Y'),
                    $actionButtons
                ];
            }

            return response()->json([
                'draw' => intval($draw),
                'recordsTotal' => $total,
                'recordsFiltered' => $total,
                'data' => $results,
            ]);
        }


        // $entries = $query->orderBy('date_time', 'desc')->paginate($perPage)->withQueryString();

        return view('admin/contact_voter_details', compact('title'));
    }

    public function downloadVoters(Request $request)
    {
        $filter = $request->query('filter');
        $query = RegistrationForm::with(['step2.area', 'step3']);

        switch ($filter) {
            case 'today-voters':
                $query->whereDate('date_time', now())->where('type', 1);
                break;
            case 'today-contacts':
                $query->whereDate('date_time', now())->whereIn('type', [1, 2]);
                break;
            case 'total-voters':
                $query->where('type', 1);
                break;
            case 'total-contacts':
                $query->whereIn('type', [1, 2]);
                break;
            default:
                abort(404);
        }

        $data = $query->get();

        $csvData = [];
        foreach ($data as $voter) {
            $csvData[] = [
                'नाम' => $voter->name,
                'पिता/पति' => $voter->father_name,
                'मकान क्र.' => $voter->step2->house ?? '',
                'उम्र' => $voter->age,
                'लिंग' => $voter->gender,
                'मतदाता आईडी' => $voter->voter_id,
                'मतदान क्षेत्र' => $voter->step2->area->area_name ?? '',
                'जाति' => $voter->jati,
                'मतदान क्र.' => $voter->step2->matdan_kendra_no ?? '',
                'कुल सदस्य' => $voter->step3->total_member ?? '',
                'मुखिया मोबाइल' => $voter->step3->mukhiya_mobile ?? '',
                'मृत्यु/स्थानांतरित' => $voter->death_left ?? '',
                'दिनांक' => \Carbon\Carbon::parse($voter->date_time)->format('d-m-Y'),
            ];
        }

        $filename = 'voter_data_' . now()->format('Ymd_His') . '.csv';

        $handle = fopen('php://output', 'w');
        ob_start();
        fputcsv($handle, array_keys($csvData[0] ?? []));
        foreach ($csvData as $row) {
            fputcsv($handle, $row);
        }
        $csv = ob_get_clean();

        return response($csv)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    public function detail_suchna($id)
    {
        $complaint = Complaint::with(
            'registration',
            'division',
            'district',
            'vidhansabha',
            'mandal',
            'gram',
            'polling',
            'area',
            'admin',
            'registrationDetails'
        )->findOrFail($id);

        // Choose applicant name based on type
        $aavedak = $complaint->type == 2
            ? ($complaint->admin->admin_name ?? '-')
            : ($complaint->registrationDetails->name ?? '-');

        return response()->json([
            'name' => $complaint->name,
            'mobile_number' => $complaint->mobile_number,
            'area' => $complaint->area,
            'issue_description' => $complaint->issue_description,
            'voter_id' => $complaint->voter_id,
            'program_date' => $complaint->program_date,
            'news_time' => $complaint->news_time,
            'complaint_type' => $complaint->complaint_type,
            'complaint_number' => $complaint->complaint_number,
            'status_text' => $complaint->statusText(),
            'aavedak' => $aavedak,
            'issue_attachment' => $complaint->issue_attachment,
        ]);
    }


    public function usercreate()
    {
        $admins = User::all();
        return view('admin/create_user', compact('admins'));
    }

    public function userstore(Request $request)
    {
        $request->validate([
            'admin_name' => 'required|string|max:255',
            'admin_pass' => 'required|string|min:4',
            'role' => 'required|in:1,2,3',
        ]);

        User::create([
            'admin_name' => $request->admin_name,
            'admin_pass' => Hash::make($request->admin_pass),
            'role' => $request->role,
            'posted_date' => now(),
        ]);

        if ($request->ajax()) {
            return response()->json(['message' => 'एडमिन जोड़ा गया।']);
        }

        return redirect()->route('user.create')->with('success', 'एडमिन जोड़ा गया।');
    }

    public function userupdate(Request $request, User $user)
    {
        $request->validate([
            'admin_name' => 'required|string|max:255',
            'admin_pass' => 'nullable|string|min:4',
            'role' => 'required|in:2,3',
        ]);

        $user->admin_name = $request->admin_name;

        if ($request->filled('admin_pass')) {
            $user->admin_pass = $request->admin_pass;
        }

        $user->modify_at = now();
        $user->save();

        return redirect()->route('user.create')->with('success', 'एडमिन अपडेट हुआ।');
    }

    public function userdestroy(User $user)
    {
        $user->delete();
        return redirect()->route('user.create')->with('success', 'एडमिन हटाया गया।');
    }

    public function index()
    {
        $districts = District::all();
        $divisions = Division::all();
        $jaties = Jati::all();
        $categories = Category::all();
        $religions = Religion::all();
        $educations = Education::all();
        $businesses = Business::all();
        $politics = Politics::all();
        $interests = Interest::all();

        $jatis = RegistrationForm::select('jati')->distinct()->get();

        return view('admin/dashboard', compact('districts', 'jatis', 'divisions', 'jaties', 'categories', 'religions', 'educations', 'businesses', 'politics', 'interests'));
    }

    // public function filter(Request $request)
    // {
    //     $start = $request->input('start', 0);
    //     $length = $request->input('length', 10);

    //     $query = \DB::table('registration_form as reg')
    //         ->join('step2 as st', 'reg.registration_id', '=', 'st.registration_id')
    //         ->join('step3 as st3', 'reg.registration_id', '=', 'st3.registration_id')
    //         ->join('step4 as st4', 'reg.registration_id', '=', 'st4.registration_id');

    //     if ($request->main_mobile) {
    //         $query->where('reg.member_id', $request->main_mobile);
    //     }
    //     if ($request->name) {
    //         $query->where('reg.name', $request->name);
    //     }
    //     if ($request->gender) {
    //         $query->where('reg.gender', $request->gender);
    //     }
    //     if ($request->category) {
    //         $query->where('reg.caste', $request->category);
    //     }
    //     if ($request->business) {
    //         $query->where('reg.business', $request->business);
    //     }
    //     if ($request->district) {
    //         $query->where('st.district', $request->district);
    //     }
    //     if ($request->txtvidhansabha) {
    //         $query->where('st.vidhansabha', $request->txtvidhansabha);
    //     }
    //     if ($request->mandal) {
    //         $query->where('st.mandal', $request->mandal);
    //     }
    //     if ($request->txtjati) {
    //         $query->where('reg.jati', $request->txtjati);
    //     }
    //     if ($request->religion) {
    //         $query->where('reg.religion', $request->religion);
    //     }
    //     if ($request->from_age && $request->to_age) {
    //         $query->whereBetween('reg.age', [$request->from_age, $request->to_age]);
    //     }
    //     if ($request->education) {
    //         $query->where('reg.education', $request->education);
    //     }
    //     if ($request->party_name) {
    //         $query->where('st4.party_name', $request->party_name);
    //     }
    //     if ($request->membership) {
    //         $query->where('reg.membership', $request->membership);
    //     }
    //     if ($request->interest_area) {
    //         $query->where('st3.intrest', $request->interest_area);
    //     }
    //     if ($request->family_member) {
    //         $range = explode(' AND ', $request->family_member);
    //         if (count($range) === 2) {
    //             $query->whereBetween('st3.total_member', $range);
    //         }
    //     }
    //     if ($request->vehicle) {
    //         $query->where('st3.' . $request->vehicle, '>', 0);
    //     }
    //     if ($request->whatsapp !== null) {
    //         $query->where('reg.mobile1_whatsapp', $request->whatsapp);
    //     }

    //     $total = $query->count();

    //     $registrations = $query
    //         ->select('reg.registration_id', 'reg.member_id', 'reg.name', 'reg.mobile1', 'reg.gender', 'reg.date_time')
    //         ->orderBy('reg.registration_id', 'desc')
    //         ->offset($start)
    //         ->limit($length)
    //         ->get();

    //     $x = $start;
    //     foreach ($registrations as $row) {
    //         $x++;
    //         $date = date('d-m-Y', strtotime($row->date_time));
    //         $html .= '<tr>';
    //         $html .= '<td>' . $x . '</td>';
    //         $html .= '<td>' . $row->member_id . '</td>';
    //         $html .= '<td>' . $row->name . '</td>';
    //         $html .= '<td>' . $row->mobile1 . '</td>';
    //         $html .= '<td>' . $row->gender . '</td>';
    //         $html .= '<td>' . $date . '</td>';
    //         $html .= '<td style="white-space: nowrap;">
    //           <a href="' . route('register.show', $row->registration_id) . '" class="btn btn-sm btn-success">View</a>
    //           <a href="' . route('register.card', $row->registration_id) . '" class="btn btn-sm btn-primary">Card</a>
    //           <a href="' . route('register.destroy', $row->registration_id) . '" class="btn btn-sm btn-danger">Delete</a>
    //         </td>';
    //         $html .= '</tr>';
    //     }

    //     $html .= '</tbody></table>';

    //     return response()->json([
    //         'html' => $html,
    //         'count' => $total,
    //     ]);
    // }


    public function filter(Request $request)
    {
        $start = $request->input('start', 0);
        $length = $request->input('length', 10);

        $query = \DB::table('registration_form as reg')
            ->join('step2 as st', 'reg.registration_id', '=', 'st.registration_id')
            ->join('step3 as st3', 'reg.registration_id', '=', 'st3.registration_id')
            ->join('step4 as st4', 'reg.registration_id', '=', 'st4.registration_id');

        if ($request->main_mobile) {
            $query->where('reg.member_id', $request->main_mobile);
        }
        if ($request->name) {
            $query->where('reg.name', $request->name);
        }
        if ($request->gender) {
            $query->where('reg.gender', $request->gender);
        }
        if ($request->category) {
            $query->where('reg.caste', $request->category);
        }
        if ($request->business) {
            $query->where('reg.business', $request->business);
        }
        if ($request->district) {
            $query->where('st.district', $request->district);
        }
        if ($request->txtvidhansabha) {
            $query->where('st.vidhansabha', $request->txtvidhansabha);
        }
        if ($request->mandal) {
            $query->where('st.mandal', $request->mandal);
        }
        if ($request->txtjati) {
            $query->where('reg.jati', $request->txtjati);
        }
        if ($request->religion) {
            $query->where('reg.religion', $request->religion);
        }
        if ($request->from_age && $request->to_age) {
            $query->whereBetween('reg.age', [$request->from_age, $request->to_age]);
        }
        if ($request->education) {
            $query->where('reg.education', $request->education);
        }
        if ($request->party_name) {
            $query->where('st4.party_name', $request->party_name);
        }
        if ($request->membership) {
            $query->where('reg.membership', $request->membership);
        }
        if ($request->interest_area) {
            $query->where('st3.intrest', $request->interest_area);
        }
        if ($request->family_member) {
            $range = explode(' AND ', $request->family_member);
            if (count($range) === 2) {
                $query->whereBetween('st3.total_member', $range);
            }
        }
        if ($request->vehicle) {
            $query->where('st3.' . $request->vehicle, '>', 0);
        }
        if (!is_null($request->whatsapp)) {
            $query->where('reg.mobile1_whatsapp', $request->whatsapp);
        }

        if ($request->filled('from_date')) {
            $query->whereDate('date_time', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('date_time', '<=', $request->to_date);
        }

        $totalFiltered = $query->count();

        $data = $query
            ->select('reg.registration_id', 'reg.member_id', 'reg.name', 'reg.mobile1', 'reg.gender', 'reg.date_time')
            ->orderBy('reg.registration_id', 'desc')
            ->offset($start)
            ->limit($length)
            ->get();

        $formatted = [];
        $index = $start;

        foreach ($data as $row) {
            $index++;
            $formatted[] = [
                'DT_RowIndex' => $index,
                'member_id' => $row->member_id,
                'name' => $row->name,
                'mobile' => $row->mobile1,
                'gender' => $row->gender,
                'entry_date' => date('d-m-Y', strtotime($row->date_time)),
                'action' => '
                    <div style="display: flex;">
                        <a href="' . route('register.show', $row->registration_id) . '" class="btn btn-sm btn-success mr-2">विवरण</a>
                        <a href="' . route('register.card', $row->registration_id) . '" class="btn btn-sm btn-primary mr-2">कार्ड</a>
                       <button type="button" data-id="' . $row->registration_id . '" class="btn btn-sm btn-danger deleteBtn">हटाएं</button>
                    </div>',
                'edit' => '
                    <div style="display: flex;">
                        <a href="' . route('membership.edit', $row->registration_id) . '" class="btn btn-sm btn-info mr-2">अपडेट</a>
                    </div>'
            ];
        }

        return response()->json([
            'draw' => intval($request->input('draw')),
            'recordsTotal' => $totalFiltered,
            'recordsFiltered' => $totalFiltered,
            'data' => $formatted
        ]);
    }



    // public function download(Request $request)
    // {
    //     $condition = $request->input('download_data_whr');

    //     $query = DB::table('registration_form as reg')
    //         ->leftJoin('step2 as st2', 'st2.registration_id', '=', 'reg.registration_id')
    //         ->leftJoin('step3 as st3', 'st3.registration_id', '=', 'reg.registration_id')
    //         ->leftJoin('step4 as st4', 'st4.registration_id', '=', 'reg.registration_id')
    //         ->select([
    //             'reg.reference_id',
    //             'reg.member_id',
    //             'reg.name',
    //             'reg.membership',
    //             'reg.gender',
    //             'reg.dob',
    //             'reg.age',
    //             'reg.mobile1',
    //             'reg.mobile2',
    //             'reg.mobile1_whatsapp',
    //             'reg.mobile2_whatsapp',
    //             'reg.religion',
    //             'reg.caste',
    //             'reg.jati',
    //             'reg.education',
    //             'reg.business',
    //             'reg.position',
    //             'reg.father_name',
    //             'reg.email',
    //             'st2.division_id',
    //             'st2.district',
    //             'st2.vidhansabha',
    //             'st2.mandal',
    //             'st2.nagar',
    //             'st2.matdan_kendra_name',
    //             'st2.loksabha',
    //             'st3.total_member',
    //             'st3.total_voter',
    //             'st3.member_job',
    //             'st3.member_name_1',
    //             'st3.member_mobile_1',
    //             'st3.member_name_2',
    //             'st3.member_mobile_2',
    //             'st3.friend_name_1',
    //             'st3.friend_mobile_1',
    //             'st3.friend_name_2',
    //             'st3.friend_mobile_2',
    //             'st3.intrest',
    //             'st3.vehicle1',
    //             'st3.vehicle2',
    //             'st3.vehicle3',
    //             'st3.permanent_address',
    //             'st3.temp_address',
    //             'st4.party_name',
    //             'st4.present_post',
    //             'st4.reason_join',
    //             'st4.post_date',
    //             'reg.photo',
    //         ]);


    //     if (!empty($condition)) {
    //         $query->whereRaw($condition);
    //     }

    //     $data = $query->get();


    //     $headers = [
    //         "Content-type" => "text/csv; charset=UTF-8",
    //         "Content-Disposition" => "attachment; filename=List_Export.csv",
    //         "Pragma" => "no-cache",
    //         "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
    //         "Expires" => "0"
    //     ];

    //     $columns = [
    //         "साथी का आई डी नंबर",
    //         "आपका सदस्यता आई डी",
    //         "आपका नाम",
    //         "लिंग",
    //         "जन्म दिनांक",
    //         "आयु",
    //         "मोबाइल 1",
    //         "मोबाइल 2",
    //         "मोबाइल 1 व्हाट्सएप",
    //         "मोबाइल 2 व्हाट्सएप",
    //         "धर्म",
    //         "श्रेणी",
    //         "जाति",
    //         "शैक्षणिक योग्यता",
    //         "व्यवसाय",
    //         "व्यवसायिक पद",
    //         "पिता का नाम",
    //         "ईमेल आईडी",
    //         "संभाग का नाम",
    //         "जिले का नाम",
    //         "लोकसभा",
    //         "विधानसभा का नाम",
    //         "मंडल का नाम",
    //         "नगर केंद्र/ग्राम केंद्र का नाम",
    //         "मतदान केंद्र का नाम/क्रमांक",
    //         "परिवार में कुल सदस्य",
    //         "परिवार में कुल मतदाता",
    //         "शासकीय/अशासकीय सेवा में सदस्य",
    //         "परिवार के सदस्य नाम 1",
    //         "परिवार के सदस्य मोबाइल 1",
    //         "परिवार के सदस्य नाम 2",
    //         "परिवार के सदस्य मोबाइल 2",
    //         "मित्र / पड़ोसी नाम1",
    //         "मित्र / पड़ोसी मोबाइल1",
    //         "मित्र / पड़ोसी नाम2",
    //         "मित्र / पड़ोसी मोबाइल2",
    //         "रुचि",
    //         "मोटरसाइकिल",
    //         "कार",
    //         "ट्रेक्टर",
    //         "स्थाई पता",
    //         "अस्थाई पता",
    //         "राजनीतिक/सामाजिक सक्रियता",
    //         "पद वर्तमान/भूतपूर्व",
    //         "आप बीजेएस के सदस्य क्यों बन रहे हैं",
    //         "दिनांक",
    //         "Photo"
    //     ];

    //     $callback = function () use ($data, $columns) {
    //         $file = fopen('php://output', 'w');

    //         fputs($file, $bom = (chr(0xEF) . chr(0xBB) . chr(0xBF)));

    //         fputcsv($file, $columns);

    //         foreach ($data as $row) {
    //             fputcsv($file, [
    //                 $row->reference_id,
    //                 $row->member_id,
    //                 $row->name,
    //                 $row->gender,
    //                 $row->dob,
    //                 $row->age,
    //                 $row->mobile1,
    //                 $row->mobile2,
    //                 $row->mobile1_whatsapp ? 'yes' : 'no',
    //                 $row->mobile2_whatsapp ? 'yes' : 'no',
    //                 $row->religion,
    //                 $row->caste,
    //                 $row->jati,
    //                 $row->education,
    //                 $row->business,
    //                 $row->position,
    //                 $row->father_name,
    //                 $row->email,
    //                 $row->division_id,
    //                 $row->district,
    //                 $row->loksabha,
    //                 $row->vidhansabha,
    //                 $row->mandal,
    //                 $row->nagar,
    //                 $row->matdan_kendra_name,
    //                 $row->total_member,
    //                 $row->total_voter,
    //                 $row->member_job,
    //                 $row->member_name_1,
    //                 $row->member_mobile_1,
    //                 $row->member_name_2,
    //                 $row->member_mobile_2,
    //                 $row->friend_name_1,
    //                 $row->friend_mobile_1,
    //                 $row->friend_name_2,
    //                 $row->friend_mobile_2,
    //                 $row->intrest,
    //                 $row->vehicle1,
    //                 $row->vehicle2,
    //                 $row->vehicle3,
    //                 preg_replace('/[ ,]+/', '-', trim($row->permanent_address)),
    //                 preg_replace('/[ ,]+/', '-', trim($row->temp_address)),
    //                 $row->party_name,
    //                 $row->present_post,
    //                 preg_replace('/[ ,]+/', '-', trim($row->reason_join)),
    //                 $row->post_date,
    //                 $row->photo
    //             ]);
    //         }

    //         fclose($file);
    //     };

    //     return new StreamedResponse($callback, 200, $headers);
    // }

    public function download(Request $request)
    {
        $query = DB::table('registration_form as reg')
            ->leftJoin('step2 as st2', 'st2.registration_id', '=', 'reg.registration_id')
            ->leftJoin('step3 as st3', 'st3.registration_id', '=', 'reg.registration_id')
            ->leftJoin('step4 as st4', 'st4.registration_id', '=', 'reg.registration_id')
            ->select([
                'reg.reference_id',
                'reg.member_id',
                'reg.name',
                'reg.membership',
                'reg.gender',
                'reg.dob',
                'reg.age',
                'reg.mobile1',
                'reg.mobile2',
                'reg.mobile1_whatsapp',
                'reg.mobile2_whatsapp',
                'reg.religion',
                'reg.caste',
                'reg.jati',
                'reg.education',
                'reg.business',
                'reg.position',
                'reg.father_name',
                'reg.email',
                'st2.division_id',
                'st2.district',
                'st2.vidhansabha',
                'st2.mandal',
                'st2.nagar',
                'st2.matdan_kendra_name',
                'st2.loksabha',
                'st3.total_member',
                'st3.total_voter',
                'st3.member_job',
                'st3.member_name_1',
                'st3.member_mobile_1',
                'st3.member_name_2',
                'st3.member_mobile_2',
                'st3.friend_name_1',
                'st3.friend_mobile_1',
                'st3.friend_name_2',
                'st3.friend_mobile_2',
                'st3.intrest',
                'st3.vehicle1',
                'st3.vehicle2',
                'st3.vehicle3',
                'st3.permanent_address',
                'st3.temp_address',
                'st4.party_name',
                'st4.present_post',
                'st4.reason_join',
                'st4.post_date',
                'reg.photo',
            ]);

        // Apply filters from the request
        if ($request->filled('main_mobile')) {
            $query->where('reg.mobile1', 'like', '%' . $request->main_mobile . '%');
        }

        if ($request->filled('name')) {
            $query->where('reg.name', 'like', '%' . $request->name . '%');
        }

        if ($request->filled('gender')) {
            $query->where('reg.gender', $request->gender);
        }

        if ($request->filled('category')) {
            $query->where('reg.caste', $request->category);
        }

        if ($request->filled('business')) {
            $query->where('reg.business', 'like', '%' . $request->business . '%');
        }

        if ($request->filled('district')) {
            $query->where('st2.district', $request->district);
        }

        if ($request->filled('txtvidhansabha')) {
            $query->where('st2.vidhansabha', $request->txtvidhansabha);
        }

        if ($request->filled('mandal')) {
            $query->where('st2.mandal', $request->mandal);
        }

        if ($request->filled('txtjati')) {
            $query->where('reg.jati', $request->txtjati);
        }

        if ($request->filled('religion')) {
            $query->where('reg.religion', $request->religion);
        }

        if ($request->filled('from_age')) {
            $query->where('reg.age', '>=', $request->from_age);
        }

        if ($request->filled('to_age')) {
            $query->where('reg.age', '<=', $request->to_age);
        }

        if ($request->filled('education')) {
            $query->where('reg.education', $request->education);
        }

        if ($request->filled('party_name')) {
            $query->where('st4.party_name', 'like', '%' . $request->party_name . '%');
        }

        if ($request->filled('membership')) {
            $query->where('reg.membership', $request->membership);
        }

        if ($request->filled('interest_area')) {
            $query->where('st3.intrest', 'like', '%' . $request->interest_area . '%');
        }

        if ($request->filled('family_member')) {
            $query->where('st3.total_member', '>=', $request->family_member);
        }

        if ($request->filled('vehicle')) {
            $query->where(function ($q) use ($request) {
                $q->where('st3.vehicle1', 'like', '%' . $request->vehicle . '%')
                    ->orWhere('st3.vehicle2', 'like', '%' . $request->vehicle . '%')
                    ->orWhere('st3.vehicle3', 'like', '%' . $request->vehicle . '%');
            });
        }

        if ($request->filled('whatsapp')) {
            $query->where(function ($q) use ($request) {
                $q->where('reg.mobile1_whatsapp', $request->whatsapp)
                    ->orWhere('reg.mobile2_whatsapp', $request->whatsapp);
            });
        }

        $data = $query->get();

        $headers = [
            "Content-type" => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename=List_Export.csv",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];

        $columns = [
            "साथी का आई डी नंबर",
            "आपका सदस्यता आई डी",
            "आपका नाम",
            "लिंग",
            "जन्म दिनांक",
            "आयु",
            "मोबाइल 1",
            "मोबाइल 2",
            "मोबाइल 1 व्हाट्सएप",
            "मोबाइल 2 व्हाट्सएप",
            "धर्म",
            "श्रेणी",
            "जाति",
            "शैक्षणिक योग्यता",
            "व्यवसाय",
            "व्यवसायिक पद",
            "पिता का नाम",
            "ईमेल आईडी",
            "संभाग का नाम",
            "जिले का नाम",
            "लोकसभा",
            "विधानसभा का नाम",
            "मंडल का नाम",
            "नगर केंद्र/ग्राम केंद्र का नाम",
            "मतदान केंद्र का नाम/क्रमांक",
            "परिवार में कुल सदस्य",
            "परिवार में कुल मतदाता",
            "शासकीय/अशासकीय सेवा में सदस्य",
            "परिवार के सदस्य नाम 1",
            "परिवार के सदस्य मोबाइल 1",
            "परिवार के सदस्य नाम 2",
            "परिवार के सदस्य मोबाइल 2",
            "मित्र / पड़ोसी नाम1",
            "मित्र / पड़ोसी मोबाइल1",
            "मित्र / पड़ोसी नाम2",
            "मित्र / पड़ोसी मोबाइल2",
            "रुचि",
            "मोटरसाइकिल",
            "कार",
            "ट्रेक्टर",
            "स्थाई पता",
            "अस्थाई पता",
            "राजनीतिक/सामाजिक सक्रियता",
            "पद वर्तमान/भूतपूर्व",
            "आप बीजेएस के सदस्य क्यों बन रहे हैं",
            "दिनांक",
            "Photo"
        ];

        $callback = function () use ($data, $columns) {
            $file = fopen('php://output', 'w');

            // UTF-8 BOM
            fputs($file, $bom = chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($file, $columns);

            foreach ($data as $row) {
                fputcsv($file, [
                    $row->reference_id,
                    $row->member_id,
                    $row->name,
                    $row->gender,
                    $row->dob,
                    $row->age,
                    $row->mobile1,
                    $row->mobile2,
                    $row->mobile1_whatsapp ? 'yes' : 'no',
                    $row->mobile2_whatsapp ? 'yes' : 'no',
                    $row->religion,
                    $row->caste,
                    $row->jati,
                    $row->education,
                    $row->business,
                    $row->position,
                    $row->father_name,
                    $row->email,
                    $row->division_id,
                    $row->district,
                    $row->loksabha,
                    $row->vidhansabha,
                    $row->mandal,
                    $row->nagar,
                    $row->matdan_kendra_name,
                    $row->total_member,
                    $row->total_voter,
                    $row->member_job,
                    $row->member_name_1,
                    $row->member_mobile_1,
                    $row->member_name_2,
                    $row->member_mobile_2,
                    $row->friend_name_1,
                    $row->friend_mobile_1,
                    $row->friend_name_2,
                    $row->friend_mobile_2,
                    $row->intrest,
                    $row->vehicle1,
                    $row->vehicle2,
                    $row->vehicle3,
                    preg_replace('/[ ,]+/', '-', trim($row->permanent_address)),
                    preg_replace('/[ ,]+/', '-', trim($row->temp_address)),
                    $row->party_name,
                    $row->present_post,
                    preg_replace('/[ ,]+/', '-', trim($row->reason_join)),
                    $row->post_date,
                    $row->photo
                ]);
            }

            fclose($file);
        };

        return new StreamedResponse($callback, 200, $headers);
    }


    public function show($id)
    {
        $registration = RegistrationForm::with('reference')->findOrFail($id);
        $step2 = Step2::where('registration_id', $id)->first();
        $step3 = Step3::where('registration_id', $id)->first();
        $step4 = Step4::where('registration_id', $id)->first();

        // $vidhansabhaModel = VidhansabhaLoksabha::find($step2->vidhansabha);
        // $vidhansabha = $vidhansabhaModel ? $vidhansabhaModel->vidhansabha : 'NA';

        // $mandalModel = Mandal::find($step2->mandal);
        // $mandal = $mandalModel ? $mandalModel->mandal_name : null;

        // $nagarModel = Nagar::find($step2->nagar);
        // $nagar = $nagarModel ? $nagarModel->nagar_name : null;

        // $pollingModel = Polling::find($step2->matdan_kendra_name);
        // $polling = $pollingModel ? $pollingModel->polling_name : null;

        // $areaModel = Area::find($step2->area_id);
        // $area = $areaModel ? $areaModel->area_name : null;


        $vidhansabha = optional(VidhansabhaLokSabha::find($step2->vidhansabha ?? null))->vidhansabha ?? 'N/A';
        $mandal = optional(Mandal::find($step2->mandal ?? null))->mandal_name ?? 'N/A';
        $nagar = optional(Nagar::find($step2->nagar ?? null))->nagar_name ?? 'N/A';
        $polling = optional(Polling::find($step2->matdan_kendra_name ?? null))->polling_name ?? 'N/A';
        $area = optional(Area::find($step2->area_id ?? null))->area_name ?? 'N/A';

        $divisions = DB::table('division_master')->get();

        $district_id = $division->district_id ?? null;

        $districts = DB::table('district_master')->get();

        // $interests = explode(' ', $step3->intrest);
        $interests = isset($step3->intrest) ? explode(' ', $step3->intrest) : [];
        // $interestOptions = [
        //     'कृषि',
        //     'समाजसेवा',
        //     'राजनीति',
        //     'पर्यावरण',
        //     'शिक्षा',
        //     'योग',
        //     'स्वास्थ्य',
        //     'स्वच्छता',
        //     'साधना'
        // ];


        $jatis       = Jati::all();
        $categories  = Category::all();
        $religions   = Religion::all();
        $educations  = Education::all();
        $businesses  = Business::all();
        $politics    = Politics::all();
        $interestsDB = Interest::all();

        return view('admin.details_register', compact(
            'registration',
            'step2',
            'step3',
            'step4',
            'vidhansabha',
            'mandal',
            'nagar',
            'polling',
            'area',
            'divisions',
            'districts',
            'district_id',
            'interests',
            // 'interestOptions',
            'jatis',
            'categories',
            'religions',
            'educations',
            'businesses',
            'politics',
            'interestsDB'
        ));
    }


    public function card($id)
    {
        $member = DB::table('registration_form')->where('registration_id', $id)->first();

        if (!$member) {
            abort(404, 'Member not found.');
        }

        $photo = $member->photo;

        $step2 = DB::table('step2')->where('registration_id', $id)->first();
        $district_id = $step2->district ?? null;

        $step3 = DB::table('step3')->where('registration_id', $id)->first();
        $address = $step3->permanent_address ?? '—';

        // Get district name
        $district = '—';
        if ($district_id) {
            $districtData = DB::table('district_master')->where('district_id', $district_id)->first();
            $district = $districtData->district_name ?? '—';
        }



        return view('admin/card_process', [
            'member' => $member,
            'filename' => $member->photo,
            'address' => $step3->permanent_address ?? '',
            'district' => $district,
        ]);
    }

    public function print($id)
    {
        $member = DB::table('registration_form')->where('registration_id', $id)->first();
        $step3 = DB::table('step3')->where('registration_id', $id)->first();

        if (!$member) {
            abort(404, 'Member not found');
        }

        $assignPosition = AssignPosition::with('position')
            ->where('member_id', $id)
            ->orderByDesc('assign_position_id')
            ->first();

        $positionName = $assignPosition->position->position_name ?? 'दायित्व नहीं दिया';
        $fromDate = $assignPosition ? Carbon::parse($assignPosition->from_date)->format('d-M-Y') : 'नहीं है';
        $toDate   = $assignPosition ? Carbon::parse($assignPosition->to_date)->format('d-M-Y') : '';

        $photoPath = public_path('assets/upload/' . $member->photo);
        $backgroundPath = public_path('assets/images/member_back.jpg');


        $html = view('admin/card_print', [
            'member' => $member,
            'step3' => $step3,
            'address' => $step3->permanent_address ?? '',
            'photoPath' => $photoPath,
            'backgroundPath' => $backgroundPath,
            'positionName' => $positionName,
            'fromDate' => $fromDate,
            'toDate' => $toDate,
        ])->render();

        $mpdf = new Mpdf([
            'mode' => 'utf-8',
            'default_font' => 'FreeSans',
            'format' => 'A4',
            'margin_left' => 70,
            'margin_right' => 10,
            'margin_top' => 20,
            'margin_bottom' => 0,
            'margin_header' => 0,
            'margin_footer' => 0,
        ]);

        $mpdf->showImageErrors = true;
        $mpdf->WriteHTML($html);
        $mpdf->SetDisplayMode('fullpage');

        return response($mpdf->Output('', 'S'))->header('Content-Type', 'application/pdf');
    }

    public function uploadPhoto(Request $request)
    {
        $request->validate([
            'id' => 'required|integer|exists:registration_form,registration_id',
            'photo' => 'required|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $registration = RegistrationForm::findOrFail($request->id);

        // Handle the file upload
        $file = $request->file('photo');
        $filename = 'edit_' . time() . '.' . $file->getClientOriginalExtension();
        $path = public_path('assets/upload/' . $filename);

        // Resize image if needed (like PHP GD code)
        $img = Image::make($file->getRealPath());
        $img->resize(800, null, function ($constraint) {
            $constraint->aspectRatio();
        })->save($path);

        // Update DB
        $registration->photo = $filename;
        $registration->save();

        return redirect()->back()->with('success', 'फोटो सफलतापूर्वक अपडेट किया गया!');
    }

    public function destroy($id)
    {
        $registration = RegistrationForm::findOrFail($id);

        $registration->step2()->delete();
        $registration->step3()->delete();
        $registration->step4()->delete();

        $registration->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'रिकॉर्ड और संबंधित डेटा हटाया गया!'
        ]);
    }

    // dashboard2 functions

    public function dashboard2_index()
    {
        $query = DB::table('registration_form as A')
            ->join('registration_form as B', 'A.registration_id', '=', 'B.reference_id')
            ->join('step2 as st', 'st.registration_id', '=', 'A.registration_id')
            ->join('step3 as st3', 'st3.registration_id', '=', 'A.registration_id')
            ->join('step4 as st4', 'st4.registration_id', '=', 'A.registration_id')
            ->select(
                'A.*',
                'B.name',
                'B.member_id',
                'B.mobile1 as mbl',
                'B.mobile2',
                'B.gender',
                'B.date_time as pdate',
                'A.registration_id as member',
                'B.registration_id as added_member_id'
            )
            ->whereNotNull('A.mobile1')
            ->where('A.mobile1', '!=', '')
            ->where('A.type', 2);

        $registrations = $query->get();
        $count = $registrations->count();

        return view('admin/dashboard2', compact('registrations', 'count'));
    }

    public function dashboard2_filter(Request $request)
    {
        $condition = $request->input('where');

        $query = DB::table('registration_form as A')
            ->join('registration_form as B', 'A.registration_id', '=', 'B.reference_id')
            ->join('step2 as st', 'st.registration_id', '=', 'A.registration_id')
            ->join('step3 as st3', 'st3.registration_id', '=', 'A.registration_id')
            ->join('step4 as st4', 'st4.registration_id', '=', 'A.registration_id')
            ->select(
                'A.*',
                'B.name',
                'B.member_id',
                'B.mobile1 as mbl',
                'B.mobile2',
                'B.gender',
                'B.date_time as pdate',
                'A.registration_id as member',
                'B.registration_id as added_member_id'
            );

        if (!empty($condition)) {
            $query->whereRaw($condition);
        }

        $query->whereNotNull('A.mobile1')->where('A.mobile1', '!=', '')->where('A.type', 2);

        $registrations = $query->get();
        $count = $registrations->count();

        $html = '';
        $x = 0;

        foreach ($registrations as $row) {
            $x++;
            $date = $row->pdate ? date('d-m-Y', strtotime($row->pdate)) : '';

            $html .= '<tr>';
            $html .= '<td>' . $x . '</td>';
            $html .= '<td>' . $row->member_id . '</td>';
            $html .= '<td>' . $row->name . '</td>';
            $html .= '<td>' . $row->mbl . '</td>';
            $html .= '<td>' . ($row->mobile2 ?? '-') . '</td>';
            $html .= '<td>' . ($row->gender ?? '-') . '</td>';
            $html .= '<td>' . $date . '</td>';
            $html .= '<td style="white-space: nowrap;">
                <a href="' . route('register.show', $row->added_member_id) . '" class="btn btn-sm btn-success">View</a>
                <a href="' . route('register.card', $row->added_member_id) . '" class="btn btn-sm btn-primary">Card</a>
                <a href="' . route('register.destroy', $row->added_member_id) . '" class="btn btn-sm btn-danger">Delete</a>
            </td>';
            $html .= '</tr>';
        }

        return response()->json([
            'html' => $html,
            'count' => $count,
        ]);
    }


    public function dashboard2_download(Request $request)
    {
        $condition = $request->input('download_data_whr');

        $query = DB::table('registration_form as A')
            ->join('registration_form as B', 'A.registration_id', '=', 'B.reference_id')
            ->leftJoin('step2 as st2', 'st2.registration_id', '=', 'A.registration_id')
            ->leftJoin('step3 as st3', 'st3.registration_id', '=', 'A.registration_id')
            ->leftJoin('step4 as st4', 'st4.registration_id', '=', 'A.registration_id')
            ->select([
                'A.reference_id',
                'A.member_id',
                'A.name',
                'A.membership',
                'A.gender',
                'A.dob',
                'A.age',
                'B.mobile1',
                'A.mobile2',
                'A.mobile1_whatsapp',
                'A.mobile2_whatsapp',
                'A.religion',
                'A.caste',
                'A.jati',
                'A.education',
                'A.business',
                'A.position',
                'A.father_name',
                'A.email',
                'st2.division_id',
                'st2.district',
                'st2.vidhansabha',
                'st2.mandal',
                'st2.nagar',
                'st2.matdan_kendra_name',
                'st2.loksabha',
                'st3.total_member',
                'st3.total_voter',
                'st3.member_job',
                'st3.member_name_1',
                'st3.member_mobile_1',
                'st3.member_name_2',
                'st3.member_mobile_2',
                'st3.friend_name_1',
                'st3.friend_mobile_1',
                'st3.friend_name_2',
                'st3.friend_mobile_2',
                'st3.intrest',
                'st3.vehicle1',
                'st3.vehicle2',
                'st3.vehicle3',
                'st3.permanent_address',
                'st3.temp_address',
                'st4.party_name',
                'st4.present_post',
                'st4.reason_join',
                'st4.post_date',
                'A.photo',
            ]);


        if (!empty($condition)) {
            $query->whereRaw($condition);
        }

        $data = $query->get();


        $headers = [
            "Content-type" => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename=List_Export.csv",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];

        $columns = [
            "साथी का आई डी नंबर",
            "आपका सदस्यता आई डी",
            "आपका नाम",
            "लिंग",
            "जन्म दिनांक",
            "आयु",
            "मोबाइल 1",
            "मोबाइल 2",
            "मोबाइल 1 व्हाट्सएप",
            "मोबाइल 2 व्हाट्सएप",
            "धर्म",
            "श्रेणी",
            "जाति",
            "शैक्षणिक योग्यता",
            "व्यवसाय",
            "व्यवसायिक पद",
            "पिता का नाम",
            "ईमेल आईडी",
            "संभाग का नाम",
            "जिले का नाम",
            "लोकसभा",
            "विधानसभा का नाम",
            "मंडल का नाम",
            "नगर केंद्र/ग्राम केंद्र का नाम",
            "मतदान केंद्र का नाम/क्रमांक",
            "परिवार में कुल सदस्य",
            "परिवार में कुल मतदाता",
            "शासकीय/अशासकीय सेवा में सदस्य",
            "परिवार के सदस्य नाम 1",
            "परिवार के सदस्य मोबाइल 1",
            "परिवार के सदस्य नाम 2",
            "परिवार के सदस्य मोबाइल 2",
            "मित्र / पड़ोसी नाम1",
            "मित्र / पड़ोसी मोबाइल1",
            "मित्र / पड़ोसी नाम2",
            "मित्र / पड़ोसी मोबाइल2",
            "रुचि",
            "मोटरसाइकिल",
            "कार",
            "ट्रेक्टर",
            "स्थाई पता",
            "अस्थाई पता",
            "राजनीतिक/सामाजिक सक्रियता",
            "पद वर्तमान/भूतपूर्व",
            "आप बीजेएस के सदस्य क्यों बन रहे हैं",
            "दिनांक",
            "Photo"
        ];

        $callback = function () use ($data, $columns) {
            $file = fopen('php://output', 'w');

            fputs($file, $bom = (chr(0xEF) . chr(0xBB) . chr(0xBF)));

            fputcsv($file, $columns);

            foreach ($data as $row) {
                fputcsv($file, [
                    $row->reference_id,
                    $row->member_id,
                    $row->name,
                    $row->gender,
                    $row->dob,
                    $row->age,
                    $row->mobile1,
                    $row->mobile2,
                    $row->mobile1_whatsapp ? 'yes' : 'no',
                    $row->mobile2_whatsapp ? 'yes' : 'no',
                    $row->religion,
                    $row->caste,
                    $row->jati,
                    $row->education,
                    $row->business,
                    $row->position,
                    $row->father_name,
                    $row->email,
                    $row->division_id,
                    $row->district,
                    $row->loksabha,
                    $row->vidhansabha,
                    $row->mandal,
                    $row->nagar,
                    $row->matdan_kendra_name,
                    $row->total_member,
                    $row->total_voter,
                    $row->member_job,
                    $row->member_name_1,
                    $row->member_mobile_1,
                    $row->member_name_2,
                    $row->member_mobile_2,
                    $row->friend_name_1,
                    $row->friend_mobile_1,
                    $row->friend_name_2,
                    $row->friend_mobile_2,
                    $row->intrest,
                    $row->vehicle1,
                    $row->vehicle2,
                    $row->vehicle3,
                    preg_replace('/[ ,]+/', '-', trim($row->permanent_address)),
                    preg_replace('/[ ,]+/', '-', trim($row->temp_address)),
                    $row->party_name,
                    $row->present_post,
                    preg_replace('/[ ,]+/', '-', trim($row->reason_join)),
                    $row->post_date,
                    $row->photo
                ]);
            }

            fclose($file);
        };

        return new StreamedResponse($callback, 200, $headers);
    }

    // birthday functions
    public function birthday_index()
    {
        $birthdays = DB::table('registration_form')
            ->select(
                'registration_form.*',
                DB::raw("DATE_FORMAT(dob, '%d-%m-%Y') as dob_formatted"),
                DB::raw("DATE_FORMAT(date_time, '%d-%m-%Y') as entry_date"),
                DB::raw("DATE_ADD(dob, INTERVAL (YEAR(CURDATE()) - YEAR(dob)) YEAR) AS currbirthday"),
                DB::raw("DATE_ADD(dob, INTERVAL (YEAR(CURDATE()) - YEAR(dob) + 1) YEAR) AS nextbirthday")
            )
            ->whereRaw("dob != '0000-00-00'")
            ->whereRaw("MONTH(dob) = MONTH(CURDATE())")
            ->orderBy('currbirthday', 'asc')
            ->get();

        return view('admin/birthday', compact('birthdays'));
    }

    // complaint functions
    // public function complaint_index(Request $request)
    // {
    //     $query = Complaint::all();



    //     if ($request->filled('complaint_status')) {
    //         $query->where('complaint_status', $request->complaint_status);
    //     }

    //     if ($request->filled('complaint_type')) {
    //         $query->where('complaint_type', $request->complaint_type);
    //     } else {
    //         // Apply default filter for initial load or sabhi
    //         $query->where('complaint_type', 'समस्या');
    //     }

    //     // if ($request->filled('department_id')) {
    //     //     $query->where('complaint_department', $request->department_id);
    //     // }

    //     // if ($request->filled('subject_id')) {
    //     //     $query->where('issue_title', $request->subject_id);
    //     // }

    //     if ($request->filled('department_id')) {
    //         $department = Department::find($request->department_id);
    //         if ($department) {
    //             $query->where('complaint_department', $department->department_name);
    //         }
    //     }

    //     if ($request->filled('reply_id')) {
    //         $query->whereHas('replies', function ($q) use ($request) {
    //             $q->where('selected_reply', $request->reply_id);
    //         });
    //     }

    //     if ($request->filled('subject_id')) {
    //         $subject = Subject::find($request->subject_id);
    //         if ($subject) {
    //             $query->where('issue_title', $subject->subject);
    //         }
    //     }

    //     if ($request->filled('mandal_id')) {
    //         $query->where('mandal_id', $request->mandal_id);
    //     }

    //     if ($request->filled('gram_id')) {
    //         $query->where('gram_id', $request->gram_id);
    //     }

    //     if ($request->filled('polling_id')) {
    //         $query->where('polling_id', $request->polling_id);
    //     }

    //     if ($request->filled('area_id')) {
    //         $query->where('area_id', $request->area_id);
    //     }

    //     if ($request->filled('from_date')) {
    //         $query->whereDate('posted_date', '>=', $request->from_date);
    //     }

    //     if ($request->filled('to_date')) {
    //         $query->whereDate('posted_date', '<=', $request->to_date);
    //     }

    //     $complaints = Complaint::orderBy('posted_date', 'desc')->get();

    //     foreach ($complaints as $complaint) {
    //         if (!in_array($complaint->complaint_status, [4, 5])) {
    //             $complaint->pending_days = Carbon::parse($complaint->posted_date)->diffInDays(now());
    //         } else {
    //             $complaint->pending_days = 0;
    //         }
    //     }


    //     if ($request->ajax()) {
    //         $html = '';

    //         foreach ($complaints as $index => $complaint) {
    //             $html .= '<tr>';
    //             $html .= '<td>' . ($index + 1) . '</td>';
    //             $html .= '<td>' . ($complaint->name ?? 'N/A') . '<br>' . ($complaint->mobile_number ?? '') . '</td>';

    //             $html .= '<td title="
    //         विभाग: ' . ($complaint->division->division_name ?? 'N/A') . '
    //         जिला: ' . ($complaint->district->district_name ?? 'N/A') . '
    //         विधानसभा: ' . ($complaint->vidhansabha->vidhansabha ?? 'N/A') . '
    //         मंडल: ' . ($complaint->mandal->mandal_name ?? 'N/A') . '
    //         नगर/ग्राम: ' . ($complaint->gram->nagar_name ?? 'N/A') . '
    //         मतदान केंद्र: ' . ($complaint->polling->polling_name ?? 'N/A') . ' (' . ($complaint->polling->polling_no ?? 'N/A') . ')
    //         क्षेत्र: ' . ($complaint->area->area_name ?? 'N/A') . '">
    //         ' . ($complaint->division->division_name ?? 'N/A') . '<br>' .
    //                 ($complaint->district->district_name ?? 'N/A') . '<br>' .
    //                 ($complaint->vidhansabha->vidhansabha ?? 'N/A') . '<br>' .
    //                 ($complaint->mandal->mandal_name ?? 'N/A') . '<br>' .
    //                 ($complaint->gram->nagar_name ?? 'N/A') . '<br>' .
    //                 ($complaint->polling->polling_name ?? 'N/A') . ' (' . ($complaint->polling->polling_no ?? 'N/A') . ')<br>' .
    //                 ($complaint->area->area_name ?? 'N/A') .
    //                 '</td>';

    //             $html .= '<td>' . ($complaint->complaint_department ?? 'N/A') . '</td>';
    //             $html .= '<td>' . \Carbon\Carbon::parse($complaint->posted_date)->format('d-m-Y') . '</td>';

    //             // Pending Days or Status
    //             if (in_array($complaint->complaint_status, [4, 5])) {
    //                 $html .= '<td>0 दिन</td>';
    //             } else {
    //                 $html .= '<td>' . $complaint->pending_days . ' दिन</td>';
    //             }

    //             // Status Text
    //             $html .= '<td>' . strip_tags($complaint->statusTextPlain()) . '</td>';
    //             $html .= '<td>' . ($complaint->admin_name ?? '') . '</td>';

    //             // Attachment
    //             if (!empty($complaint->issue_attachment)) {
    //                 $html .= '<td><a href="' . asset('assets/upload/complaints/' . $complaint->issue_attachment) . '" target="_blank" class="btn btn-sm btn-success">' . $complaint->issue_attachment . '</a></td>';
    //             } else {
    //                 $html .= '<td><button class="btn btn-sm btn-secondary" disabled>No Attachment</button></td>';
    //             }

    //             // Action Button
    //             $html .= '<td><a href="' . route('complaints_show.details', $complaint->complaint_id) . '" class="btn btn-sm btn-primary" style="white-space: nowrap;">क्लिक करें</a></td>';

    //             $html .= '</tr>';
    //         }

    //         return response()->json([
    //             'html' => $html,
    //             'count' => $complaints->count(),
    //         ]);
    //     }

    //     $mandals = Mandal::where('vidhansabha_id', 49)->get();
    //     $grams = $request->mandal_id ? Nagar::where('mandal_id', $request->mandal_id)->get() : collect();
    //     $pollings = $request->gram_id ? Polling::where('nagar_id', $request->gram_id)->get() : collect();
    //     $areas = $request->polling_id ? Area::where('polling_id', $request->polling_id)->get() : collect();
    //     $departments = Department::all();
    //     $replyOptions = ComplaintReply::all();
    //     $subjects = $request->department_id ? Subject::where('department_id', $request->department_id)->get() : collect();

    //     return view('admin/view_complaint', compact(
    //         'complaints',
    //         'mandals',
    //         'grams',
    //         'pollings',
    //         'areas',
    //         'departments',
    //         'subjects',
    //         'replyOptions'
    //     ));
    // }


    public function CommanderComplaints(Request $request)
    {
        $query = Complaint::with('registrationDetails', 'replies.forwardedToManager')->where('type', 1)->whereIn('complaint_type', ['समस्या', 'विकास']);

        if ($request->filled('complaintOtherFilter')) {
            switch ($request->complaintOtherFilter) {
                case 'forwarded_manager':
                    $query->whereHas('replies', function ($q2) use ($request) {
                        $q2->where('forwarded_to', $request->admin_id);
                    });
                    break;

                case 'not_opened':
                    $query->whereHas('latestReply', function ($q) use ($request) {
                        $q->where('forwarded_to', $request->admin_id);
                    });
                    break;

                case 'reviewed':
                    $query->whereHas('replies', function ($q) {
                        $q->whereNotNull('review_date')
                            ->whereRaw('reply_date = (
                              SELECT MAX(reply_date)
                              FROM complaint_reply
                              WHERE complaint_reply.complaint_id = complaint.complaint_id
                          )');
                    });
                    break;

                case 'important':
                    $query->whereHas('replies', function ($q) {
                        $q->whereNotNull('importance')
                            ->whereRaw('reply_date = (
                              SELECT MAX(reply_date)
                              FROM complaint_reply
                              WHERE complaint_reply.complaint_id = complaint.complaint_id
                          )');
                    })->orderByRaw("FIELD(
                        (SELECT importance 
                         FROM complaint_reply 
                         WHERE complaint_reply.complaint_id = complaint.complaint_id 
                         ORDER BY reply_date DESC 
                         LIMIT 1),
                        'उच्च', 'मध्यम', 'कम'
                    )");
                    break;



                case 'closed':
                    $query->where('complaint_status', 4);
                    break;

                case 'cancel':
                    $query->where('complaint_status', 5);
                    break;

                case 'reference_null':
                    $query->whereNull('reference_name');
                    break;

                case 'reference':
                    $query->whereNotNull('reference_name');
                    break;

                default:
                    break;
            }
        }

        if ($request->filled('complaint_status')) {
            $query->where('complaint_status', $request->complaint_status);
        }

        if ($request->filled('complaint_type')) {
            $query->where('complaint_type', $request->complaint_type);
        } else {
            $query->where('complaint_type', 'समस्या');
        }

        if ($request->filled('filter')) {
            switch ($request->filter) {
                case 'not_opened':
                    $query->whereHas('replies', function ($q) {
                        $q->where('complaint_status', 1)
                            ->where('complaint_reply', 'शिकायत दर्ज की गई है।')
                            ->where('forwarded_to', 6)
                            ->whereNull('selected_reply');
                    })->has('replies', '=', 1);
                    break;


                case 'reviewed':
                    $query->whereHas('replies', function ($q) {
                        $q->whereNotNull('review_date')
                            ->whereRaw('reply_date = (
                                SELECT MAX(reply_date)
                                FROM complaint_reply
                                WHERE complaint_reply.complaint_id = complaint.complaint_id
                            )');
                    })->orderByRaw("
                                (SELECT review_date 
                                FROM complaint_reply 
                                WHERE complaint_reply.complaint_id = complaint.complaint_id 
                                ORDER BY reply_date DESC 
                                LIMIT 1)
                            ");
                    break;

                case 'important':
                    $query->whereHas('replies', function ($q) {
                        $q->whereNotNull('importance')
                            ->whereRaw('reply_date = (
                            SELECT MAX(reply_date)
                            FROM complaint_reply
                            WHERE complaint_reply.complaint_id = complaint.complaint_id
                                )');
                    })->orderByRaw("FIELD(
                                (SELECT importance 
                                FROM complaint_reply 
                                WHERE complaint_reply.complaint_id = complaint.complaint_id 
                                ORDER BY reply_date DESC 
                                LIMIT 1),
                                'उच्च', 'मध्यम', 'कम'
                            )");
                    break;



                case 'closed':
                    $query->where('complaint_status', 4);
                    break;

                case 'cancel':
                    $query->where('complaint_status', 5);
                    break;

                case 'reference_null':
                    $query->whereNull('reference_name');
                    break;

                case 'reference':
                    $query->whereNotNull('reference_name');
                    break;
            }
        }

        // if ($request->filled('department_id')) {
        //     $query->where('complaint_department', $request->department_id);
        // }

        // if ($request->filled('subject_id')) {
        //     $query->where('issue_title', $request->subject_id);
        // }

        if ($request->get('department_null') == '1') {
            $query->where(function ($q) {
                $q->whereNull('complaint_department')
                    ->orWhere('complaint_department', '');
            });
        } elseif ($request->filled('department_id')) {
            $department = Department::find($request->department_id);
            if ($department) {
                $query->where('complaint_department', $department->department_name);
            }
        }

        if ($request->get('reference_null') == '1') {
            $query->whereNull('reference_name')
                ->orWhere('reference_name', '');
        } elseif ($request->filled('reference_name')) {
            $query->where('reference_name', $request->reference_name);
        }


        if ($request->get('jati_null') == '1') {
            $query->whereNull('jati_id');
        } elseif ($request->filled('jati_id')) {
            $query->where('jati_id', $request->jati_id);
        }

        if ($request->filled('admin_id')) {
            $query->whereHas('replies', function ($q) use ($request) {
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

        if ($request->filled('division_id')) {
            $query->where('division_id', $request->division_id);
        }

        if ($request->filled('district_id')) {
            $query->where('district_id', $request->district_id);
        }

        if ($request->filled('vidhansabha_id')) {
            $query->where('vidhansabha_id', $request->vidhansabha_id);
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
            $complaint->forwarded_reply_date = $lastReply?->reply_date?->format('d-m-Y h:i') ?? '-';
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
                    'applicant_name' => $complaint->registrationDetails->name ?? '',
                    'forwarded_to_name' => ($complaint->forwarded_to_name ?? '-') . '<br>' . ($complaint->forwarded_reply_date ?? '-'),
                    'action' => '<div style="display: inline-flex; gap: 5px; white-space: nowrap;">
                     <a href="' . route('admincomplaints.summary', $complaint->complaint_id) . '" class="btn btn-sm btn-warning" style="white-space: nowrap;">विवरण देखें</a>
                    <button class="btn btn-sm btn-danger delete-complaint" data-id="' . $complaint->complaint_id . '">हटाएं</button>
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
        $divisions = Division::all();
        $districts = $request->division_id ? District::where('division_id', $request->division_id)->get() : collect();
        $vidhansabhas = $request->district_id ? VidhansabhaLokSabha::where('district_id', $request->district_id)->get() : collect();
        $mandals = $request->vidhansabha_id ? Mandal::where('vidhansabha_id', $request->vidhansabha_id)->get() : collect();
        $grams = $request->mandal_id ? Nagar::where('mandal_id', $request->mandal_id)->get() : collect();
        $pollings = $request->gram_id ? Polling::where('nagar_id', $request->gram_id)->get() : collect();
        $areas = $request->polling_id ? Area::where('polling_id', $request->polling_id)->get() : collect();
        $departments = Department::all();
        $jatis = Jati::all();
        $replyOptions = ComplaintReply::all();
        $subjects = $request->department_id ? Subject::where('department_id', $request->department_id)->get() : collect();
        $managers = User::where('role', 2)->get();

        return view('admin/commander_complaints', compact(
            'divisions',
            'districts',
            'vidhansabhas',
            'complaints',
            'mandals',
            'grams',
            'pollings',
            'areas',
            'departments',
            'subjects',
            'replyOptions',
            'managers',
            'jatis'
        ));
    }

    public function complaintDestroy($id)
    {
        $complaint = Complaint::find($id);

        if (!$complaint) {
            return response()->json(['error' => 'रिकॉर्ड नहीं मिला।'], 404);
        }

        $type = $complaint->complaint_type;

        $replyIds = $complaint->replies()->pluck('complaint_reply_id')->toArray();

        if (!empty($replyIds)) {
            DB::table('incoming_calls')->whereIn('complaint_reply_id', $replyIds)->delete();
            DB::table('followup_status')->whereIn('complaint_reply_id', $replyIds)->delete();
        }

        $complaint->replies()->delete();
        DB::table('incoming_calls')->where('complaint_id', $complaint->complaint_id)->delete();

        DB::table('followup_status')->where('complaint_id', $complaint->complaint_id)->delete();

        DB::table('update_complaints')->where('complaint_id', $complaint->complaint_id)->delete();

        $complaint->delete();

        if ($type === 'शुभ सुचना') {
            return response()->json(['success' => 'शुभ सूचना सफलतापूर्वक हटा दी गई।']);
        } elseif ($type === 'अशुभ सुचना') {
            return response()->json(['success' => 'अशुभ सूचना सफलतापूर्वक हटा दी गई।']);
        }

        return response()->json(['success' => 'शिकायत सफलतापूर्वक हटा दी गई।']);
    }

    // public function OperatorComplaints(Request $request)
    // {
    //     // $vidhansabhaId = 49;
    //     // $districtId = 11;
    //     // $divisionId = 2;

    //     $query = Complaint::with('registrationDetails', 'replies.forwardedToManager')->where('type', 2)->whereIn('complaint_type', ['समस्या', 'विकास']);
    //     // ->where('district_id', $districtId)
    //     // ->where('vidhansabha_id', $vidhansabhaId);

    //     // Filters

    //     if ($request->filled('complaint_status')) {
    //         $query->where('complaint_status', $request->complaint_status);
    //     }

    //     if ($request->filled('complaint_type')) {
    //         $query->where('complaint_type', $request->complaint_type);
    //     } else {
    //         // Apply default filter for initial load or sabhi
    //         $query->where('complaint_type', 'समस्या');
    //     }

    //     // if ($request->filled('department_id')) {
    //     //     $query->where('complaint_department', $request->department_id);
    //     // }

    //     // if ($request->filled('subject_id')) {
    //     //     $query->where('issue_title', $request->subject_id);
    //     // }

    //     if ($request->filled('department_id')) {
    //         $department = Department::find($request->department_id);
    //         if ($department) {
    //             $query->where('complaint_department', $department->department_name);
    //         }
    //     }

    //     if ($request->filled('admin_id')) {
    //         $query->whereHas('latestReply', function ($q) use ($request) {
    //             $q->where('forwarded_to', $request->admin_id);
    //         });
    //     }

    //     if ($request->filled('reply_id')) {
    //         $query->whereHas('replies', function ($q) use ($request) {
    //             $q->where('selected_reply', $request->reply_id);
    //         });
    //     }

    //     if ($request->filled('jati_id')) {
    //         $query->where('jati_id', $request->jati_id);
    //     }

    //     if ($request->filled('subject_id')) {
    //         $subject = Subject::find($request->subject_id);
    //         if ($subject) {
    //             $query->where('issue_title', $subject->subject);
    //         }
    //     }

    //     if ($request->filled('mandal_id')) {
    //         $query->where('mandal_id', $request->mandal_id);
    //     }

    //     if ($request->filled('gram_id')) {
    //         $query->where('gram_id', $request->gram_id);
    //     }

    //     if ($request->filled('polling_id')) {
    //         $query->where('polling_id', $request->polling_id);
    //     }

    //     if ($request->filled('area_id')) {
    //         $query->where('area_id', $request->area_id);
    //     }

    //     if ($request->filled('from_date')) {
    //         $query->whereDate('posted_date', '>=', $request->from_date);
    //     }

    //     if ($request->filled('to_date')) {
    //         $query->whereDate('posted_date', '<=', $request->to_date);
    //     }

    //     $start = $request->input('start', 0);
    //     $length = $request->input('length', 10);

    //     $recordsFiltered = $query->count(); // total after filters
    //     $recordsTotal = $query->count();

    //     $complaints = $query->orderBy('posted_date', 'desc')
    //         ->offset($start)
    //         ->limit($length)
    //         ->get();


    //     // Add extra data to each complaint
    //     foreach ($complaints as $complaint) {
    //         $admin = DB::table('admin_master')->where('admin_id', $complaint->complaint_created_by)->first();
    //         $complaint->admin_name = $admin->admin_name ?? '';
    //         $complaint->pending_days = in_array($complaint->complaint_status, [4, 5])
    //             ? 0
    //             : \Carbon\Carbon::parse($complaint->posted_date)->diffInDays(now());

    //         $lastReply = $complaint->replies
    //             ->whereNotNull('forwarded_to')
    //             ->sortByDesc('reply_date')
    //             ->first();

    //         $complaint->forwarded_to_name = $lastReply?->forwardedToManager?->admin_name ?? '-';
    //         $complaint->forwarded_reply_date = $lastReply?->reply_date?->format('d-m-Y h:i') ?? '-';
    //     }

    //     if ($request->ajax()) {
    //         $data = [];
    //         foreach ($complaints as $index => $complaint) {

    //             $pendingText = $complaint->complaint_status == 4 ? 'पूर्ण' : ($complaint->complaint_status == 5 ? 'रद्द' : $complaint->pending_days . ' दिन');


    //             $data[] = [
    //                 'index' => $start + $index + 1,
    //                 'name' => "<strong>शिकायत क्र.: </strong>{$complaint->complaint_number}<br>" .
    //                     "<strong>नाम: </strong>{$complaint->name}<br>" .
    //                     "<strong>मोबाइल: </strong>{$complaint->mobile_number}<br>" .
    //                     "<strong>पुत्र श्री: </strong>{$complaint->father_name}<br>" .
    //                     "<strong>जाति: </strong>" . ($complaint->jati->jati_name ?? '-') . "<br>" .
    //                     "<strong>स्थिति: </strong>{$complaint->statusTextPlain()}",

    //                 'reference_name' => $complaint->reference_name ?? '',

    //                 'area_details' => "<strong>संभाग: </strong>" . ($complaint->division?->division_name ?? '') . ",<br>" .
    //                     "<strong>जिला: </strong>" . ($complaint->district?->district_name ?? '') . ",<br>" .
    //                     "<strong>विधानसभा: </strong>" . ($complaint->vidhansabha?->vidhansabha ?? '') . ",<br>" .
    //                     "<strong>मंडल: </strong>" . ($complaint->mandal?->mandal_name ?? '') . ",<br>" .
    //                     "<strong>नगर/ग्राम: </strong>" . ($complaint->gram?->nagar_name ?? '') . ",<br>" .
    //                     "<strong>मतदान केंद्र: </strong>" . ($complaint->polling?->polling_name ?? '') .
    //                     " (" . ($complaint->polling?->polling_no ?? '') . ") ,<br>" .
    //                     "<strong>ग्राम/वार्ड: </strong>" . ($complaint->area?->area_name ?? '') . ",<br>",

    //                 // 'area_details' => '<span title="
    //                 //         विभाग: ' . ($complaint->division?->division_name ?? 'N/A') . '
    //                 //         जिला: ' . ($complaint->district?->district_name ?? 'N/A') . '
    //                 //         विधानसभा: ' . ($complaint->vidhansabha?->vidhansabha ?? 'N/A') . '
    //                 //         मंडल: ' . ($complaint->mandal?->mandal_name ?? 'N/A') . '
    //                 //         नगर/ग्राम: ' . ($complaint->gram?->nagar_name ?? 'N/A') . '
    //                 //         मतदान केंद्र: ' . ($complaint->polling?->polling_name ?? 'N/A') . ' (' . ($complaint->polling?->polling_no ?? 'N/A') . ')
    //                 //         क्षेत्र: ' . ($complaint->area?->area_name ?? 'N/A') . '">
    //                 //     ' . ($complaint->division?->division_name ?? '') . '<br>' .
    //                 //     ($complaint->district?->district_name ?? '') . '<br>' .
    //                 //     ($complaint->vidhansabha?->vidhansabha ?? '') . '<br>' .
    //                 //     ($complaint->mandal?->mandal_name ?? '') . '<br>' .
    //                 //     ($complaint->gram?->nagar_name ?? '') . '<br>' .
    //                 //     ($complaint->polling?->polling_name ?? '') . ' (' . ($complaint->polling?->polling_no ?? '') . ')<br>' .
    //                 //     ($complaint->area?->area_name ?? '') .
    //                 //     '</span>',

    //                 'issue_description' => $complaint->issue_description,
    //                 'complaint_department' => $complaint->complaint_department,
    //                 'posted_date' => "<strong>तिथि: " . \Carbon\Carbon::parse($complaint->posted_date)->format('d-m-Y h:i A') . "</strong><br>" . $pendingText,

    //                 'review_date' => optional($complaint->replies->sortByDesc('reply_date')->first())->review_date ?? 'N/A',
    //                 'importance' => $complaint->latestReply?->importance ?? 'N/A',
    //                 'applicant_name' => $complaint->admin->admin_name ?? '',
    //                 'forwarded_to_name' => ($complaint->forwarded_to_name ?? '-') . '<br>' . ($complaint->forwarded_reply_date ?? '-'),
    //                 'action' => '<div style="display: inline-flex; gap: 5px; white-space: nowrap;">
    //                 <a href="' . route('admincomplaints.summary', $complaint->complaint_id) . '" class="btn btn-sm btn-warning" style="white-space: nowrap;">विवरण देखें</a>
    //                 <button class="btn btn-sm btn-danger delete-complaint" data-id="' . $complaint->complaint_id . '">हटाएं</button>
    //                 </div>',

    //                 'voter_id' => $complaint->voter_id ?? ''
    //             ];
    //         }

    //         return response()->json([
    //             'draw' => intval($request->input('draw')),
    //             'recordsTotal' => $recordsTotal,
    //             'recordsFiltered' => $recordsFiltered,
    //             'data' => $data
    //         ]);
    //     }

    //     $mandals = Mandal::where('vidhansabha_id', 49)->get();
    //     $grams = $request->mandal_id ? Nagar::where('mandal_id', $request->mandal_id)->get() : collect();
    //     $pollings = $request->gram_id ? Polling::where('nagar_id', $request->gram_id)->get() : collect();
    //     $areas = $request->polling_id ? Area::where('polling_id', $request->polling_id)->get() : collect();
    //     $departments = Department::all();
    //     $jatis = Jati::all();
    //     $replyOptions = ComplaintReply::all();
    //     $subjects = $request->department_id ? Subject::where('department_id', $request->department_id)->get() : collect();
    //     $managers = User::where('role', 2)->get();

    //     return view('admin.operator_complaints', compact(
    //         'complaints',
    //         'mandals',
    //         'grams',
    //         'pollings',
    //         'areas',
    //         'departments',
    //         'subjects',
    //         'replyOptions',
    //         'managers',
    //         'jatis'
    //     ));
    // }

    public function OperatorComplaints(Request $request)
    {
        // $vidhansabhaId = 49;
        // $districtId = 11;
        // $divisionId = 2;

        $query = Complaint::with(
            'registrationDetails',
            'replies.forwardedToManager',
            'latestReply.forwardedToManager'
        )->where('type', 2)->whereIn('complaint_type', ['समस्या', 'विकास']);
        // ->where('district_id', $districtId)
        // ->where('vidhansabha_id', $vidhansabhaId);

        if ($request->filled('complaintOtherFilter')) {
            switch ($request->complaintOtherFilter) {
                case 'forwarded_manager':
                    $query->whereHas('replies', function ($q2) use ($request) {
                        $q2->where('forwarded_to', $request->admin_id);
                    });
                    break;

                case 'not_opened':
                    $query->whereHas('latestReply', function ($q) use ($request) {
                        $q->where('forwarded_to', $request->admin_id);
                    });
                    break;

                case 'reviewed':
                    $query->whereHas('replies', function ($q) {
                        $q->whereNotNull('review_date')
                            ->whereRaw('reply_date = (
                              SELECT MAX(reply_date)
                              FROM complaint_reply
                              WHERE complaint_reply.complaint_id = complaint.complaint_id
                          )');
                    });
                    break;

                case 'important':
                    $query->whereHas('replies', function ($q) {
                        $q->whereNotNull('importance')
                            ->whereRaw('reply_date = (
                              SELECT MAX(reply_date)
                              FROM complaint_reply
                              WHERE complaint_reply.complaint_id = complaint.complaint_id
                          )');
                    })->orderByRaw("FIELD(
                        (SELECT importance 
                         FROM complaint_reply 
                         WHERE complaint_reply.complaint_id = complaint.complaint_id 
                         ORDER BY reply_date DESC 
                         LIMIT 1),
                        'उच्च', 'मध्यम', 'कम'
                    )");
                    break;



                case 'closed':
                    $query->where('complaint_status', 4);
                    break;

                case 'cancel':
                    $query->where('complaint_status', 5);
                    break;

                case 'reference_null':
                    $query->whereNull('reference_name');
                    break;

                case 'reference':
                    $query->whereNotNull('reference_name');
                    break;

                default:
                    // यदि कोई filter नहीं है या 'all', तो कोई extra condition नहीं लगाई जाएगी
                    break;
            }
        }

        if ($request->get('reference_null') == '1') {
            $query->where(function ($q) {
                $q->whereNull('reference_name')
                    ->orWhere('reference_name', '');
            });
        } elseif ($request->filled('reference_name')) {
            $query->where('reference_name', $request->reference_name);
        }

        if ($request->filled('complaint_status')) {
            $query->where('complaint_status', $request->complaint_status);
        }

        if ($request->filled('complaint_type')) {
            $query->where('complaint_type', $request->complaint_type);
        } else {
            $query->where('complaint_type', 'समस्या');
        }

        if ($request->filled('filter')) {
            switch ($request->filter) {
                case 'not_opened':
                    $query->whereHas('latestReply', function ($q) use ($request) {
                        $q->where('forwarded_to',  $request->admin_id);
                    });
                    break;



                case 'reviewed':
                    $query->whereHas('replies', function ($q) {
                        $q->whereNotNull('review_date')
                            ->whereRaw('reply_date = (
                                SELECT MAX(reply_date)
                                FROM complaint_reply
                                WHERE complaint_reply.complaint_id = complaint.complaint_id
                            )');
                    })->orderByRaw("
                                (SELECT review_date 
                                FROM complaint_reply 
                                WHERE complaint_reply.complaint_id = complaint.complaint_id 
                                ORDER BY reply_date DESC 
                                LIMIT 1)
                            ");
                    break;

                case 'important':
                    $query->whereHas('replies', function ($q) {
                        $q->whereNotNull('importance')
                            ->whereRaw('reply_date = (
                            SELECT MAX(reply_date)
                            FROM complaint_reply
                            WHERE complaint_reply.complaint_id = complaint.complaint_id
                                )');
                    })->orderByRaw("FIELD(
                                (SELECT importance 
                                FROM complaint_reply 
                                WHERE complaint_reply.complaint_id = complaint.complaint_id 
                                ORDER BY reply_date DESC 
                                LIMIT 1),
                                'उच्च', 'मध्यम', 'कम'
                            )");
                    break;



                case 'closed':
                    $query->where('complaint_status', 4);
                    break;

                case 'cancel':
                    $query->where('complaint_status', 5);
                    break;

                case 'reference_null':
                    $query->whereNull('reference_name');
                    break;

                case 'reference':
                    $query->whereNotNull('reference_name');
                    break;
            }
        }

        if ($request->get('department_null') == '1') {
            $query->where(function ($q) {
                $q->whereNull('complaint_department')
                    ->orWhere('complaint_department', '');
            });
        } elseif ($request->filled('department_id')) {
            $department = Department::find($request->department_id);
            if ($department) {
                $query->where('complaint_department', $department->department_name);
            }
        }

        if ($request->filled('admin_id')) {
            $query->whereHas('replies', function ($q) use ($request) {
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
        // if ($request->filled('jati_id')) {
        //     $query->where('jati_id', $request->jati_id);
        // }

        // if ($request->filled('jati_null') && $request->jati_null == '1') {
        //     $query->whereNull('jati_id');
        // }

        if ($request->get('jati_null') == '1') {
            $query->whereNull('jati_id');
        } elseif ($request->filled('jati_id')) {
            $query->where('jati_id', $request->jati_id);
        }

        if ($request->filled('division_id')) {
            $query->where('division_id', $request->division_id);
        }

        if ($request->filled('district_id')) {
            $query->where('district_id', $request->district_id);
        }

        if ($request->filled('vidhansabha_id')) {
            $query->where('vidhansabha_id', $request->vidhansabha_id);
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

        $recordsFiltered = $query->count(); // total after filters
        $recordsTotal = $query->count();

        $complaints = $query->orderBy('posted_date', 'desc')
            ->offset($start)
            ->limit($length)
            ->get();


        // Add extra data to each complaint
        foreach ($complaints as $complaint) {
            $admin = DB::table('admin_master')->where('admin_id', $complaint->complaint_created_by)->first();
            $complaint->admin_name = $admin->admin_name ?? '';
            $complaint->pending_days = in_array($complaint->complaint_status, [4, 5])
                ? 0
                : \Carbon\Carbon::parse($complaint->posted_date)->diffInDays(now());

            $lastReply = $complaint->replies
                ->whereNotNull('forwarded_to')
                ->sortByDesc('reply_date')
                ->first();

            $complaint->forwarded_to_name = $lastReply?->forwardedToManager?->admin_name ?? '-';
            $complaint->forwarded_reply_date = $lastReply?->reply_date?->format('d-m-Y h:i') ?? '-';
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
                    'action' => '<div style="display: inline-flex; gap: 5px; white-space: nowrap;">
                   <a href="' . route('admincomplaints.summary', $complaint->complaint_id) . '" class="btn btn-sm btn-warning" style="white-space: nowrap;">विवरण देखें</a>
                    <button class="btn btn-sm btn-danger delete-complaint" data-id="' . $complaint->complaint_id . '">हटाएं</button>
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

        $divisions = Division::all();
        $districts = $request->division_id ? District::where('division_id', $request->division_id)->get() : collect();
        $vidhansabhas = $request->district_id ? VidhansabhaLokSabha::where('district_id', $request->district_id)->get() : collect();
        $mandals = $request->vidhansabha_id ? Mandal::where('vidhansabha_id', $request->vidhansabha_id)->get() : collect();
        $grams = $request->mandal_id ? Nagar::where('mandal_id', $request->mandal_id)->get() : collect();
        $pollings = $request->gram_id ? Polling::where('nagar_id', $request->gram_id)->get() : collect();
        $areas = $request->polling_id ? Area::where('polling_id', $request->polling_id)->get() : collect();
        $departments = Department::all();
        $jatis = Jati::all();
        $replyOptions = ComplaintReply::all();
        $subjects = $request->department_id ? Subject::where('department_id', $request->department_id)->get() : collect();
        $managers = User::where('role', 2)->get();

        return view('admin.operator_complaints', compact(
            'complaints',
            'divisions',
            'districts',
            'vidhansabhas',
            'mandals',
            'grams',
            'pollings',
            'areas',
            'departments',
            'subjects',
            'replyOptions',
            'managers',
            'jatis'
        ));
    }


    // public function CommanderSuchnas(Request $request)
    // {
    //     $query = Complaint::with('registrationDetails', 'replies.forwardedToManager')->where('type', 1)->whereIn('complaint_type', ['अशुभ सुचना', 'शुभ सुचना']);

    //     if ($request->filled('complaint_status')) {
    //         $query->where('complaint_status', $request->complaint_status);
    //     }

    //     if ($request->filled('complaint_type')) {
    //         $query->where('complaint_type', $request->complaint_type);
    //     } else if (!$request->has('filter')) {
    //         $query->where('complaint_type', 'शुभ सुचना');
    //     }


    //     if ($request->filled('admin_id')) {
    //         $query->whereHas('latestReply', function ($q) use ($request) {
    //             $q->where('forwarded_to', $request->admin_id);
    //         });
    //     }

    //     if ($request->filled('jati_id')) {
    //         $query->where('jati_id', $request->jati_id);
    //     }

    //     if ($request->filled('mandal_id')) {
    //         $query->where('mandal_id', $request->mandal_id);
    //     }

    //     if ($request->filled('issue_title')) {
    //         $query->where('issue_title', $request->issue_title);
    //     }

    //     if ($request->filled('gram_id')) {
    //         $query->where('gram_id', $request->gram_id);
    //     }

    //     if ($request->filled('polling_id')) {
    //         $query->where('polling_id', $request->polling_id);
    //     }

    //     if ($request->filled('area_id')) {
    //         $query->where('area_id', $request->area_id);
    //     }

    //     if ($request->filled('from_date')) {
    //         $query->whereDate('posted_date', '>=', $request->from_date);
    //     }

    //     if ($request->filled('to_date')) {
    //         $query->whereDate('posted_date', '<=', $request->to_date);
    //     }

    //     if ($request->filled('programfrom_date')) {
    //         $query->whereDate('program_date', '>=', $request->programfrom_date);
    //     }

    //     if ($request->filled('programto_date')) {
    //         $query->whereDate('program_date', '<=', $request->programto_date);
    //     }


    //     $start = $request->input('start', 0);
    //     $length = $request->input('length', 10);

    //     $recordsFiltered = $query->count();
    //     $recordsTotal = $query->count();

    //     $complaints = $query->orderBy('posted_date', 'desc')
    //         ->offset($start)
    //         ->limit($length)
    //         ->get();

    //     foreach ($complaints as $complaint) {
    //         if (!in_array($complaint->complaint_status, [13, 14, 15, 16, 17, 18])) {
    //             $complaint->pending_days = Carbon::parse($complaint->posted_date)->diffInDays(now());
    //         } else {
    //             $complaint->pending_days = 0;
    //         }

    //         $lastReply = $complaint->replies
    //             ->whereNotNull('forwarded_to')
    //             ->sortByDesc('reply_date')
    //             ->first();

    //         $complaint->forwarded_to_name = $lastReply?->forwardedToManager?->admin_name ?? '-';
    //         $complaint->forwarded_reply_date = $lastReply?->reply_date?->format('d-m-Y h:i') ?? '-';
    //     }

    //     if ($request->ajax()) {
    //         $data = [];

    //         foreach ($complaints as $index => $complaint) {
    //             if ($complaint->complaint_status == 13) {
    //                 $pendingText = 'सम्मिलित हुए';
    //             } elseif ($complaint->complaint_status == 14) {
    //                 $pendingText = 'सम्मिलित नहीं हुए';
    //             } elseif ($complaint->complaint_status == 15) {
    //                 $pendingText = 'फोन पर संपर्क किया';
    //             } elseif ($complaint->complaint_status == 16) {
    //                 $pendingText = 'ईमेल पर संपर्क किया';
    //             } elseif ($complaint->complaint_status == 17) {
    //                 $pendingText = 'व्हाट्सएप पर संपर्क किया';
    //             } elseif ($complaint->complaint_status == 18) {
    //                 $pendingText = 'रद्द';
    //             } else {
    //                 $pendingText = $complaint->pending_days . ' दिन';
    //             }

    //             $data[] = [
    //                 'index' => $start + $index + 1,
    //                 'name' => "<strong>शिकायत क्र.: </strong>{$complaint->complaint_number}<br>" .
    //                     "<strong>नाम: </strong>{$complaint->name}<br>" .
    //                     "<strong>मोबाइल: </strong>{$complaint->mobile_number}<br>" .
    //                     "<strong>पुत्र श्री: </strong>{$complaint->father_name}<br>" .
    //                     "<strong>जाति: </strong>" . ($complaint->jati->jati_name ?? '-') . "<br>" .
    //                     "<strong>स्थिति: </strong>{$complaint->statusTextPlain()}",

    //                 'reference_name' => $complaint->reference_name ?? '',

    //                 'area_details' => "<strong>संभाग: </strong>" . ($complaint->division?->division_name ?? '') . ",<br>" .
    //                     "<strong>जिला: </strong>" . ($complaint->district?->district_name ?? '') . ",<br>" .
    //                     "<strong>विधानसभा: </strong>" . ($complaint->vidhansabha?->vidhansabha ?? '') . ",<br>" .
    //                     "<strong>मंडल: </strong>" . ($complaint->mandal?->mandal_name ?? '') . ",<br>" .
    //                     "<strong>नगर/ग्राम: </strong>" . ($complaint->gram?->nagar_name ?? '') . ",<br>" .
    //                     "<strong>मतदान केंद्र: </strong>" . ($complaint->polling?->polling_name ?? '') .
    //                     " (" . ($complaint->polling?->polling_no ?? '') . ") ,<br>" .
    //                     "<strong>ग्राम/वार्ड: </strong>" . ($complaint->area?->area_name ?? '') . ",<br>",

    //                 'posted_date' => "<strong>तिथि: " . \Carbon\Carbon::parse($complaint->posted_date)->format('d-m-Y h:i A') . "</strong><br>" . $pendingText,


    //                 'applicant_name' => $complaint->registrationDetails->name ?? '',

    //                 'forwarded_to_name' => ($complaint->forwarded_to_name ?? '-') . '<br>' . ($complaint->forwarded_reply_date ?? '-'),
    //                 'issue_description' => $complaint->issue_description,
    //                 'issue_title' => $complaint->issue_title,
    //                 'program_date' => $complaint->program_date,
    //                 'action' => '<div style="display: inline-flex; gap: 5px; white-space: nowrap;">
    //                 <a href="' . route('admincomplaints.summary', $complaint->complaint_id) . '" class="btn btn-sm btn-warning" style="white-space: nowrap;">विवरण देखें</a>
    //                 <button class="btn btn-sm btn-danger delete-complaint" data-id="' . $complaint->complaint_id . '">हटाएं</button>
    //                 </div>',

    //                 'voter_id' => $complaint->voter_id ?? ''
    //             ];
    //         }

    //         return response()->json([
    //             'draw' => intval($request->input('draw')),
    //             'recordsTotal' => $recordsTotal,
    //             'recordsFiltered' => $recordsFiltered,
    //             'data' => $data
    //         ]);
    //     }


    //     $mandals = Mandal::where('vidhansabha_id', 49)->get();
    //     $grams = $request->mandal_id ? Nagar::where('mandal_id', $request->mandal_id)->get() : collect();
    //     $pollings = $request->gram_id ? Polling::where('nagar_id', $request->gram_id)->get() : collect();
    //     $areas = $request->polling_id ? Area::where('polling_id', $request->polling_id)->get() : collect();
    //     $departments = Department::all();
    //     $jatis = Jati::all();
    //     $replyOptions = ComplaintReply::all();
    //     $managers = User::where('role', 2)->get();

    //     return view('admin/commander_suchna', compact(
    //         'complaints',
    //         'mandals',
    //         'grams',
    //         'pollings',
    //         'areas',
    //         'departments',
    //         'replyOptions',
    //         'managers',
    //         'jatis'
    //     ));
    // }


    public function CommanderSuchnas(Request $request)
    {
        $query = Complaint::with('registrationDetails', 'replies.forwardedToManager')->where('type', 1)->whereIn('complaint_type', ['अशुभ सुचना', 'शुभ सुचना']);

        if ($request->filled('complaintOtherFilter')) {
            switch ($request->complaintOtherFilter) {
                case 'forwarded_manager':
                    $query->whereHas('replies', function ($q) use ($request) {
                        $q->where('forwarded_to', $request->admin_id);
                    });
                    break;

                case 'not_opened':
                    $query->whereHas('latestReply', function ($q) use ($request) {
                        $q->where('forwarded_to', $request->admin_id);
                    });
                    break;

                case 'cancel':
                    $query->where('complaint_status', 18);
                    break;

                case 'sammilit_done':
                    $query->where('complaint_status', 13);
                    break;

                case 'sammilit_notdone':
                    $query->where('complaint_status', 14);
                    break;

                case 'reference_null':
                    $query->whereNull('reference_name');
                    break;

                case 'reference':
                    $query->whereNotNull('reference_name');
                    break;
            }
        }


        if ($request->filled('complaint_status')) {
            $query->where('complaint_status', $request->complaint_status);
        }

        if ($request->filled('filter')) {
            switch ($request->filter) {
                case 'not_opened':
                    $query->whereHas('replies', function ($q) {
                        $q->where('complaint_status', 11)
                            ->where('complaint_reply', 'सूचना दर्ज की गई है।')
                            ->where('forwarded_to', 6)
                            ->whereNull('selected_reply');
                    });
                    break;

                case 'cancel':
                    $query->where('complaint_status', 18);
                    break;

                case 'sammilit_done':
                    $query->where('complaint_status', 13);
                    break;

                case 'sammilit_notdone':
                    $query->where('complaint_status', 14);
                    break;

                case 'reference_null':
                    $query->whereNull('reference_name');
                    break;

                case 'reference':
                    $query->whereNotNull('reference_name');
                    break;
            }
        }


        if ($request->filled('complaint_type')) {
            $query->where('complaint_type', $request->complaint_type);
        } else if (!$request->has('filter')) {
            $query->where('complaint_type', 'शुभ सुचना');
        }


        if ($request->filled('admin_id')) {
            $query->whereHas('replies', function ($q) use ($request) {
                $q->where('forwarded_to', $request->admin_id);
            });
        }

        if ($request->filled('division_id')) {
            $query->where('division_id', $request->division_id);
        }

        if ($request->filled('district_id')) {
            $query->where('district_id', $request->district_id);
        }

        if ($request->filled('vidhansabha_id')) {
            $query->where('vidhansabha_id', $request->vidhansabha_id);
        }

        if ($request->filled('mandal_id')) {
            $query->where('mandal_id', $request->mandal_id);
        }

        if ($request->filled('issue_title')) {
            $query->where('issue_title', $request->issue_title);
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

        if ($request->filled('jati_id')) {
            $query->where('jati_id', $request->jati_id);
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
            $complaint->forwarded_reply_date = $lastReply?->reply_date?->format('d-m-Y h:i') ?? '-';
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

                    //                     'area_details' => '<span title="
                    // विभाग: ' . ($complaint->division?->division_name ?? 'N/A') . '
                    // जिला: ' . ($complaint->district?->district_name ?? 'N/A') . '
                    // विधानसभा: ' . ($complaint->vidhansabha?->vidhansabha ?? 'N/A') . '
                    // मंडल: ' . ($complaint->mandal?->mandal_name ?? 'N/A') . '
                    // नगर/ग्राम: ' . ($complaint->gram?->nagar_name ?? 'N/A') . '
                    // मतदान केंद्र: ' . ($complaint->polling?->polling_name ?? 'N/A') . ' (' . ($complaint->polling?->polling_no ?? 'N/A') . ')
                    // क्षेत्र: ' . ($complaint->area?->area_name ?? 'N/A') . '">
                    //             ' . ($complaint->division?->division_name ?? '') . '<br>' .
                    //                         ($complaint->district?->district_name ?? '') . '<br>' .
                    //                         ($complaint->vidhansabha?->vidhansabha ?? '') . '<br>' .
                    //                         ($complaint->mandal?->mandal_name ?? '') . '<br>' .
                    //                         ($complaint->gram?->nagar_name ?? '') . '<br>' .
                    //                         ($complaint->polling?->polling_name ?? '') . ' (' . ($complaint->polling?->polling_no ?? '') . ')<br>' .
                    //                         ($complaint->area?->area_name ?? '') .
                    //                         '</span>',

                    'posted_date' => "<strong>तिथि: " . \Carbon\Carbon::parse($complaint->posted_date)->format('d-m-Y h:i A') . "</strong><br>" . $pendingText,


                    'applicant_name' => $complaint->registrationDetails->name ?? '',

                    'forwarded_to_name' => ($complaint->forwarded_to_name ?? '-') . '<br>' . ($complaint->forwarded_reply_date ?? '-'),

                    'issue_description' => $complaint->issue_description,
                    'issue_title' => $complaint->issue_title,
                    'program_date' => $complaint->program_date,
                    'action' => '<div style="display: inline-flex; gap: 5px; white-space: nowrap;">
                    <a href="' . route('admincomplaints.summary', $complaint->complaint_id) . '" class="btn btn-sm btn-warning" style="white-space: nowrap;">विवरण देखें</a>
                    <button class="btn btn-sm btn-danger delete-complaint" data-id="' . $complaint->complaint_id . '">हटाएं</button>
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


        $divisions = Division::all();
        $districts = $request->division_id ? District::where('division_id', $request->division_id)->get() : collect();
        $vidhansabhas = $request->district_id ? VidhansabhaLokSabha::where('district_id', $request->district_id)->get() : collect();
        $mandals = $request->vidhansabha_id ? Mandal::where('vidhansabha_id', $request->vidhansabha_id)->get() : collect();
        $grams = $request->mandal_id ? Nagar::where('mandal_id', $request->mandal_id)->get() : collect();
        $pollings = $request->gram_id ? Polling::where('nagar_id', $request->gram_id)->get() : collect();
        $areas = $request->polling_id ? Area::where('polling_id', $request->polling_id)->get() : collect();
        $departments = Department::all();
        $jatis = Jati::all();
        $replyOptions = ComplaintReply::all();
        $managers = User::where('role', 2)->get();

        return view('admin/commander_suchna', compact(
            'divisions',
            'districts',
            'vidhansabhas',
            'complaints',
            'mandals',
            'grams',
            'pollings',
            'areas',
            'departments',
            'replyOptions',
            'managers',
            'jatis'
        ));
    }

    // public function OperatorSuchnas(Request $request)
    // {
    //     $query = Complaint::with('registrationDetails', 'replies.forwardedToManager')->where('type', 2)->whereIn('complaint_type', ['अशुभ सुचना', 'शुभ सुचना']);

    //     if ($request->filled('complaint_status')) {
    //         $query->where('complaint_status', $request->complaint_status);
    //     }

    //     if ($request->filled('complaint_type')) {
    //         $query->where('complaint_type', $request->complaint_type);
    //     } else if (!$request->has('filter')) {
    //         $query->where('complaint_type', 'शुभ सुचना');
    //     }

    //     if ($request->filled('admin_id')) {
    //         $query->whereHas('latestReply', function ($q) use ($request) {
    //             $q->where('forwarded_to', $request->admin_id);
    //         });
    //     }

    //     if ($request->filled('issue_title')) {
    //         $query->where('issue_title', $request->issue_title);
    //     }


    //     if ($request->filled('mandal_id')) {
    //         $query->where('mandal_id', $request->mandal_id);
    //     }

    //     if ($request->filled('gram_id')) {
    //         $query->where('gram_id', $request->gram_id);
    //     }

    //     if ($request->filled('polling_id')) {
    //         $query->where('polling_id', $request->polling_id);
    //     }

    //     if ($request->filled('area_id')) {
    //         $query->where('area_id', $request->area_id);
    //     }

    //     if ($request->filled('jati_id')) {
    //         $query->where('jati_id', $request->jati_id);
    //     }

    //     if ($request->filled('from_date')) {
    //         $query->whereDate('posted_date', '>=', $request->from_date);
    //     }

    //     if ($request->filled('to_date')) {
    //         $query->whereDate('posted_date', '<=', $request->to_date);
    //     }


    //     if ($request->filled('programfrom_date')) {
    //         $query->whereDate('program_date', '>=', $request->programfrom_date);
    //     }

    //     if ($request->filled('programto_date')) {
    //         $query->whereDate('program_date', '<=', $request->programto_date);
    //     }


    //     $start = $request->input('start', 0);
    //     $length = $request->input('length', 10);

    //     $recordsFiltered = $query->count();
    //     $recordsTotal = $query->count();

    //     $complaints = $query->orderBy('posted_date', 'desc')
    //         ->offset($start)
    //         ->limit($length)
    //         ->get();

    //     foreach ($complaints as $complaint) {
    //         if (!in_array($complaint->complaint_status, [13, 14, 15, 16, 17, 18])) {
    //             $complaint->pending_days = Carbon::parse($complaint->posted_date)->diffInDays(now());
    //         } else {
    //             $complaint->pending_days = 0;
    //         }

    //         $lastReply = $complaint->replies
    //             ->whereNotNull('forwarded_to')
    //             ->sortByDesc('reply_date')
    //             ->first();

    //         $complaint->forwarded_to_name = $lastReply?->forwardedToManager?->admin_name ?? '-';
    //         $complaint->forwarded_reply_date = $lastReply?->reply_date?->format('d-m-Y h:i') ?? '-';
    //     }

    //     if ($request->ajax()) {
    //         $data = [];

    //         foreach ($complaints as $index => $complaint) {
    //             if ($complaint->complaint_status == 13) {
    //                 $pendingText = 'सम्मिलित हुए';
    //             } elseif ($complaint->complaint_status == 14) {
    //                 $pendingText = 'सम्मिलित नहीं हुए';
    //             } elseif ($complaint->complaint_status == 15) {
    //                 $pendingText = 'फोन पर संपर्क किया';
    //             } elseif ($complaint->complaint_status == 16) {
    //                 $pendingText = 'ईमेल पर संपर्क किया';
    //             } elseif ($complaint->complaint_status == 17) {
    //                 $pendingText = 'व्हाट्सएप पर संपर्क किया';
    //             } elseif ($complaint->complaint_status == 18) {
    //                 $pendingText = 'रद्द';
    //             } else {
    //                 $pendingText = $complaint->pending_days . ' दिन';
    //             }

    //             $data[] = [
    //                 'index' => $start + $index + 1,
    //                 'name' => "<strong>शिकायत क्र.: </strong>{$complaint->complaint_number}<br>" .
    //                     "<strong>नाम: </strong>{$complaint->name}<br>" .
    //                     "<strong>मोबाइल: </strong>{$complaint->mobile_number}<br>" .
    //                     "<strong>पुत्र श्री: </strong>{$complaint->father_name}<br>" .
    //                     "<strong>जाति: </strong>" . ($complaint->jati->jati_name ?? '-') . "<br>" .
    //                     "<strong>स्थिति: </strong>{$complaint->statusTextPlain()}",

    //                 'reference_name' => $complaint->reference_name ?? '',

    //                 'area_details' => "<strong>संभाग: </strong>" . ($complaint->division?->division_name ?? '') . ",<br>" .
    //                     "<strong>जिला: </strong>" . ($complaint->district?->district_name ?? '') . ",<br>" .
    //                     "<strong>विधानसभा: </strong>" . ($complaint->vidhansabha?->vidhansabha ?? '') . ",<br>" .
    //                     "<strong>मंडल: </strong>" . ($complaint->mandal?->mandal_name ?? '') . ",<br>" .
    //                     "<strong>नगर/ग्राम: </strong>" . ($complaint->gram?->nagar_name ?? '') . ",<br>" .
    //                     "<strong>मतदान केंद्र: </strong>" . ($complaint->polling?->polling_name ?? '') .
    //                     " (" . ($complaint->polling?->polling_no ?? '') . ") ,<br>" .
    //                     "<strong>ग्राम/वार्ड: </strong>" . ($complaint->area?->area_name ?? '') . ",<br>",

    //                 'posted_date' => "<strong>तिथि: " . \Carbon\Carbon::parse($complaint->posted_date)->format('d-m-Y h:i A') . "</strong><br>" . $pendingText,


    //                 'applicant_name' => $complaint->admin->admin_name ?? '',

    //                 'forwarded_to_name' => ($complaint->forwarded_to_name ?? '-') . '<br>' . ($complaint->forwarded_reply_date ?? '-'),
    //                 'issue_description' => $complaint->issue_description,
    //                 'issue_title' => $complaint->issue_title,
    //                 'program_date' => $complaint->program_date,
    //                 'action' => '<div style="display: inline-flex; gap: 5px; white-space: nowrap;">
    //                 <a href="' . route('admincomplaints.summary', $complaint->complaint_id) . '" class="btn btn-sm btn-warning" style="white-space: nowrap;">विवरण देखें</a>
    //                 <button class="btn btn-sm btn-danger delete-complaint" data-id="' . $complaint->complaint_id . '">हटाएं</button>
    //                 </div>',

    //                 'voter_id' => $complaint->voter_id ?? ''
    //             ];
    //         }

    //         return response()->json([
    //             'draw' => intval($request->input('draw')),
    //             'recordsTotal' => $recordsTotal,
    //             'recordsFiltered' => $recordsFiltered,
    //             'data' => $data
    //         ]);
    //     }

    //     $mandals = Mandal::where('vidhansabha_id', 49)->get();
    //     $grams = $request->mandal_id ? Nagar::where('mandal_id', $request->mandal_id)->get() : collect();
    //     $pollings = $request->gram_id ? Polling::where('nagar_id', $request->gram_id)->get() : collect();
    //     $areas = $request->polling_id ? Area::where('polling_id', $request->polling_id)->get() : collect();
    //     $departments = Department::all();
    //     $jatis = Jati::all();
    //     $replyOptions = ComplaintReply::all();
    //     $managers = User::where('role', 2)->get();

    //     return view('admin.operator_suchna', compact(
    //         'complaints',
    //         'mandals',
    //         'grams',
    //         'pollings',
    //         'areas',
    //         'departments',
    //         'replyOptions',
    //         'managers',
    //         'jatis'
    //     ));
    // }

    public function OperatorSuchnas(Request $request)
    {
        $query = Complaint::with('registrationDetails', 'replies.forwardedToManager')->where('type', 2)->whereIn('complaint_type', ['अशुभ सुचना', 'शुभ सुचना']);

        if ($request->filled('complaintOtherFilter')) {
            switch ($request->complaintOtherFilter) {
                case 'forwarded_manager':
                    $query->whereHas('replies', function ($q2) use ($request) {
                        $q2->where('forwarded_to', $request->admin_id);
                    });
                    break;

                case 'not_opened':
                    $query->whereHas('latestReply', function ($q) use ($request) {
                        $q->where('forwarded_to', $request->admin_id);
                    });
                    break;

                case 'cancel':
                    $query->where('complaint_status', 18);
                    break;

                case 'sammilit_done':
                    $query->where('complaint_status', 13);
                    break;

                case 'sammilit_notdone':
                    $query->where('complaint_status', 14);
                    break;

                case 'reference_null':
                    $query->whereNull('reference_name');
                    break;

                case 'reference':
                    $query->whereNotNull('reference_name');
                    break;
            }
        }

        if ($request->filled('complaint_status')) {
            $query->where('complaint_status', $request->complaint_status);
        }

        if ($request->filled('filter')) {
            switch ($request->filter) {
                case 'not_opened':
                    $query->whereHas('replies', function ($q) {
                        $q->where('complaint_status', 11)
                            ->where('complaint_reply', 'सूचना दर्ज की गई है।')
                            ->where('forwarded_to', 6)
                            ->whereNull('selected_reply');
                    });
                    break;

                case 'cancel':
                    $query->where('complaint_status', 18);
                    break;

                case 'sammilit_done':
                    $query->where('complaint_status', 13);
                    break;

                case 'sammilit_notdone':
                    $query->where('complaint_status', 14);
                    break;

                case 'reference_null':
                    $query->whereNull('reference_name');
                    break;

                case 'reference':
                    $query->whereNotNull('reference_name');
                    break;
            }
        }

        if ($request->filled('complaint_type')) {
            $query->where('complaint_type', $request->complaint_type);
        } else if (!$request->has('filter')) {
            $query->where('complaint_type', 'शुभ सुचना');
        }

        if ($request->filled('admin_id')) {
            $query->whereHas('replies', function ($q) use ($request) {
                $q->where('forwarded_to', $request->admin_id);
            });
        }

        if ($request->filled('issue_title')) {
            $query->where('issue_title', $request->issue_title);
        }

        if ($request->filled('division_id')) {
            $query->where('division_id', $request->division_id);
        }

        if ($request->filled('district_id')) {
            $query->where('district_id', $request->district_id);
        }

        if ($request->filled('vidhansabha_id')) {
            $query->where('vidhansabha_id', $request->vidhansabha_id);
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

        if ($request->filled('jati_id')) {
            $query->where('jati_id', $request->jati_id);
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
            $complaint->forwarded_reply_date = $lastReply?->reply_date?->format('d-m-Y h:i') ?? '-';
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
                    'issue_description' => $complaint->issue_description,
                    'issue_title' => $complaint->issue_title,
                    'program_date' => $complaint->program_date,
                    'action' => '<div style="display: inline-flex; gap: 5px; white-space: nowrap;">
                    <a href="' . route('admincomplaints.summary', $complaint->complaint_id) . '" class="btn btn-sm btn-warning" style="white-space: nowrap;">विवरण देखें</a>
                    <button class="btn btn-sm btn-danger delete-complaint" data-id="' . $complaint->complaint_id . '">हटाएं</button>
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

        $divisions = Division::all();
        $districts = $request->division_id ? District::where('division_id', $request->division_id)->get() : collect();
        $vidhansabhas = $request->district_id ? VidhansabhaLokSabha::where('district_id', $request->district_id)->get() : collect();
        $mandals = $request->vidhansabha_id ? Mandal::where('vidhansabha_id', $request->vidhansabha_id)->get() : collect();
        $grams = $request->mandal_id ? Nagar::where('mandal_id', $request->mandal_id)->get() : collect();
        $pollings = $request->gram_id ? Polling::where('nagar_id', $request->gram_id)->get() : collect();
        $areas = $request->polling_id ? Area::where('polling_id', $request->polling_id)->get() : collect();
        $departments = Department::all();
        $jatis = Jati::all();
        $replyOptions = ComplaintReply::all();
        $managers = User::where('role', 2)->get();

        return view('admin.operator_suchna', compact(
            'complaints',
            'divisions',
            'districts',
            'vidhansabhas',
            'mandals',
            'grams',
            'pollings',
            'areas',
            'departments',
            'replyOptions',
            'managers',
            'jatis'
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
            'area'
        )->findOrFail($id);

        $replyOptions = ComplaintReply::all();
        $managers = User::where('role', 2)->get();

        $latestReply = $complaint->replies()->latest('reply_date')->first();
        $disableReply = false;

        if ($latestReply && in_array($latestReply->complaint_status, [4, 5, 13, 14, 15, 16, 17, 18])) {
            $disableReply = true;
        }

        return view('admin/details_complaint', [
            'complaint' => $complaint,
            'replyOptions' => $replyOptions,
            'managers' => $managers,
            'disableReply' => $disableReply
        ]);
    }

    public function postReply(Request $request, $id)
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
        $reply->need_followup = $request->input('needfollowup') ? 1 : 0;



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

        // if ($request->ajax()) {
        //     return response()->json([
        //         'message' => 'शिकायत का उत्तर सफलतापूर्वक दर्ज किया गया और स्थिति अपडेट हो गई।'
        //     ]);
        // }

        if ($request->ajax()) {
            $message = 'शिकायत का उत्तर सफलतापूर्वक दर्ज किया गया और स्थिति अपडेट हो गई।';

            if ($complaint->complaint_type === 'शुभ सुचना' || $complaint->complaint_type === 'अशुभ सुचना') {
                $message = 'सूचना का उत्तर सफलतापूर्वक दर्ज किया गया और स्थिति अपडेट हो गई।';
            }

            return response()->json([
                'message' => $message
            ]);
        }

        return redirect()->route('complaints.show', $id)
            ->with('success', 'जवाब प्रस्तुत किया गया और शिकायत अपडेट की गई');
    }

    // assign responsibility functions
    public function reponsibility_index()
    {
        $districts = District::all();
        $levels = Level::all();
        $positions = Position::all();
        return view('admin/assign_responsibility', compact('districts', 'levels', 'positions'));
    }

    public function responsibility_filter(Request $request)
    {
        $start = $request->input('start', 0);
        $length = $request->input('length', 10);

        $query = RegistrationForm::query()
            ->where('registration_form.type', 2)
            ->join('step2', 'step2.registration_id', '=', 'registration_form.registration_id')
            ->join('step3', 'step3.registration_id', '=', 'registration_form.registration_id');

        if ($request->filled('mobile')) {
            $query->where('registration_form.member_id', $request->mobile);
        }
        if ($request->filled('education')) {
            $query->where('registration_form.education', $request->education);
        }
        if ($request->filled('category')) {
            $query->where('registration_form.caste', $request->category);
        }
        if ($request->filled('business')) {
            $query->where('registration_form.business', $request->business);
        }
        if ($request->filled('district_id')) {
            $query->where('step2.district', $request->district_id);
        }
        if ($request->filled('vidhansabha_id')) {
            $query->where('step2.vidhansabha', $request->vidhansabha_id);
        }

        $totalFiltered = $query->count();
        $results = $query->select(
            'registration_form.*',
            'step2.district as district_id',
            'step3.permanent_address'
        )->skip($start)
            ->take($length)
            ->get();

        foreach ($results as $r) {
            $district = District::find($r->district_id);
            $r->district = $district ? $district->district_name : null;
        }

        $data = [];
        foreach ($results as $index => $b) {
            $date = $b->date_time ? \Carbon\Carbon::parse($b->date_time)->format('d-m-Y') : '';
            $isAssigned = AssignPosition::where('member_id', $b->registration_id)->exists();

            $assignButton = $isAssigned
                ? "<button class='btn btn-info btn-sm mr-1 already-assigned' data-name='{$b->name}'>Assigned</button>"
                : "<a href='#' class='btn btn-primary btn-sm chk' data-id='{$b->registration_id}' data-toggle='modal' data-target='#assignModal'>Assign</a>";
            $data[] = [
                'sr_no' => $start + $index + 1,
                'member_id' => $b->member_id,
                'name' => "{$b->name}<br>{$b->mobile1}<br>{$b->gender}",
                'address' => $b->permanent_address,
                'district' => $b->district,
                'photo' => "<img src='" . asset('assets/upload/' . $b->photo) . "' height='100' />",
                'post_date' => $b->date_time ? \Carbon\Carbon::parse($b->date_time)->format('d-m-Y') : '',
                'action' => "<div class='d-flex'>
            <a href='" . route('register.show', $b->registration_id) . "' class='btn btn-success btn-sm mr-1'>View</a>
            <a href='" . route('register.card', ['id' => $b->registration_id]) . "' class='btn btn-warning btn-sm mr-1'>Card</a>
            {$assignButton}
        </div>"
            ];
        }

        return response()->json([
            "draw" => intval($request->input('draw')),
            "recordsTotal" => RegistrationForm::where('type', 2)->count(),
            "recordsFiltered" => $totalFiltered,
            "data" => $data
        ]);
    }

    public function downloadFullData(Request $request)
    {
        $query = RegistrationForm::query()
            ->join('step2', 'step2.registration_id', '=', 'registration_form.registration_id')
            ->join('step3', 'step3.registration_id', '=', 'registration_form.registration_id');

        if ($request->filled('mobile')) {
            $query->where('registration_form.member_id', $request->mobile);
        }
        if ($request->filled('education')) {
            $query->where('registration_form.education', $request->education);
        }
        if ($request->filled('category')) {
            $query->where('registration_form.caste', $request->category);
        }
        if ($request->filled('business')) {
            $query->where('registration_form.business', $request->business);
        }
        if ($request->filled('district_id')) {
            $query->where('step2.district', $request->district_id);
        }
        if ($request->filled('vidhansabha_id')) {
            $query->where('step2.vidhansabha', $request->vidhansabha_id);
        }

        $results = $query->select(
            'registration_form.member_id',
            'registration_form.name',
            'registration_form.mobile1',
            'registration_form.gender',
            'step3.permanent_address',
            'step2.district',
            'registration_form.date_time'
        )->get();

        $csvData = [];
        foreach ($results as $item) {
            $csvData[] = [
                'Member ID' => $item->member_id,
                'Name' => $item->name,
                'Mobile' => $item->mobile1,
                'Gender' => $item->gender,
                'Address' => $item->permanent_address,
                'District' => District::find($item->district)->district_name ?? '',
                'Post Date' => $item->date_time ? \Carbon\Carbon::parse($item->date_time)->format('d-m-Y') : ''
            ];
        }

        // Create CSV file download
        $filename = "filtered_data_" . now()->format('Ymd_His') . ".csv";
        $handle = fopen('php://output', 'w');

        header('Content-Type: text/csv');
        header("Content-Disposition: attachment; filename=$filename");

        // Write headers
        fputcsv($handle, array_keys($csvData[0] ?? []));

        // Write rows
        foreach ($csvData as $row) {
            fputcsv($handle, $row);
        }

        fclose($handle);
        exit;
    }


    public function responsibility_store(Request $request)
    {
        $validated = $request->validate([
            'member_id' => 'required|integer',
            'workarea' => 'required|string',
            'position_id' => 'required|integer',
            'from' => 'required|date',
            'to' => 'required|date',
        ]);

        $workarea = $request->input('workarea');

        switch ($workarea) {
            case 'प्रदेश':
                $ref_id = $request->input('txtpradesh');
                break;
            case 'जिला':
                $ref_id = $request->input('txtdistrict');
                break;
            case 'विधानसभा':
                $ref_id = $request->input('txtvidhansabha');
                break;
            case 'मंडल':
                $ref_id = $request->input('txtmandal');
                break;
            case 'कमाण्ड ऐरिया':
                $ref_id = $request->input('txtgram');
                break;
            case 'ग्राम/वार्ड चौपाल':
                $ref_id = $request->input('area_name');
                break;
            default:
                $ref_id = null;
        }

        if (!$ref_id) {
            return redirect()->back()->with('error', 'Reference ID missing.');
        }

        DB::table('assign_position')->where('member_id', $request->member_id)->delete();

        DB::table('assign_position')->insert([
            'member_id' => $request->member_id,
            'level_name' => $workarea,
            'refrence_id' => $ref_id,
            'position_id' => $request->position_id,
            'from_date' => Carbon::parse($request->from),
            'to_date' => Carbon::parse($request->to),
            'status' => 0,
            'post_date' => now(),
        ]);

        // DB::table('registration_form')->where('registration_id', $request->member_id)
        //     ->update([
        //         'position_id' => $request->position_id,
        //         'type' => 3
        //     ]);

        return redirect()->back()->with('success', 'दायित्व सफलतापूर्वक जोड़ा गया।');
    }


    public function getVidhansabhasByDistrict(Request $request)
    {
        $districtId = $request->district_id;

        $vidhansabhas = VidhansabhaLokSabha::where('district_id', $districtId)->get(['vidhansabha_id', 'vidhansabha', 'loksabha']);

        return response()->json($vidhansabhas);
    }

    public function fetchLocationData($registration_id)
    {
        $step2 = Step2::where('registration_id', $registration_id)->first();

        if (!$step2) {
            return response()->json(['error' => 'Step2 data not found.'], 404);
        }

        return response()->json([
            'district_id' => $step2->district,
            'vidhansabha' => $step2->vidhansabha,
        ]);
    }

    public function getVidhansabha($district_id)
    {
        return VidhansabhaLokSabha::where('district_id', $district_id)->get(['vidhansabha_id', 'vidhansabha']);
    }

    public function getMandal($vidhansabha_id)
    {
        return response()->json(
            Mandal::where('vidhansabha_id', $vidhansabha_id)->get()
        );
    }

    public function getNagar($mandal_id)
    {
        return response()->json(
            Nagar::where('mandal_id', $mandal_id)->get()
        );
    }

    public function getPolling($gram_id)
    {
        return response()->json(
            Polling::where('nagar_id', $gram_id)->get()
        );
    }

    public function getArea($polling_id)
    {
        return response()->json(
            Area::where('polling_id', $polling_id)->get()
        );
    }

    // view responsibility member functions
    public function viewResponsibilities(Request $request)
    {
        $query = AssignPosition::with(['member', 'position', 'addressInfo', 'district']);

        if ($request->filled('name')) {
            $query->whereHas('member', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->name . '%');
            });
        }

        if ($request->filled('jati')) {
            $query->whereHas('member', function ($q) use ($request) {
                $q->where('jati', $request->jati);
            });
        }

        if ($request->filled('from_date') && $request->filled('to_date')) {
            $query->whereBetween('post_date', [$request->from_date, $request->to_date]);
        } elseif ($request->filled('from_date')) {
            $query->whereDate('post_date', '>=', $request->from_date);
        } elseif ($request->filled('to_date')) {
            $query->whereDate('post_date', '<=', $request->to_date);
        }

        if ($request->filled('workarea')) {
            $query->where('level_name', $request->workarea);
        }

        if ($request->filled('position_id')) {
            $query->where('position_id', $request->position_id);
        }

        $assignments = $query->get();

        $districts = District::all();
        $jaties = Jati::all();

        return view('admin/view_responsibility', compact('assignments', 'districts', 'jaties'));
    }


    public function assign_destroy($id)
    {
        $assignment = AssignPosition::findOrFail($id);
        $assignment->delete();

        return redirect()->route('view_responsibility.index')->with('delete_msg', 'दायित्व सफलतापूर्वक हटाया गया!');
    }

    public function fetchFullResponsibilityData($registration_id)
    {
        $step2 = Step2::where('registration_id', $registration_id)->first();
        $assign = AssignPosition::where('member_id', $registration_id)->first();

        if (!$step2 || !$assign) {
            return response()->json(['error' => 'Data not found.'], 404);
        }

        $level = $assign->level_name;
        $refId = $assign->refrence_id;
        $workarea_name = '';

        // Define all IDs as null initially
        $district_id = $step2->district;
        $vidhansabha_id = $step2->vidhansabha;
        $mandal_id = null;
        $gram_id = null;
        $polling_id = null;
        $area_id = null;

        switch ($level) {
            case 'प्रदेश':
                $workarea_name = 'मध्य प्रदेश';
                break;

            case 'जिला':
                $district_id = $refId;
                $district = DB::table('district_master')->where('district_id', $district_id)->value('district_name');
                $workarea_name = $district;
                break;

            case 'विधानसभा':
                $vidhansabha_id = $refId;
                $vidhansabha = DB::table('vidhansabha_loksabha')->where('vidhansabha_id', $vidhansabha_id)->value('vidhansabha');
                $workarea_name = $vidhansabha;
                break;

            case 'मंडल':
                $mandal = DB::table('mandal')->where('mandal_id', $refId)->first();
                $mandal_id = $mandal->mandal_id;
                $vidhansabha_id = $mandal->vidhansabha_id;
                $workarea_name = "मंडल : $mandal->mandal_name";
                break;

            case 'नगर केंद्र/ग्राम केंद्र':
                $nagar = DB::table('nagar_master')->where('nagar_id', $refId)->first();
                $gram_id = $nagar->nagar_id;
                $mandal_id = $nagar->mandal_id;
                $mandal = DB::table('mandal')->where('mandal_id', $mandal_id)->first();
                $vidhansabha_id = $mandal->vidhansabha_id;
                $workarea_name = "नगर केंद्र/ग्राम केंद्र : $nagar->nagar_name, <br/>मंडल : $mandal->mandal_name";
                break;

            case 'ग्राम/वार्ड चौपाल':
                $area = DB::table('area_master')->where('area_id', $refId)->first();
                $polling_id = $area->polling_id;
                $polling = DB::table('gram_polling')
                    ->join('nagar_master', 'gram_polling.nagar_id', '=', 'nagar_master.nagar_id')
                    ->where('gram_polling.gram_polling_id', $polling_id)
                    ->select('gram_polling.gram_polling_id', 'nagar_master.nagar_name', 'nagar_master.mandal_id', 'nagar_master.nagar_id')
                    ->first();

                $mandal_id = $polling->mandal_id;
                $gram_id = $polling->nagar_id;
                $mandal = DB::table('mandal')->where('mandal_id', $mandal_id)->first();
                $vidhansabha_id = $mandal->vidhansabha_id;
                $area_id = $refId;

                $workarea_name = "$level : $area->area_name, <br/>नगर केंद्र/ग्राम केंद्र : $polling->nagar_name, <br/>मंडल : $mandal->mandal_name";
                break;
        }

        return response()->json([
            'district_id'    => $district_id,
            'vidhansabha_id' => $vidhansabha_id,
            'mandal_id'      => $mandal_id,
            'gram_id'        => $gram_id,
            'polling_id'     => $polling_id,
            'area_id'        => $area_id,

            'workarea'       => $assign->level_name,
            'ref_id'         => $assign->refrence_id,
            'position_id'    => $assign->position_id,
            'from'           => $assign->from_date,
            'to'             => $assign->to_date,
            'workarea_name'  => $workarea_name,
        ]);
    }



    public function responsibility_update(Request $request, $assign_position_id)
    {

        $validated = $request->validate([
            'member_id' => 'required|integer',
            'workarea' => 'required|string',
            'position_id' => 'required|integer',
            'from' => 'required|date',
            'to' => 'required|date',
        ]);

        $workarea = $request->input('workarea');

        switch ($workarea) {
            case 'प्रदेश':
                $ref_id = $request->input('txtpradesh');
                break;
            case 'जिला':
                $ref_id = $request->input('txtdistrict');
                break;
            case 'विधानसभा':
                $ref_id = $request->input('txtvidhansabha');
                break;
            case 'मंडल':
                $ref_id = $request->input('txtmandal');
                break;
            case 'कमाण्ड ऐरिया':
                $ref_id = $request->input('txtgram');
                break;
            case 'ग्राम/वार्ड चौपाल':
                $ref_id = $request->input('area_name');
                break;
            default:
                $ref_id = null;
        }

        if (!$ref_id) {
            return redirect()->back()->with('error', 'Reference ID missing.');
        }

        DB::table('assign_position')->where('assign_position_id', $assign_position_id)->update([
            'member_id' => $request->member_id,
            'level_name' => $workarea,
            'refrence_id' => $ref_id,
            'position_id' => $request->position_id,
            'from_date' => Carbon::parse($request->from),
            'to_date' => Carbon::parse($request->to),
            'status' => 0,
            'post_date' => now(),
        ]);

        // DB::table('registration_form')->where('registration_id', $request->member_id)
        //     ->update(['position_id' => $request->position_id]);

        return redirect()->back()->with('update_msg', 'दायित्व सफलतापूर्वक अपडेट किया गया।');
    }

    public function nagarStore(Request $request)
    {
        $mandalId = $request->txtmandal;
        $gramNames = $request->gram_name;
        $mandalType = $request->mandal_type;
        $date = now();

        $newNagars = [];

        foreach ($gramNames as $name) {
            $exists = DB::table('nagar_master')
                ->where('mandal_id', $mandalId)
                ->where('nagar_name', $name)
                ->exists();

            if ($exists) {
                return response()->json(['error' => 'ये नगर और मंडल पहले से हैं'], 409);
            }

            $id = DB::table('nagar_master')->insertGetId([
                'mandal_id' => $mandalId,
                'mandal_type' => $mandalType,
                'nagar_name' => $name,
                'post_date' => $date,
            ]);

            $newNagars[] = [
                'id' => $id,
                'name' => $name,
            ];
        }

        return response()->json(['success' => true, 'nagars' => $newNagars]);
    }


    public function generate()
    {
        $assignments = AssignPosition::with([
            'member',
            'position',
            'addressInfo',
        ])->get();

        $cards = collect();


        foreach ($assignments as $assign) {
            $member = $assign->member;

            if (!$member) continue;

            $backgroundPath = public_path('assets/images/member_back.jpg');

            $photoFile = $member->photo ?? 'default.png';
            $photoPath = public_path('assets/upload/' . $photoFile);
            if (!file_exists($photoPath)) {
                $photoPath = public_path('assets/upload/default.png');
            }

            $cards[] = [
                'photoPath' => 'file://' . $photoPath,
                'name' => $member->name,
                'mobile' => $member->mobile1,
                'position' => optional($assign->position)->position_name ?? '—',
                'address' => optional($assign->addressInfo)->permanent_address ?? '—',
                'workarea' => $assign->level_name . ' ' . $this->resolveWorkAreaName($assign->level_name, $assign->refrence_id),
            ];
        }

        $html = view('admin/card_responsibility_pdf', [
            'cards' => $cards->toArray(),
            'backgroundPath' => $backgroundPath
        ])->render();

        $mpdf = new Mpdf([
            'mode' => 'utf-8',
            'default_font' => 'FreeSans',
            'format' => 'A4',
            'margin_left' => 10,
            'margin_right' => 10,
            'margin_top' => 0,
            'margin_bottom' => 0,
        ]);

        $mpdf->showImageErrors = true;
        $mpdf->WriteHTML($html);
        $mpdf->SetDisplayMode('fullpage');

        return response($mpdf->Output('', 'S'))->header('Content-Type', 'application/pdf');
    }

    private function resolveWorkAreaName($level, $refrence_id)
    {
        switch ($level) {
            case 'प्रदेश':
                return 'मध्य प्रदेश';

            case 'जिला':
                return optional(District::find($refrence_id))->district_name;

            case 'विधानसभा':
                return optional(VidhansabhaLokSabha::find($refrence_id))->vidhansabha;

            case 'मंडल':
                return optional(Mandal::find($refrence_id))->mandal_name;

            case 'कमाण्ड ऐरिया':
                $nagar = nagar::find($refrence_id);
                if ($nagar) {
                    $type = $nagar->mandal_type == 1 ? 'ग्रामीण मंडल' : 'नगर मंडल';
                    return "$type, $nagar->nagar_name";
                }
                return '';

            case 'ग्राम/वार्ड चौपाल':
                $area = Area::find($refrence_id);
                return $area->area_name ?? '';

            default:
                return '';
        }
    }


    // upload voters data functions
    public function upload()
    {
        return view('admin/upload_voter');
    }

    public function exportVoterExcel()
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // $headers = [
        //     'name',
        //     'membership',
        //     'gender',
        //     'dob',
        //     'age',
        //     'mobile1',
        //     'mobile2',
        //     'mobile1_whatsapp',
        //     'mobile2_whatsapp',
        //     'religion',
        //     'caste',
        //     'jati',
        //     'education',
        //     'business',
        //     'position',
        //     'voter_id',
        //     'father_name',
        //     'email',
        //     'pincode',
        //     'samagra_id',
        //     'position_id',
        // ];

        $headers = [
            'name',
            'guardian',
            'house',

            'age',
            'gender',
            'voter_id',

            'area_name',

        ];

        $sheet->fromArray($headers, null, 'A1');

        $writer = new Xlsx($spreadsheet);
        $filename = 'voter_sheet.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header("Content-Disposition: attachment; filename=\"$filename\"");
        $writer->save('php://output');
        exit;
    }


    // public function uploadVoterData(Request $request)
    // {
    //     $request->validate([
    //         'voter_excel' => 'required|file|mimes:xlsx,xls,csv'
    //     ]);

    //     $file = $request->file('voter_excel');
    //     $spreadsheet = IOFactory::load($file);
    //     $sheetData = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);

    //     unset($sheetData[1]);

    //     foreach ($sheetData as $row) {
    //         $mobile1  = $row['F'] ?? null; 
    //         $voter_id = $row['P'] ?? null; 

    //         if (!$mobile1) {
    //             continue;
    //         }

    //         $dobRaw = $row['D'] ?? null;
    //         $dob = null;
    //         if (!empty($dobRaw)) {
    //             try {
    //                 $dob = Carbon::parse($dobRaw)->format('Y-m-d');
    //             } catch (\Exception $e) {
    //                 $dob = null;
    //             }
    //         }

    //         $data = [
    //             'reference_id'      => 0,
    //             'member_id'         => $mobile1,
    //             'name'              => $row['A'] ?? null,
    //             'membership'        => $row['B'] ?? null,
    //             'gender'            => $row['C'] ?? null,
    //             'dob'               => $dob,
    //             'age'               => $row['E'] ?? null,
    //             'mobile1'           => $mobile1,
    //             'mobile2'           => $row['G'] ?? null,
    //             'mobile1_whatsapp'  => $row['H'] ?? 0,
    //             'mobile2_whatsapp'  => $row['I'] ?? null,
    //             'religion'          => $row['J'] ?? null,
    //             'caste'             => $row['K'] ?? null,
    //             'jati'              => $row['L'] ?? null,
    //             'education'         => $row['M'] ?? null,
    //             'business'          => $row['N'] ?? null,
    //             'position'          => $row['O'] ?? null,
    //             'voter_id'          => $voter_id,
    //             'father_name'       => $row['Q'] ?? null,
    //             'email'             => $row['R'] ?? null,
    //             'photo'             => 'NA',
    //             'pincode'           => $row['S'] ?? null,
    //             'samagra_id'        => $row['T'] ?? null,
    //             'otp_recieved'      => 'NA',
    //             'position_id'       => $row['U'] ?? 0,
    //             'date_time'         => Carbon::now(),
    //             'type'              => 1,
    //         ];

    //         $data = [
    //             'reference_id'      => 0,
    //             'member_id'         => 0,
    //             'name'              => $row['A'] ?? null,
    //             'membership'        => 'N/A',
    //             'gender'            => $row['C'] ?? null,
    //             'dob'               => 'N/A',
    //             'age'               => $row['E'] ?? null,
    //             'mobile1'           => 'N/A',
    //             'mobile2'           => 'N/A',
    //             'mobile1_whatsapp'  => 0,
    //             'mobile2_whatsapp'  => 0,
    //             'religion'          => 'N/A',
    //             'caste'             => 'N/A',
    //             'jati'              => 'N/A',
    //             'education'         => 'N/A',
    //             'business'          => 'N/A',
    //             'position'          => 'N/A',
    //             'voter_id'          => 'N/A',
    //             'father_name'       => $row['Q'] ?? null,
    //             'email'             => 'N/A',
    //             'photo'             => 'NA',
    //             'pincode'           => 'N/A',
    //             'samagra_id'        => 'N/A',
    //             'otp_recieved'      => 'NA',
    //             'position_id'       => 'N/A',
    //             'date_time'         => Carbon::now(),
    //             'type'              => 1,
    //         ];

    //         $exists = DB::table('registration_form')->where('mobile1', $mobile1)->exists();

    //         if ($exists) {
    //             DB::table('registration_form')->where('mobile1', $mobile1)->update($data);
    //         } else {
    //             DB::table('registration_form')->insert($data);
    //         }
    //     }

    //     return back()->with('success', 'एक्सेल फ़ाइल सफलतापूर्वक संसाधित हो गई!');
    // }

    // public function uploadVoterData(Request $request)
    // {
    //     $request->validate([
    //         'voter_excel' => 'required|file|mimes:xlsx,xls'
    //     ]);

    //     $file = $request->file('voter_excel');
    //     $spreadsheet = IOFactory::load($file);
    //     $sheetData = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);

    //     unset($sheetData[1]);

    //     $successCount = 0;
    //     $failedRows = [];
    //     $repeatCount = 0;

    //     foreach ($sheetData as $index => $row) {
    //         DB::beginTransaction();

    //         try {
    //             $voter_id  = $row['G'] ?? null;
    //             $area_name = $row['H'] ?? null;
    //             $house        = $row['D'] ?? '';
    //             $name         = $row['B'] ?? '-';
    //             $jati   =   $row['I'] ?? 'N/A';
    //             $polling_no      = $row['J'] ?? 0;
    //             $total_family   =  $row['K'] ?? 0;
    //             $mukhiya_mobile  =  $row['L'] ?? '';

    //             if (!$voter_id) throw new \Exception("Missing voter_id");

    //             if (DB::table('registration_form')->where('voter_id', $voter_id)->exists()) {
    //                 DB::rollBack();
    //                 $repeatCount++;
    //                 continue;
    //             }

    //             if (!$area_name) {
    //                 throw new \Exception("Missing Area");
    //             }

    //             $area = DB::table('area_master')->where('area_name', $area_name)->first();
    //             if (!$area) {
    //                 throw new \Exception("Invalid Area");
    //             }

    //             $polling = DB::table('gram_polling')->where('gram_polling_id', $area->polling_id)->first();
    //             if (!$polling) {
    //                 throw new \Exception("Polling info not found");
    //             }

    //             // if (!$house) {
    //             //     throw new \Exception("House information is missing");
    //             // }

    //             $house = trim($house);

    //             if (!preg_match('/^[\p{L}0-9\x{0966}-\x{096F}\/\- ]+$/u', $house)) {
    //                 throw new \Exception("Invalid house format");
    //             }
    //             if (!$jati) {
    //                 throw new \Exception("Caste (jati) is required");
    //             }

    //             if (!is_numeric($polling_no)) {
    //                 throw new \Exception("Invalid polling number");
    //             }

    //             if (!is_numeric($total_family)) {
    //                 throw new \Exception("Invalid total family number");
    //             }

    //             if (!empty($mukhiya_mobile) && !preg_match('/^\d{10}$|^\d{12}$/', $mukhiya_mobile)) {
    //                 throw new \Exception("Invalid Mukhiya mobile number (must be 10 or 12 digits)");
    //             }

    //             $registrationId = DB::table('registration_form')->insertGetId([
    //                 'reference_id'      => 0,
    //                 'member_id'         => 0,
    //                 'name'              => $name,
    //                 'membership'        => "N/A",
    //                 'gender'            => $row['F'] ?? null,
    //                 'dob'               => null,
    //                 'age'               => $row['E'] ?? null,
    //                 'mobile1'           => 'N/A',
    //                 'mobile2'           => 'N/A',
    //                 'mobile1_whatsapp'  => 0,
    //                 'mobile2_whatsapp'  => 0,
    //                 'religion'          => 'N/A',
    //                 'caste'             => 'N/A',
    //                 'jati'              => $jati,
    //                 'education'         => 'N/A',
    //                 'business'          => 'N/A',
    //                 'position'          => 'N/A',
    //                 'voter_id'          => $voter_id,
    //                 'father_name'       => $row['C'] ?? null,
    //                 'email'             => 'N/A',
    //                 'photo'             => 'NA',
    //                 'pincode'           => 'N/A',
    //                 'samagra_id'        => 'N/A',
    //                 'otp_recieved'      => 'NA',
    //                 'position_id'       => 0,
    //                 'date_time'         => now(),
    //                 'type'              => 1,
    //                 'death_left'        => $row['M'] ?? ''
    //             ]);

    //             DB::table('step2')->updateOrInsert(
    //                 ['registration_id' => $registrationId],
    //                 [
    //                     'division_id'        => 2,
    //                     'district'           => 11,
    //                     'vidhansabha'        => 49,
    //                     'mandal_type'        => 1,
    //                     'mandal'             => $polling->mandal_id,
    //                     'nagar'              => $polling->nagar_id,
    //                     'matdan_kendra_no'   => $polling_no,
    //                     'matdan_kendra_name' => $polling->polling_name,
    //                     'area_id'            => $area->area_id,
    //                     'loksabha'           => 'ग्वालियर',
    //                     'voter_front'        => 'NA',
    //                     'voter_back'         => 'NA',
    //                     'voter_number'       => $voter_id,
    //                     'house'              => $house,
    //                     'post_date'          => now(),
    //                 ]
    //             );

    //             DB::table('step3')->updateOrInsert(
    //                 ['registration_id' => $registrationId],
    //                 [
    //                     'total_member'       => $total_family,
    //                     'total_voter'        => 0,
    //                     'member_job'         => 0,
    //                     'member_name_1'      => 'NA',
    //                     'member_mobile_1'    => 0,
    //                     'member_name_2'      => 'NA',
    //                     'member_mobile_2'    => 0,
    //                     'friend_name_1'      => 'NA',
    //                     'friend_mobile_1'    => 0,
    //                     'friend_name_2'      => 'NA',
    //                     'friend_mobile_2'    => 0,
    //                     'intrest'            => 'NA',
    //                     'vehicle1'           => 'NA',
    //                     'vehicle2'           => 'NA',
    //                     'vehicle3'           => 'NA',
    //                     'permanent_address'  => 'NA',
    //                     'temp_address'       => 'NA',
    //                     'post_date'          => now(),
    //                     'mukhiya_mobile'     => $mukhiya_mobile
    //                 ]
    //             );

    //             DB::commit();
    //             $successCount++;
    //         } catch (\Exception $e) {
    //             DB::rollBack();

    //             $originalMessage = strtolower($e->getMessage());

    //             $knownErrors = [
    //                 'missing area'                  => 'Area name is missing',
    //                 'invalid area'                  => 'Area not found',
    //                 'polling info not found'        => 'Polling info not found',
    //                 'house information is missing'  => 'House is required',
    //                 'invalid house'                 => 'Invalid house format',
    //                 'caste (jati) is required'      => 'Caste is required',
    //                 'invalid polling number'        => 'Polling number should be numeric',
    //                 'invalid total family number'   => 'Family count should be numeric',
    //                 'invalid mukhiya mobile number' => 'Mukhiya mobile must be 10 or 12 digits',
    //                 'incorrect integer value'       => 'Wrong number format',
    //                 'invalid datetime format'       => 'Invalid date format',
    //                 'duplicate voter id'            => 'Duplicate voter ID'
    //             ];

    //             $simpleReason = 'Data Processing Error';

    //             foreach ($knownErrors as $key => $value) {
    //                 if (Str::contains($originalMessage, strtolower($key))) {
    //                     $simpleReason = $value;
    //                     break;
    //                 }
    //             }

    //             // if (!$simpleReason) {
    //             //     $simpleReason = $e->getMessage(); 
    //             // }

    //             $failedRows[] = [
    //                 'name'         => $name,
    //                 'father_name'  => $row['C'] ?? '',
    //                 'house'        => $house,
    //                 'age'          => $row['E'] ?? '',
    //                 'gender'       => $row['F'] ?? '',
    //                 'voter_id'     => $voter_id,
    //                 'area'         => $area_name,
    //                 'jati'         => $jati,
    //                 'polling_no'   => $polling_no,
    //                 'family_count' => $total_family,
    //                 'mukhiya_mobile' => $mukhiya_mobile,
    //                 'death_left' =>  $row['M'] ?? '',
    //                 'reason'   => $simpleReason,
    //             ];
    //         }
    //     }

    //     return response()->json([
    //         'status'        => count($failedRows) ? 'partial' : 'success',
    //         'repeat_count'  => $repeatCount,
    //         'success_count' => $successCount,
    //         'failed_count'  => count($failedRows),
    //         'errors'        => $failedRows
    //     ]);
    // }


    // public function uploadVoterData(Request $request)
    // {
    //     $request->validate([
    //         'voter_excel' => 'required|file|mimes:xlsx,xls,csv'
    //     ]);

    //     $file = $request->file('voter_excel');
    //     $spreadsheet = IOFactory::load($file);
    //     $sheetData = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);

    //     unset($sheetData[1]);

    //     $successCount = 0;
    //     $failedRows = [];

    //     foreach ($sheetData as $index => $row) {
    //         DB::beginTransaction();

    //         try {
    //             $voter_id  = $row['G'] ?? null;
    //             $area_name = $row['H'] ?? null;
    //             $house     = $row['D'] ?? null;
    //             $name      = $row['B'] ?? '-';

    //             if (!$area_name) {
    //                 throw new \Exception("Missing Area");
    //             }

    //             $area = DB::table('area_master')->where('area_name', $area_name)->first();
    //             if (!$area) {
    //                 throw new \Exception("Invalid Area");
    //             }

    //             $polling = DB::table('gram_polling')->where('gram_polling_id', $area->polling_id)->first();
    //             if (!$polling) {
    //                 throw new \Exception("No Polling Info");
    //             }

    //             if (!$house) {
    //                 throw new \Exception("No house");
    //             }

    //             if (!preg_match('/^[a-zA-Z0-9\-\/ ]+$/', $house)) {
    //                 throw new \Exception("Invalid house");
    //             }

    //             $registrationId = DB::table('registration_form')->insertGetId([
    //                 'reference_id'      => 0,
    //                 'member_id'         => 0,
    //                 'name'              => $name,
    //                 'membership'        => "N/A",
    //                 'gender'            => $row['F'] ?? null,
    //                 'dob'               => null,
    //                 'age'               => $row['E'] ?? null,
    //                 'mobile1'           => 'N/A',
    //                 'mobile2'           => 'N/A',
    //                 'mobile1_whatsapp'  => 0,
    //                 'mobile2_whatsapp'  => 0,
    //                 'religion'          => 'N/A',
    //                 'caste'             => 'N/A',
    //                 'jati'              => $row['I'] ?? 'N/A',
    //                 'education'         => 'N/A',
    //                 'business'          => 'N/A',
    //                 'position'          => 'N/A',
    //                 'voter_id'          => $voter_id,
    //                 'father_name'       => $row['C'] ?? null,
    //                 'email'             => 'N/A',
    //                 'photo'             => 'NA',
    //                 'pincode'           => 'N/A',
    //                 'samagra_id'        => 'N/A',
    //                 'otp_recieved'      => 'NA',
    //                 'position_id'       => 0,
    //                 'date_time'         => "2025-10-12",
    //                 'type'              => 1,
    //                 'death_left'        => $row['M'] ?? ''
    //             ]);

    //             DB::table('step2')->updateOrInsert(
    //                 ['registration_id' => $registrationId],
    //                 [
    //                     'division_id'        => 2,
    //                     'district'           => 11,
    //                     'vidhansabha'        => 49,
    //                     'mandal_type'        => 1,
    //                     'mandal'             => $polling->mandal_id,
    //                     'nagar'              => $polling->nagar_id,
    //                     'matdan_kendra_no'   => $row['J'] ?? 0,
    //                     'matdan_kendra_name' => $polling->polling_name,
    //                     'area_id'            => $area->area_id,
    //                     'loksabha'           => 'ग्वालियर',
    //                     'voter_front'        => 'NA',
    //                     'voter_back'         => 'NA',
    //                     'voter_number'       => $voter_id,
    //                     'house'              => $house,
    //                     'post_date'          => now(),
    //                 ]
    //             );

    //             DB::table('step3')->updateOrInsert(
    //                 ['registration_id' => $registrationId],
    //                 [
    //                     'total_member'       => $row['K'] ?? 0,
    //                     'total_voter'        => 0,
    //                     'member_job'         => 0,
    //                     'member_name_1'      => 'NA',
    //                     'member_mobile_1'    => 0,
    //                     'member_name_2'      => 'NA',
    //                     'member_mobile_2'    => 0,
    //                     'friend_name_1'      => 'NA',
    //                     'friend_mobile_1'    => 0,
    //                     'friend_name_2'      => 'NA',
    //                     'friend_mobile_2'    => 0,
    //                     'intrest'            => 'NA',
    //                     'vehicle1'           => 'NA',
    //                     'vehicle2'           => 'NA',
    //                     'vehicle3'           => 'NA',
    //                     'permanent_address'  => 'NA',
    //                     'temp_address'       => 'NA',
    //                     'post_date'          => now(),
    //                     'mukhiya_mobile'     => $row['L'] ?? ''
    //                 ]
    //             );

    //             DB::commit();
    //             $successCount++;
    //         } catch (\Exception $e) {
    //             DB::rollBack();

    //             $originalMessage = $e->getMessage();

    //             $knownErrors = [
    //                 'No area'                => 'Area name is missing',
    //                 'Invalid area'           => 'Area not found',
    //                 'No polling'             => 'Polling info not found',
    //                 'Incorrect integer value' => 'Wrong data format',
    //                 'Invalid datetime format' => 'Invalid date format',
    //                 'SQLSTATE'               => 'Database error',
    //                 'No house'               => 'House information is missing',
    //                 'Invalid house'          => 'Invalid house value provided',
    //             ];

    //             $matchedReasons = [];

    //             foreach ($knownErrors as $key => $value) {
    //                 if (stripos($originalMessage, $key) !== false) {
    //                     $matchedReasons[] = $value;
    //                 }
    //             }

    //             if (empty($matchedReasons)) {
    //                 $matchedReasons[] = 'Data processing error';
    //             }


    //             $failedRows[] = [
    //                 'name'     => $name,
    //                 'father_name' => $row['C'] ?? '',
    //                 'house'     => $house,
    //                 'age'     => $row['E'] ?? '',
    //                 'gender'     => $row['F'] ?? '',
    //                 'voter_id' => $voter_id,
    //                 'area' => $area_name,
    //                 'reason'   => $matchedReasons,
    //             ];
    //         }
    //     }

    //     return response()->json([
    //         'status'        => count($failedRows) ? 'partial' : 'success',
    //         'success_count' => $successCount,
    //         'failed_count'  => count($failedRows),
    //         'errors'        => $failedRows
    //     ]);
    // }







    // public function uploadVoterData(Request $request)
    // {
    //     $request->validate([
    //         'voter_excel' => 'required|file|mimes:xlsx,xls'
    //     ]);

    //     $file = $request->file('voter_excel');
    //     $spreadsheet = IOFactory::load($file);
    //     $sheetData = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);

    //     unset($sheetData[1]);

    //     $successCount = 0;
    //     $updateCount = 0;
    //     $failedRows = [];

    //     foreach ($sheetData as $index => $row) {
    //         DB::beginTransaction();

    //         try {
    //             $voter_id       = $row['G'] ?? '';
    //             $area_name      = $row['H'] ?? '';
    //             $house          = trim($row['D'] ?? '');
    //             $name           = $row['B'] ?? '-';
    //             $father_name    = $row['C'] ?? '';
    //             $age            = $row['E'] ?? '';
    //             $gender         = $row['F'] ?? '';
    //             $jati           = $row['I'] ?? 'N/A';
    //             $polling_no     = $row['J'] ?? 0;
    //             $total_family   = $row['K'] ?? 0;
    //             $mukhiya_mobile = $row['L'] ?? '';
    //             $death_or_left  = $row['M'] ?? '';

    //             if (!$voter_id) throw new \Exception("Missing voter_id");
    //             if (!$area_name) throw new \Exception("Missing area");

    //             $area = DB::table('area_master')->where('area_name', $area_name)->first();
    //             if (!$area) throw new \Exception("Invalid area");

    //             $polling = DB::table('gram_polling')->where('gram_polling_id', $area->polling_id)->first();
    //             if (!$polling) throw new \Exception("Polling info not found");

    //             if (!empty($house) && !preg_match('/^[\p{L}0-9\x{0966}-\x{096F}\/\- ]+$/u', $house)) {
    //                 throw new \Exception("Invalid house format");
    //             }

    //             if (!is_numeric($polling_no)) throw new \Exception("Invalid polling number");
    //             if (!is_numeric($total_family)) throw new \Exception("Invalid total family number");
    //             if (!empty($mukhiya_mobile) && !preg_match('/^\d{10}$|^\d{12}$/', $mukhiya_mobile)) {
    //                 throw new \Exception("Invalid Mukhiya mobile");
    //             }

    //             $registrationData = [
    //                 'reference_id'      => 0,
    //                 'member_id'         => 0,
    //                 'name'              => $name,
    //                 'membership'        => "N/A",
    //                 'gender'            => $gender,
    //                 'dob'               => null,
    //                 'age'               => $age,
    //                 'mobile1'           => 'N/A',
    //                 'mobile2'           => 'N/A',
    //                 'mobile1_whatsapp'  => 0,
    //                 'mobile2_whatsapp'  => 0,
    //                 'religion'          => 'N/A',
    //                 'caste'             => 'N/A',
    //                 'jati'              => $jati,
    //                 'education'         => 'N/A',
    //                 'business'          => 'N/A',
    //                 'position'          => 'N/A',
    //                 'father_name'       => $father_name,
    //                 'email'             => 'N/A',
    //                 'photo'             => 'NA',
    //                 'pincode'           => 'N/A',
    //                 'samagra_id'        => 'N/A',
    //                 'otp_recieved'      => 'NA',
    //                 'position_id'       => 0,
    //                 'date_time'         => now(),
    //                 'type'              => 1,
    //                 'voter_nature'      => 'no input',
    //                 'death_left'        => $death_or_left
    //             ];

    //             $existing = DB::table('registration_form')->where('voter_id', $voter_id)->first();

    //             DB::table('registration_form')->updateOrInsert(
    //                 ['voter_id' => $voter_id],
    //                 $registrationData
    //             );

    //             $registrationId = DB::table('registration_form')
    //                 ->where('voter_id', $voter_id)
    //                 ->value('registration_id');

    //             // Count logic
    //             if ($existing) {
    //                 $updateCount++;
    //             } else {
    //                 $successCount++;
    //             }

    //             DB::table('step2')->updateOrInsert(
    //                 ['registration_id' => $registrationId],
    //                 [
    //                     'division_id'        => 2,
    //                     'district'           => 11,
    //                     'vidhansabha'        => 49,
    //                     'mandal_type'        => 1,
    //                     'mandal'             => $polling->mandal_id,
    //                     'nagar'              => $polling->nagar_id,
    //                     'matdan_kendra_no'   => $polling_no,
    //                     'matdan_kendra_name' => $polling->polling_name,
    //                     'area_id'            => $area->area_id,
    //                     'loksabha'           => 'ग्वालियर',
    //                     'voter_front'        => 'NA',
    //                     'voter_back'         => 'NA',
    //                     'voter_number'       => $voter_id,
    //                     'house'              => $house,
    //                     'post_date'          => now(),
    //                 ]
    //             );

    //             DB::table('step3')->updateOrInsert(
    //                 ['registration_id' => $registrationId],
    //                 [
    //                     'total_member'       => $total_family,
    //                     'total_voter'        => 0,
    //                     'member_job'         => 0,
    //                     'member_name_1'      => 'NA',
    //                     'member_mobile_1'    => 0,
    //                     'member_name_2'      => 'NA',
    //                     'member_mobile_2'    => 0,
    //                     'friend_name_1'      => 'NA',
    //                     'friend_mobile_1'    => 0,
    //                     'friend_name_2'      => 'NA',
    //                     'friend_mobile_2'    => 0,
    //                     'intrest'            => 'NA',
    //                     'vehicle1'           => 'NA',
    //                     'vehicle2'           => 'NA',
    //                     'vehicle3'           => 'NA',
    //                     'permanent_address'  => 'NA',
    //                     'temp_address'       => 'NA',
    //                     'post_date'          => now(),
    //                     'mukhiya_mobile'     => $mukhiya_mobile
    //                 ]
    //             );

    //             DB::commit();
    //         } catch (\Exception $e) {
    //             DB::rollBack();


    //             $originalMessage = strtolower($e->getMessage());

    //             $knownErrors = [
    //                 'missing area'                  => 'Area name is missing',
    //                 'invalid area'                  => 'Area not found',
    //                 'polling info not found'        => 'Polling info not found',
    //                 'house information is missing'  => 'House is required',
    //                 'invalid house'                 => 'Invalid house format',
    //                 'caste (jati) is required'      => 'Caste is required',
    //                 'invalid polling number'        => 'Polling number should be numeric',
    //                 'invalid total family number'   => 'Family count should be numeric',
    //                 'invalid mukhiya mobile number' => 'Mukhiya mobile must be 10 or 12 digits',
    //                 'incorrect integer value'       => 'Wrong number format',
    //                 'invalid datetime format'       => 'Invalid date format',
    //                 'duplicate voter id'            => 'Duplicate voter ID'
    //             ];

    //             $simpleReason = 'Data Processing Error';

    //             foreach ($knownErrors as $key => $value) {
    //                 if (Str::contains($originalMessage, strtolower($key))) {
    //                     $simpleReason = $value;
    //                     break;
    //                 }
    //             }

    //             // if (!$simpleReason) {
    //             //     $simpleReason = $e->getMessage(); 
    //             // }

    //             $failedRows[] = [
    //                 'name'         => $name,
    //                 'father_name'  => $father_name,
    //                 'house'        => $house,
    //                 'age'          => $age,
    //                 'gender'       => $gender,
    //                 'voter_id'     => $voter_id,
    //                 'area'         => $area_name,
    //                 'jati'         => $jati,
    //                 'polling_no'   => $polling_no,
    //                 'family_count' => $total_family,
    //                 'mukhiya_mobile' => $mukhiya_mobile,
    //                 'death_left'   => $death_or_left,
    //                 'reason'       => $e->getMessage(),
    //             ];
    //         }
    //     }

    //     return response()->json([
    //         'status'         => count($failedRows) ? 'partial' : 'success',
    //         'success_count' => $successCount,
    //         'repeat_count'  => $updateCount,
    //         'failed_count'   => count($failedRows),
    //         'errors'         => $failedRows
    //     ]);
    // }


    // public function uploadVoterData(Request $request)
    // {
    //     try {
    //         $request->validate([
    //             'voter_excel' => 'required|file|mimes:xlsx,xls,csv'
    //         ]);

    //         $file = $request->file('voter_excel');
    //         $extension = strtolower($file->getClientOriginalExtension());

    //         if ($extension === 'xls') {
    //             $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xls();
    //         } elseif ($extension === 'xlsx') {
    //             $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
    //         } elseif ($extension === 'csv') {
    //             $reader = new \PhpOffice\PhpSpreadsheet\Reader\Csv();
    //             $reader->setInputEncoding('UTF-8');
    //             $reader->setDelimiter(',');
    //         } else {
    //             throw new \Exception("Unsupported file format.");
    //         }

    //         $reader->setReadDataOnly(true);

    //         // Get available sheet names
    //         $sheetNames = $reader->listWorksheetNames($file->getPathname());

    //         if (empty($sheetNames)) {
    //             throw new \Exception("No sheets found in uploaded file.");
    //         }

    //         // Load the first available sheet
    //         $reader->setLoadSheetsOnly($sheetNames);
    //         $spreadsheet = $reader->load($file->getPathname());
    //         $sheetData = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);
    //         unset($sheetData[1]); // remove header row

    //         $successCount = 0;
    //         $updateCount = 0;
    //         $failedRows = [];

    //         foreach ($sheetData as $index => $row) {
    //             if (empty(array_filter($row, fn($value) => trim($value) !== ''))) {
    //                 continue;
    //             }

    //             DB::beginTransaction();

    //             try {
    //                 $voter_id       = $row['G'] ?? '';
    //                 $area_name      = $row['H'] ?? '';
    //                 $house          = trim($row['D'] ?? '');
    //                 $house = preg_replace('/[^\p{L}\p{N}\/\- ]+/u', '', $house);
    //                 $house = mb_substr($house, 0, 50);
    //                 $name           = $row['B'] ?? '-';
    //                 $father_name    = $row['C'] ?? '';
    //                 $age            = $row['E'] ?? '';
    //                 $gender         = $row['F'] ?? '';
    //                 $jati           = $row['I'] ?? 'N/A';
    //                 $polling_no     = $row['J'] ?? 0;
    //                 $total_family   = $row['K'] ?? 0;
    //                 $mukhiya_mobile = $row['L'] ?? '';
    //                 $death_or_left  = $row['M'] ?? '';

    //                 if (!$voter_id) throw new \Exception("Missing voter_id");
    //                 if (!$area_name) throw new \Exception("Missing area");

    //                 $area = DB::table('area_master')->where('area_name', $area_name)->first();
    //                 if (!$area) throw new \Exception("Invalid area");

    //                 $polling = DB::table('gram_polling')->where('gram_polling_id', $area->polling_id)->first();
    //                 if (!$polling) throw new \Exception("Polling info not found");

    //                 if (!empty($house) && !preg_match('/^[\p{L}0-9\x{0966}-\x{096F}\/\- ]+$/u', $house)) {
    //                     throw new \Exception("Invalid house format");
    //                 }

    //                 if (!is_numeric($polling_no)) throw new \Exception("Invalid polling number");
    //                 if (!is_numeric($total_family)) throw new \Exception("Invalid total family number");
    //                 if (!empty($mukhiya_mobile) && !preg_match('/^\d{10}$|^\d{12}$/', $mukhiya_mobile)) {
    //                     throw new \Exception("Invalid Mukhiya mobile");
    //                 }

    //                 if (!empty($age) && !is_numeric($age)) {
    //                     throw new \Exception("Invalid age format");
    //                 }

    //                 if (strlen($house) > 50) {
    //                     throw new \Exception("House exceeds max length (50)");
    //                 }

    //                 $registrationData = [
    //                     'reference_id'      => 0,
    //                     'member_id'         => 0,
    //                     'name'              => $name,
    //                     'membership'        => "N/A",
    //                     'gender'            => $gender,
    //                     'dob'               => null,
    //                     'age'               => $age,
    //                     'mobile1'           => 'N/A',
    //                     'mobile2'           => 'N/A',
    //                     'mobile1_whatsapp'  => 0,
    //                     'mobile2_whatsapp'  => 0,
    //                     'religion'          => 'N/A',
    //                     'caste'             => 'N/A',
    //                     'jati'              => $jati,
    //                     'education'         => 'N/A',
    //                     'business'          => 'N/A',
    //                     'position'          => 'N/A',
    //                     'father_name'       => $father_name,
    //                     'email'             => 'N/A',
    //                     'photo'             => 'NA',
    //                     'pincode'           => 'N/A',
    //                     'samagra_id'        => 'N/A',
    //                     'otp_recieved'      => 'NA',
    //                     'position_id'       => 0,
    //                     'date_time'         => now(),
    //                     'type'              => 1,
    //                     'voter_nature'      => 'no input',
    //                     'death_left'        => $death_or_left
    //                 ];

    //                 $existing = DB::table('registration_form')->where('voter_id', $voter_id)->first();

    //                 DB::table('registration_form')->updateOrInsert(
    //                     ['voter_id' => $voter_id],
    //                     $registrationData
    //                 );

    //                 $registrationId = DB::table('registration_form')
    //                     ->where('voter_id', $voter_id)
    //                     ->value('registration_id');

    //                 if ($existing) {
    //                     $updateCount++;
    //                 } else {
    //                     $successCount++;
    //                 }

    //                 DB::table('step2')->updateOrInsert(
    //                     ['registration_id' => $registrationId],
    //                     [
    //                         'division_id'        => 2,
    //                         'district'           => 11,
    //                         'vidhansabha'        => 49,
    //                         'mandal_type'        => 1,
    //                         'mandal'             => $polling->mandal_id,
    //                         'nagar'              => $polling->nagar_id,
    //                         'matdan_kendra_no'   => $polling_no,
    //                         'matdan_kendra_name' => $polling->polling_name,
    //                         'area_id'            => $area->area_id,
    //                         'loksabha'           => 'ग्वालियर',
    //                         'voter_front'        => 'NA',
    //                         'voter_back'         => 'NA',
    //                         'voter_number'       => $voter_id,
    //                         'house'              => $house,
    //                         'post_date'          => now(),
    //                     ]
    //                 );

    //                 DB::table('step3')->updateOrInsert(
    //                     ['registration_id' => $registrationId],
    //                     [
    //                         'total_member'       => $total_family,
    //                         'total_voter'        => 0,
    //                         'member_job'         => 0,
    //                         'member_name_1'      => 'NA',
    //                         'member_mobile_1'    => 0,
    //                         'member_name_2'      => 'NA',
    //                         'member_mobile_2'    => 0,
    //                         'friend_name_1'      => 'NA',
    //                         'friend_mobile_1'    => 0,
    //                         'friend_name_2'      => 'NA',
    //                         'friend_mobile_2'    => 0,
    //                         'intrest'            => 'NA',
    //                         'vehicle1'           => 'NA',
    //                         'vehicle2'           => 'NA',
    //                         'vehicle3'           => 'NA',
    //                         'permanent_address'  => 'NA',
    //                         'temp_address'       => 'NA',
    //                         'post_date'          => now(),
    //                         'mukhiya_mobile'     => $mukhiya_mobile
    //                     ]
    //                 );

    //                 DB::commit();
    //             } catch (\Exception $e) {
    //                 DB::rollBack();

    //                 $originalMessage = strtolower($e->getMessage());

    //                 $knownErrors = [
    //                     'missing voter_id'               => 'Voter ID is missing',
    //                     'missing area'                  => 'Area name is missing',
    //                     'invalid area'                  => 'Area not found',
    //                     'polling info not found'        => 'Polling info not found',
    //                     'house information is missing'  => 'House is required',
    //                     'invalid house format'          => 'Invalid house format',
    //                     'house exceeds max length'       => 'House exceeds max length (50)',
    //                     'invalid polling number'         => 'Polling number should be numeric',
    //                     'invalid total family number'    => 'Family count should be numeric',
    //                     'caste (jati) is required'      => 'Caste is required',
    //                     'invalid mukhiya mobile'        => 'Mukhiya mobile must be 10 or 12 digits',
    //                     'invalid age format'             => 'Age should be numeric',
    //                     'unsupported file format'        => 'Unsupported file format',
    //                     'data truncated'                 => 'Data too long for column (check "house")',
    //                     'incorrect integer value'       => 'Wrong number format',
    //                     'invalid datetime format'       => 'Invalid date format',
    //                     'duplicate voter id'            => 'Duplicate voter ID',
    //                     'unreadable file'                => 'Excel file corrupted or unreadable',
    //                 ];

    //                 $simpleReason = 'Data Processing Error';

    //                 foreach ($knownErrors as $key => $value) {
    //                     if (Str::contains($originalMessage, strtolower($key))) {
    //                         $simpleReason = $value;
    //                         break;
    //                     }
    //                 }

    //                 $failedRows[] = [
    //                     'name'           => $name,
    //                     'father_name'    => $father_name,
    //                     'house'          => $house,
    //                     'age'            => $age,
    //                     'gender'         => $gender,
    //                     'voter_id'       => $voter_id,
    //                     'area'           => $area_name,
    //                     'jati'           => $jati,
    //                     'polling_no'     => $polling_no,
    //                     'family_count'   => $total_family,
    //                     'mukhiya_mobile' => $mukhiya_mobile,
    //                     'death_left'     => $death_or_left,
    //                     'reason'         => $simpleReason,
    //                 ];
    //             }
    //         }

    //         return response()->json([
    //             'status'        => count($failedRows) ? 'partial' : 'success',
    //             'success_count' => $successCount,
    //             'repeat_count'  => $updateCount,
    //             'failed_count'  => count($failedRows),
    //             'errors'        => $failedRows
    //         ]);
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'status'  => 'error',
    //             'message' => 'Server error: ' . $e->getMessage(),
    //         ], 500);
    //     }
    // }


    public function uploadVoterData(Request $request)
    {
        try {
            //  ini_set('memory_limit', '-1');
            $request->validate([
                'voter_excel' => 'required|file|mimes:xlsx,xls,csv'
            ]);

            $file = $request->file('voter_excel');
            $extension = strtolower($file->getClientOriginalExtension());

            if ($extension === 'xls') {
                $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xls();
            } elseif ($extension === 'xlsx') {
                $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
            } elseif ($extension === 'csv') {
                $reader = new \PhpOffice\PhpSpreadsheet\Reader\Csv();
                $reader->setInputEncoding('UTF-8');
                $reader->setDelimiter(',');
            } else {
                throw new \Exception("Unsupported file format.");
            }

            $reader->setReadDataOnly(true);

            // Get available sheet names
            $spreadsheet = $reader->load($file->getPathname());
            $worksheet = $spreadsheet->getActiveSheet();
            $rowIterator = $worksheet->getRowIterator();

            $successCount = 0;
            $updateCount = 0;
            $failedRows = [];

            foreach ($rowIterator as $row) {
                $rowIndex = $row->getRowIndex();
                if ($rowIndex === 1) {
                    continue;
                }

                // Collect only non-empty cells
                $cellIterator = $row->getCellIterator();
                $cellIterator->setIterateOnlyExistingCells(true);

                $rowData = [];


                foreach ($cellIterator as $cell) {
                    $rowData[$cell->getColumn()] = trim((string)$cell->getValue());
                }

                // Skip completely empty rows
                if (empty(array_filter($rowData, fn($v) => $v !== ''))) {
                    continue;
                }

                DB::beginTransaction();

                try {
                    $voter_id       = $rowData['G'] ?? '';
                    $area_name      = $rowData['H'] ?? '';
                    $house          = trim($rowData['D'] ?? '');
                    $house          = preg_replace('/[^\p{L}\p{N}\/\- ]+/u', '', $house);
                    $house          = mb_substr($house, 0, 50);
                    $name           = $rowData['B'] ?? '-';
                    $father_name    = $rowData['C'] ?? '';
                    $age            = $rowData['E'] ?? '';
                    $gender         = $rowData['F'] ?? '';
                    $jati           = $rowData['I'] ?? 'N/A';
                    $polling_no     = $rowData['J'] ?? 0;
                    $total_family   = $rowData['K'] ?? 0;
                    $mukhiya_mobile = $rowData['L'] ?? '';
                    $death_or_left  = $rowData['M'] ?? '';

                    if (!$voter_id) throw new \Exception("Missing voter_id");
                    if (!$area_name) throw new \Exception("Missing area");

                    $area = DB::table('area_master')->where('area_name', $area_name)->first();
                    if (!$area) throw new \Exception("Invalid area");

                    $polling = DB::table('gram_polling')->where('gram_polling_id', $area->polling_id)->first();
                    if (!$polling) throw new \Exception("Polling info not found");

                    if (!empty($house) && !preg_match('/^[\p{L}0-9\x{0966}-\x{096F}\/\- ]+$/u', $house)) {
                        throw new \Exception("Invalid house format");
                    }

                    if (!is_numeric($polling_no)) throw new \Exception("Invalid polling number");
                    if (!is_numeric($total_family)) throw new \Exception("Invalid total family number");
                    if (!empty($mukhiya_mobile) && !preg_match('/^\d{10}$|^\d{12}$/', $mukhiya_mobile)) {
                        throw new \Exception("Invalid Mukhiya mobile");
                    }

                    if (!empty($age) && !is_numeric($age)) {
                        throw new \Exception("Invalid age format");
                    }

                    if (strlen($house) > 50) {
                        throw new \Exception("House exceeds max length (50)");
                    }

                    $registrationData = [
                        'reference_id'      => 0,
                        'member_id'         => 0,
                        'name'              => $name,
                        'membership'        => "N/A",
                        'gender'            => $gender,
                        'dob'               => null,
                        'age'               => $age,
                        'mobile1'           => 'N/A',
                        'mobile2'           => 'N/A',
                        'mobile1_whatsapp'  => 0,
                        'mobile2_whatsapp'  => 0,
                        'religion'          => 'N/A',
                        'caste'             => 'N/A',
                        'jati'              => $jati,
                        'education'         => 'N/A',
                        'business'          => 'N/A',
                        'position'          => 'N/A',
                        'father_name'       => $father_name,
                        'email'             => 'N/A',
                        'photo'             => 'NA',
                        'pincode'           => 'N/A',
                        'samagra_id'        => 'N/A',
                        'otp_recieved'      => 'NA',
                        // 'position_id'       => null,
                        'date_time'         => now(),
                        'type'              => 1,
                        'voter_nature'      => 'no input',
                        'death_left'        => $death_or_left
                    ];

                    $existing = DB::table('registration_form')->where('voter_id', $voter_id)->first();

                    DB::table('registration_form')->updateOrInsert(
                        ['voter_id' => $voter_id],
                        $registrationData
                    );

                    $registrationId = DB::table('registration_form')
                        ->where('voter_id', $voter_id)
                        ->value('registration_id');

                    if ($existing) {
                        $updateCount++;
                    } else {
                        $successCount++;
                    }

                    DB::table('step2')->updateOrInsert(
                        ['registration_id' => $registrationId],
                        [
                            'division_id'        => 2,
                            'district'           => 11,
                            'vidhansabha'        => 49,
                            'mandal_type'        => 1,
                            'mandal'             => $polling->mandal_id,
                            'nagar'              => $polling->nagar_id,
                            'matdan_kendra_no'   => $polling_no,
                            'matdan_kendra_name' => $polling->gram_polling_id,
                            'area_id'            => $area->area_id,
                            'loksabha'           => 'ग्वालियर',
                            'voter_front'        => 'NA',
                            'voter_back'         => 'NA',
                            'voter_number'       => $voter_id,
                            'house'              => $house,
                            'post_date'          => now(),
                        ]
                    );

                    DB::table('step3')->updateOrInsert(
                        ['registration_id' => $registrationId],
                        [
                            'total_member'       => $total_family,
                            'total_voter'        => 0,
                            'member_job'         => 0,
                            'member_name_1'      => 'NA',
                            'member_mobile_1'    => 0,
                            'member_name_2'      => 'NA',
                            'member_mobile_2'    => 0,
                            'friend_name_1'      => 'NA',
                            'friend_mobile_1'    => 0,
                            'friend_name_2'      => 'NA',
                            'friend_mobile_2'    => 0,
                            'intrest'            => 'NA',
                            'vehicle1'           => 'NA',
                            'vehicle2'           => 'NA',
                            'vehicle3'           => 'NA',
                            'permanent_address'  => 'NA',
                            'temp_address'       => 'NA',
                            'post_date'          => now(),
                            'mukhiya_mobile'     => $mukhiya_mobile
                        ]
                    );

                    DB::commit();
                } catch (\Exception $e) {
                    DB::rollBack();

                    $originalMessage = strtolower($e->getMessage());

                    $knownErrors = [
                        'missing voter_id'               => 'Voter ID is missing',
                        'missing area'                  => 'Area name is missing',
                        'invalid area'                  => 'Area not found',
                        'polling info not found'        => 'Polling info not found',
                        'house information is missing'  => 'House is required',
                        'invalid house format'          => 'Invalid house format',
                        'house exceeds max length'       => 'House exceeds max length (50)',
                        'invalid polling number'         => 'Polling number should be numeric',
                        'invalid total family number'    => 'Family count should be numeric',
                        'caste (jati) is required'      => 'Caste is required',
                        'invalid mukhiya mobile'        => 'Mukhiya mobile must be 10 or 12 digits',
                        'invalid age format'             => 'Age should be numeric',
                        'unsupported file format'        => 'Unsupported file format',
                        'data truncated'                 => 'Data too long for column (check "house")',
                        'incorrect integer value'       => 'Wrong number format',
                        'invalid datetime format'       => 'Invalid date format',
                        'duplicate voter id'            => 'Duplicate voter ID',
                        'unreadable file'                => 'Excel file corrupted or unreadable',
                    ];

                    $simpleReason = 'Data Processing Error';

                    foreach ($knownErrors as $key => $value) {
                        if (Str::contains($originalMessage, strtolower($key))) {
                            $simpleReason = $value;
                            break;
                        }
                    }

                    $failedRows[] = [
                        'name'           => $name,
                        'father_name'    => $father_name,
                        'house'          => $house,
                        'age'            => $age,
                        'gender'         => $gender,
                        'voter_id'       => $voter_id,
                        'area'           => $area_name,
                        'jati'           => $jati,
                        'polling_no'     => $polling_no,
                        'family_count'   => $total_family,
                        'mukhiya_mobile' => $mukhiya_mobile,
                        'death_left'     => $death_or_left,
                        'reason'         => $simpleReason,
                    ];
                }
            }

            return response()->json([
                'status'        => count($failedRows) ? 'partial' : 'success',
                'success_count' => $successCount,
                'repeat_count'  => $updateCount,
                'failed_count'  => count($failedRows),
                'errors'        => $failedRows
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Server error: ' . $e->getMessage(),
            ], 500);
        }
    }


    // it is correct due to some error it is change according to just above but below and just above both and both are correct 
    // public function uploadVoterData(Request $request)
    // {
    //     try {
    //         $request->validate([
    //             'voter_excel' => 'required|file|mimes:xlsx,xls'
    //         ]);

    //         $file = $request->file('voter_excel');
    //         $extension = strtolower($file->getClientOriginalExtension());

    //         if ($extension === 'xls') {
    //             $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xls();
    //         } elseif ($extension === 'xlsx') {
    //             $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
    //         } else {
    //             throw new \Exception("Unsupported file format.");
    //         }

    //         $reader->setReadDataOnly(true);
    //         $spreadsheet = $reader->load($file);
    //         $sheetData = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);

    //         unset($sheetData[1]);

    //         $successCount = 0;
    //         $updateCount = 0;
    //         $failedRows = [];

    //         foreach ($sheetData as $index => $row) {
    //             if (empty(array_filter($row, fn($value) => trim($value) !== ''))) {
    //                 continue;
    //             }
    //             DB::beginTransaction();

    //             try {
    //                 $voter_id       = $row['G'] ?? '';
    //                 $area_name      = $row['H'] ?? '';
    //                 $house          = trim($row['D'] ?? '');
    //                 $name           = $row['B'] ?? '-';
    //                 $father_name    = $row['C'] ?? '';
    //                 $age            = $row['E'] ?? '';
    //                 $gender         = $row['F'] ?? '';
    //                 $jati           = $row['I'] ?? 'N/A';
    //                 $polling_no     = $row['J'] ?? 0;
    //                 $total_family   = $row['K'] ?? 0;
    //                 $mukhiya_mobile = $row['L'] ?? '';
    //                 $death_or_left  = $row['M'] ?? '';

    //                 if (!$voter_id) throw new \Exception("Missing voter_id");
    //                 if (!$area_name) throw new \Exception("Missing area");

    //                 $area = DB::table('area_master')->where('area_name', $area_name)->first();
    //                 if (!$area) throw new \Exception("Invalid area");

    //                 $polling = DB::table('gram_polling')->where('gram_polling_id', $area->polling_id)->first();
    //                 if (!$polling) throw new \Exception("Polling info not found");

    //                 if (!empty($house) && !preg_match('/^[\p{L}0-9\x{0966}-\x{096F}\/\- ]+$/u', $house)) {
    //                     throw new \Exception("Invalid house format");
    //                 }

    //                 if (!is_numeric($polling_no)) throw new \Exception("Invalid polling number");
    //                 if (!is_numeric($total_family)) throw new \Exception("Invalid total family number");
    //                 if (!empty($mukhiya_mobile) && !preg_match('/^\d{10}$|^\d{12}$/', $mukhiya_mobile)) {
    //                     throw new \Exception("Invalid Mukhiya mobile");
    //                 }

    //                 $registrationData = [
    //                     'reference_id'      => 0,
    //                     'member_id'         => 0,
    //                     'name'              => $name,
    //                     'membership'        => "N/A",
    //                     'gender'            => $gender,
    //                     'dob'               => null,
    //                     'age'               => $age,
    //                     'mobile1'           => 'N/A',
    //                     'mobile2'           => 'N/A',
    //                     'mobile1_whatsapp'  => 0,
    //                     'mobile2_whatsapp'  => 0,
    //                     'religion'          => 'N/A',
    //                     'caste'             => 'N/A',
    //                     'jati'              => $jati,
    //                     'education'         => 'N/A',
    //                     'business'          => 'N/A',
    //                     'position'          => 'N/A',
    //                     'father_name'       => $father_name,
    //                     'email'             => 'N/A',
    //                     'photo'             => 'NA',
    //                     'pincode'           => 'N/A',
    //                     'samagra_id'        => 'N/A',
    //                     'otp_recieved'      => 'NA',
    //                     'position_id'       => 0,
    //                     'date_time'         => now(),
    //                     'type'              => 1,
    //                     'voter_nature'      => 'no input',
    //                     'death_left'        => $death_or_left
    //                 ];

    //                 $existing = DB::table('registration_form')->where('voter_id', $voter_id)->first();

    //                 DB::table('registration_form')->updateOrInsert(
    //                     ['voter_id' => $voter_id],
    //                     $registrationData
    //                 );

    //                 $registrationId = DB::table('registration_form')
    //                     ->where('voter_id', $voter_id)
    //                     ->value('registration_id');

    //                 // Count logic
    //                 if ($existing) {
    //                     $updateCount++;
    //                 } else {
    //                     $successCount++;
    //                 }

    //                 DB::table('step2')->updateOrInsert(
    //                     ['registration_id' => $registrationId],
    //                     [
    //                         'division_id'        => 2,
    //                         'district'           => 11,
    //                         'vidhansabha'        => 49,
    //                         'mandal_type'        => 1,
    //                         'mandal'             => $polling->mandal_id,
    //                         'nagar'              => $polling->nagar_id,
    //                         'matdan_kendra_no'   => $polling_no,
    //                         'matdan_kendra_name' => $polling->polling_name,
    //                         'area_id'            => $area->area_id,
    //                         'loksabha'           => 'ग्वालियर',
    //                         'voter_front'        => 'NA',
    //                         'voter_back'         => 'NA',
    //                         'voter_number'       => $voter_id,
    //                         'house'              => $house,
    //                         'post_date'          => now(),
    //                     ]
    //                 );

    //                 DB::table('step3')->updateOrInsert(
    //                     ['registration_id' => $registrationId],
    //                     [
    //                         'total_member'       => $total_family,
    //                         'total_voter'        => 0,
    //                         'member_job'         => 0,
    //                         'member_name_1'      => 'NA',
    //                         'member_mobile_1'    => 0,
    //                         'member_name_2'      => 'NA',
    //                         'member_mobile_2'    => 0,
    //                         'friend_name_1'      => 'NA',
    //                         'friend_mobile_1'    => 0,
    //                         'friend_name_2'      => 'NA',
    //                         'friend_mobile_2'    => 0,
    //                         'intrest'            => 'NA',
    //                         'vehicle1'           => 'NA',
    //                         'vehicle2'           => 'NA',
    //                         'vehicle3'           => 'NA',
    //                         'permanent_address'  => 'NA',
    //                         'temp_address'       => 'NA',
    //                         'post_date'          => now(),
    //                         'mukhiya_mobile'     => $mukhiya_mobile
    //                     ]
    //                 );


    //                 DB::commit();
    //             } catch (\Exception $e) {
    //                 DB::rollBack();


    //                 $originalMessage = strtolower($e->getMessage());

    //                 $knownErrors = [
    //                     'missing area'                  => 'Area name is missing',
    //                     'invalid area'                  => 'Area not found',
    //                     'polling info not found'        => 'Polling info not found',
    //                     'house information is missing'  => 'House is required',
    //                     'invalid house'                 => 'Invalid house format',
    //                     'caste (jati) is required'      => 'Caste is required',
    //                     'invalid polling number'        => 'Polling number should be numeric',
    //                     'invalid total family number'   => 'Family count should be numeric',
    //                     'invalid mukhiya mobile number' => 'Mukhiya mobile must be 10 or 12 digits',
    //                     'incorrect integer value'       => 'Wrong number format',
    //                     'invalid datetime format'       => 'Invalid date format',
    //                     'duplicate voter id'            => 'Duplicate voter ID'
    //                 ];

    //                 $simpleReason = 'Data Processing Error';

    //                 foreach ($knownErrors as $key => $value) {
    //                     if (Str::contains($originalMessage, strtolower($key))) {
    //                         $simpleReason = $value;
    //                         break;
    //                     }
    //                 }

    //                 // if (!$simpleReason) {
    //                 //     $simpleReason = $e->getMessage(); 
    //                 // }

    //                 $failedRows[] = [
    //                     'name'         => $name,
    //                     'father_name'  => $father_name,
    //                     'house'        => $house,
    //                     'age'          => $age,
    //                     'gender'       => $gender,
    //                     'voter_id'     => $voter_id,
    //                     'area'         => $area_name,
    //                     'jati'         => $jati,
    //                     'polling_no'   => $polling_no,
    //                     'family_count' => $total_family,
    //                     'mukhiya_mobile' => $mukhiya_mobile,
    //                     'death_left'   => $death_or_left,
    //                     'reason'       => $e->getMessage(),
    //                 ];
    //             }
    //         }

    //         return response()->json([
    //             'status'         => count($failedRows) ? 'partial' : 'success',
    //             'success_count' => $successCount,
    //             'repeat_count'  => $updateCount,
    //             'failed_count'   => count($failedRows),
    //             'errors'         => $failedRows
    //         ]);
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'status'  => 'error',
    //             'message' => 'Server error: ' . $e->getMessage(),
    //         ], 500);
    //     }
    // }

    public function votershow($id)
    {
        $registration = RegistrationForm::where('type', 1)->findOrFail($id);
        $step2 = Step2::where('registration_id', $id)->first();
        $step3 = Step3::where('registration_id', $id)->first();
        $step4 = Step4::where('registration_id', $id)->first();

        $vidhansabha = optional(VidhansabhaLokSabha::find($step2->vidhansabha ?? null))->vidhansabha ?? 'N/A';
        $mandal = optional(Mandal::find($step2->mandal ?? null))->mandal_name ?? 'N/A';
        $nagar = optional(Nagar::find($step2->nagar ?? null))->nagar_name ?? 'N/A';
        $polling = optional(Polling::find($step2->matdan_kendra_name ?? null))->polling_name ?? 'N/A';
        $area = optional(Area::find($step2->area_id ?? null))->area_name ?? 'N/A';

        $divisions = DB::table('division_master')->get();
        $district_id = $step2->district_id ?? null;
        $districts = DB::table('district_master')->get();

        $interests = isset($step3->intrest) ? explode(' ', $step3->intrest) : [];
        $interestOptions = [
            'कृषि',
            'समाजसेवा',
            'राजनीति',
            'पर्यावरण',
            'शिक्षा',
            'योग',
            'स्वास्थ्य',
            'स्वच्छता',
            'साधना'
        ];

        $allComplaints = Complaint::where('voter_id', $registration->voter_id)->get();

        $samasyavikashComplaints = $allComplaints->filter(function ($c) {
            return in_array($c->complaint_type, ['समस्या', 'विकास']);
        });
        $shubhAshubhComplaints = $allComplaints->filter(function ($c) {
            return in_array(strtolower($c->complaint_type), ['शुभ सुचना', 'अशुभ सुचना']);
        });

        $complaintReplies = Reply::whereIn('complaint_id', $allComplaints->pluck('voter_id'))
            ->get()
            ->groupBy('complaint_id');

        return view('admin.details_voter', compact(
            'registration',
            'step2',
            'step3',
            'step4',
            'vidhansabha',
            'mandal',
            'nagar',
            'polling',
            'area',
            'divisions',
            'districts',
            'district_id',
            'interests',
            'interestOptions',
            'samasyavikashComplaints',
            'shubhAshubhComplaints',
            'complaintReplies'
        ));
    }



    // view voters data functions

    public function voterListPage()
    {
        $total = DB::table('registration_form')->where('type', 1)->count();
        return view('admin/voterlist', compact('total'));
    }


    public function viewvoter(Request $request)
    {
        $start = $request->input('start', 0);
        $length = $request->input('length', 10);

        $query = DB::table('registration_form')->where('type', 1);
        if ($request->filled('voter_id')) {
            $query->where('voter_id', 'like', '%' . $request->input('voter_id') . '%');
        }

        $total = $query->count();

        $results = $query->offset($start)->limit($length)->get();

        $data = [];
        $sr = $start + 1;

        foreach ($results as $voter) {
            $step2 = DB::table('step2')->where('registration_id', $voter->registration_id)->first();
            $step3 = DB::table('step3')->where('registration_id', $voter->registration_id)->first();
            $area_name = DB::table('area_master')->where('area_id', optional($step2)->area_id)->value('area_name');

            $data[] = [
                'sr_no' => $sr++,
                'name' => $voter->name,
                'father_name' => $voter->father_name,
                'house' => $step2->house ?? '',
                'age' => $voter->age,
                'gender' => $voter->gender,
                'voter_id' => $voter->voter_id,
                'area_name' => $area_name ?? '',
                'jati' => $voter->jati,
                'matdan_kendra_no' => $step2->matdan_kendra_no ?? '',
                'total_member' => $step3->total_member ?? '',
                'mukhiya_mobile' => $step3->mukhiya_mobile ?? '',
                'death_left' => $voter->{'death/left'} ?? '',
                'date_time' => $voter->date_time ? \Carbon\Carbon::parse($voter->date_time)->format('d-m-Y') : '',
                'action' => '
                    <div class="d-flex" >
                        <a href="' . route('voter.show', $voter->registration_id) . '" class="btn btn-sm btn-success" style="margin-right: 2px">View</a>
                        <a href="' . route('voter.update', $voter->registration_id) . '" class="btn btn-sm btn-info" style="margin-right: 2px">Edit</a>
                        <form action="' . route('register.destroy', $voter->registration_id) . '" method="POST" onsubmit="return confirm(\'क्या आप वाकई रिकॉर्ड हटाना चाहते हैं?\')">
                            ' . csrf_field() . method_field('DELETE') . '
                            <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                        </form>
                    </div>
                ',
            ];
        }

        return response()->json([
            'draw' => intval($request->input('draw')),
            'recordsTotal' => $total,
            'recordsFiltered' => $total,
            'data' => $data,
        ]);
    }

    // it is working but for more data it is not working
    // public function downloadvoterFullData(Request $request)
    // {
    //     $query = DB::table('registration_form')->where('type', 1);

    //     // Apply filter if available
    //     if ($request->filled('area_id')) {
    //         $areaId = $request->input('area_id');
    //         $query->whereIn('registration_id', function ($subquery) use ($areaId) {
    //             $subquery->select('registration_id')
    //                 ->from('step2')
    //                 ->where('area_id', $areaId);
    //         });
    //     }

    //     $results = $query->get();

    //     $data = [];
    //     $sr = 1;

    //     foreach ($results as $voter) {
    //         $step2 = DB::table('step2')->where('registration_id', $voter->registration_id)->first();
    //         $step3 = DB::table('step3')->where('registration_id', $voter->registration_id)->first();
    //         $area_name = DB::table('area_master')->where('area_id', optional($step2)->area_id)->value('area_name');

    //         $data[] = [
    //             'SR No' => $sr++,
    //             'Name' => $voter->name,
    //             'Father Name' => $voter->father_name,
    //             'House' => $step2->house ?? '',
    //             'Age' => $voter->age,
    //             'Gender' => $voter->gender,
    //             'Voter ID' => $voter->voter_id,
    //             'Area Name' => $area_name ?? '',
    //             'Jati' => $voter->jati,
    //             'Matdan Kendra No' => $step2->matdan_kendra_no ?? '',
    //             'Total Member' => $step3->total_member ?? '',
    //             'Mukhiya Mobile' => $step3->mukhiya_mobile ?? '',
    //             'Death/Left' => $voter->death_left ?? '',
    //             'Date Time' => $voter->date_time ? \Carbon\Carbon::parse($voter->date_time)->format('d-m-Y') : '',
    //         ];
    //     }

    //     // Prepare CSV headers
    //     $fileName = "voterlist.csv";
    //     $headers = [
    //         "Content-type" => "text/csv",
    //         "Content-Disposition" => "attachment; filename=$fileName",
    //         "Pragma" => "no-cache",
    //         "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
    //         "Expires" => "0"
    //     ];

    //     // Return streamed CSV response
    //     $callback = function () use ($data) {
    //         $file = fopen('php://output', 'w');

    //         // Write the column headings
    //         if (!empty($data)) {
    //             fputcsv($file, array_keys($data[0]));
    //         }

    //         // Write the data
    //         foreach ($data as $row) {
    //             fputcsv($file, $row);
    //         }

    //         fclose($file);
    //     };

    //     return response()->stream($callback, 200, $headers);
    // }

    // download in excel
    // public function downloadvoterFullData(Request $request)
    // {
    //     $query = DB::table('registration_form')->where('type', 1);

    //     // Optional filters 
    //     if ($request->filled('voter_id')) {
    //         $query->where('voter_id', $request->input('voter_id'));
    //     }
    //     if ($request->filled('area_id')) {
    //         $areaId = $request->input('area_id');
    //         $query->whereIn('registration_id', function ($subquery) use ($areaId) {
    //             $subquery->select('registration_id')
    //                 ->from('step2')
    //                 ->where('area_id', $areaId);
    //         });
    //     }

    //     $fileName = "voterlist.csv";
    //     $headers = [
    //         "Content-type" => "text/csv",
    //         "Content-Disposition" => "attachment; filename=$fileName",
    //         "Pragma" => "no-cache",
    //         "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
    //         "Expires" => "0"
    //     ];

    //     $callback = function () use ($query) {
    //         $file = fopen('php://output', 'w');

    //         // Write header only once
    //         $headerWritten = false;
    //         $sr = 1;

    //         $query->orderBy('registration_id')
    //             ->chunk(1000, function ($results) use (&$headerWritten, &$sr, $file) {
    //                 foreach ($results as $voter) {
    //                     $step2 = DB::table('step2')->where('registration_id', $voter->registration_id)->first();
    //                     $step3 = DB::table('step3')->where('registration_id', $voter->registration_id)->first();
    //                     $area_name = DB::table('area_master')->where('area_id', optional($step2)->area_id)->value('area_name');

    //                     $row = [
    //                         'SR No' => $sr++,
    //                         'Name' => $voter->name ?? '',
    //                         'Father Name' => $voter->father_name ?? '',
    //                         'House' => $step2->house ?? '',
    //                         'Age' => $voter->age ?? '',
    //                         'Gender' => $voter->gender ?? '',
    //                         'Voter ID' => $voter->voter_id ?? '',
    //                         'Area Name' => $area_name ?? '',
    //                         'Jati' => $voter->jati ?? '',
    //                         'Matdan Kendra No' => $step2->matdan_kendra_no ?? '',
    //                         'Total Member' => $step3->total_member ?? '',
    //                         'Mukhiya Mobile' => $step3->mukhiya_mobile ?? '',
    //                         'Death/Left' => $voter->death_left ?? '',
    //                         'Date Time' => $voter->date_time ? \Carbon\Carbon::parse($voter->date_time)->format('d-m-Y') : '',
    //                     ];

    //                     if (!$headerWritten) {
    //                         fputcsv($file, array_keys($row));
    //                         $headerWritten = true;
    //                     }

    //                     fputcsv($file, $row);
    //                 }
    //             });

    //         fclose($file);
    //     };

    //     return response()->stream($callback, 200, $headers);
    // }


    public function requestDownload(Request $request)
    {
        $filters = $request->only(['voter_id', 'area_id']);
        $userId = session('user_id');


        // Job dispatch
        \App\Jobs\GenerateVoterListJob::dispatch($filters, $userId);

        return response()->json([
            'status' => 'success',
            'message' => 'डेटा background में डाउनलोड हो रहा है। तैयार होने पर link मिल जाएगा।'
        ]);
    }

    public function downloadList()
    {
        $files = DB::table('downloads')
            ->where('user_id', session('user_id'))
            ->orderByDesc('created_at')
            ->get();

        return view('admin.downloads', compact('files'));
    }

    public function downloadFile($id)
    {
        $file = DB::table('downloads')->where('id', $id)->where('user_id', session('user_id'))->first();

        if (!$file || !Storage::exists($file->file_path)) {
            abort(404);
        }

        return Storage::download($file->file_path, $file->file_name);
    }

    public function downloadFilesJson()
    {
        $files = DB::table('downloads')
            ->where('user_id', session('user_id'))
            ->orderByDesc('created_at')
            ->get();

        return response()->json($files);
    }


    // download in zip
    // public function downloadvoterFullData(Request $request)
    // {
    //     $voterId = $request->voter_id;
    //     $fileName = 'voterlist.zip';
    //     $filePath = storage_path('app/public/' . $fileName);

    //     // Remove old file if exists
    //     if (file_exists($filePath)) {
    //         unlink($filePath);
    //     }

    //     // Temporary CSV file
    //     $csvTempPath = storage_path('app/public/voterlist.csv');
    //     if (file_exists($csvTempPath)) {
    //         unlink($csvTempPath);
    //     }

    //     $handle = fopen($csvTempPath, 'w');
    //     // Write CSV header
    //     fputcsv($handle, [
    //         'Sr No',
    //         'Name',
    //         'Father Name',
    //         'House',
    //         'Age',
    //         'Gender',
    //         'Voter ID',
    //         'Area Name',
    //         'Jati',
    //         'Matdan Kendra No',
    //         'Total Member',
    //         'Mukhiya Mobile',
    //         'Death/Left',
    //         'Date Time'
    //     ]);

    //     $sr = 1;

    //     DB::table('registration_form')
    //         ->where('type', 1)
    //         ->when($request->filled('voter_id'), fn($q) => $q->where('voter_id', 'like', '%' . $voterId . '%'))
    //         ->orderBy('registration_id')
    //         ->chunkById(500, function ($voters) use (&$sr, $handle) {
    //             foreach ($voters as $voter) {
    //                 $step2 = DB::table('step2')->where('registration_id', $voter->registration_id)->first();
    //                 $step3 = DB::table('step3')->where('registration_id', $voter->registration_id)->first();
    //                 $area_name = DB::table('area_master')->where('area_id', optional($step2)->area_id)->value('area_name');

    //                 fputcsv($handle, [
    //                     $sr++,
    //                     $voter->name,
    //                     $voter->father_name,
    //                     $step2->house ?? '',
    //                     $voter->age,
    //                     $voter->gender,
    //                     $voter->voter_id,
    //                     $area_name ?? '',
    //                     $voter->jati,
    //                     $step2->matdan_kendra_no ?? '',
    //                     $step3->total_member ?? '',
    //                     $step3->mukhiya_mobile ?? '',
    //                     $voter->{'death/left'} ?? '',
    //                     $voter->date_time ? \Carbon\Carbon::parse($voter->date_time)->format('d-m-Y') : '',
    //                 ]);
    //             }
    //         }, 'registration_id');

    //     fclose($handle);

    //     // Create ZIP
    //     $zip = new \ZipArchive();
    //     $zip->open($filePath, \ZipArchive::CREATE);
    //     $zip->addFile($csvTempPath, 'voterlist.csv');
    //     $zip->close();

    //     // Remove temporary CSV
    //     if (file_exists($csvTempPath)) {
    //         unlink($csvTempPath);
    //     }

    //     return response()->download($filePath)->deleteFileAfterSend(true);
    // }




    // public function voterdata(Request $request)
    // {
    //     $query = DB::table('registration_form')->where('type', 1);

    //     if ($request->filled('voter_id')) {
    //         $query->where('voter_id', 'like', '%' . $request->voter_id . '%');
    //     }

    //     $voters = $query->get()->map(function ($voter) {
    //         //$voter->age = $voter->dob ? \Carbon\Carbon::parse($voter->dob)->age : 'N/A';
    //         return $voter;
    //     });

    //     $count = $voters->count();
    //     $tableRows = '';
    //     $i = 1;

    //     foreach ($voters as $voter) {
    //         $step2 = DB::table('step2')->where('registration_id', $voter->registration_id)->first();
    //         $step3 = DB::table('step3')->where('registration_id', $voter->registration_id)->first();

    //         $area_name = DB::table('area_master')->where('area_id', $step2->area_id)->first();
    //         $tableRows .= '
    //         <tr>
    //             <td>' . $i++ . '</td>
    //             <td>' . $voter->name . '</td>
    // 			<td>' . $voter->father_name . '</td>
    //             <td>' . $step2->house . '</td>
    //             <td>' . $voter->age . '</td>
    //             <td>' . $voter->gender . '</td>
    // 			<td>' . $voter->voter_id . '</td>
    // 			<td>' . $area_name->area_name . '</td>
    //             <td>' . $voter->jati . '</td>
    //             <td>' . $step2->matdan_kendra_no . '</td>
    //             <td>' . $step3->total_member . '</td>
    //             <td>' . $step3->mukhiya_mobile . '</td>
    //             <td>' . $voter->death_left . '</td>
    //             <td>' . \Carbon\Carbon::parse($voter->date_time)->format('d-m-Y') . '</td>
    //             <td style="white-space: nowrap;">
    //                 <a href="' . route('voter.show', $voter->registration_id) . '" class="btn btn-xs btn-success">View</a>
    //                 <a href="' . route('voter.update', $voter->registration_id) . '" class="btn btn-xs btn-info">Edit</a>

    //                 <form action="' . route('register.destroy', $voter->registration_id) . '" method="POST" style="display:inline-block;" onsubmit="return confirm(\'क्या आप वाकई रिकॉर्ड हटाना चाहते हैं?\')">
    //                     ' . csrf_field() . '
    //                     ' . method_field('DELETE') . '
    //                     <button type="submit" class="btn btn-xs btn-danger">Delete</button>
    //                 </form>
    //             </td>
    //                         </tr>';
    //     }

    //     return response()->json([
    //         'count' => $count,
    //         'table_rows' => $tableRows
    //     ]);
    // }

    public function voterUpdate($id)
    {
        $registration = DB::table('registration_form')->where('registration_id', $id)->first();
        $step2 = DB::table('step2')->where('registration_id', $id)->first();
        $step3 = DB::table('step3')->where('registration_id', $id)->first();

        $divisions = DB::table('division_master')->get();
        $districts = DB::table('district_master')->get();
        $polling = DB::table('gram_polling')->where('gram_polling_id', $step2->matdan_kendra_no ?? 0)->first();

        return view('admin/update_voters', [
            'registration' => $registration,
            'step2' => $step2,
            'step3' => $step3,
            'divisions' => $divisions,
            'districts' => $districts,
            'polling' => $polling->gram_polling_id ?? '',
            'mandal' => $polling->mandal_id ?? '',
            'nagar' => $polling->nagar_id ?? '',
            'vidhansabha' => $step2->vidhansabha ?? '',
            'area' => DB::table('area_master')->where('area_id', $step2->area_id ?? 0)->value('area_name')
        ]);
    }

    public function voterUpdatePost(Request $request, $id)
    {
        $request->validate([
            'name' => 'required',
            'father_name' => 'required',
            'age' => 'nullable|numeric',
            'gender' => 'required|in:पुरुष,स्त्री,अन्य',
            'voter_number' => 'required'
        ]);

        DB::beginTransaction();
        try {
            DB::table('registration_form')->where('registration_id', $id)->update([
                'name' => $request->name,
                'father_name' => $request->father_name,
                'gender' => $request->gender,
                'age' => $request->age,
                'mobile1' => $request->mobile1,
                'mobile2' => $request->mobile2,
                'jati' => $request->jati,
                'business' => $request->business,
                'education' => $request->education,
                'religion' => $request->religion,
                'caste' => $request->caste,
                'voter_id' => $request->voter_number,
                'voter_nature' => $request->voter_nature ?? 'no input',
            ]);

            DB::table('step2')->updateOrInsert(
                ['registration_id' => $id],
                [
                    'division_id' => $request->division_name,
                    'district' => $request->district,
                    'vidhansabha' => $request->vidhansabha,
                    'loksabha' => $request->loksabha,
                    'mandal' => $request->mandal,
                    'nagar' => $request->nagar,
                    'matdan_kendra_name' => $request->matdan_kendra_name,
                    'matdan_kendra_no' => 0,
                    'polling_area' => $request->polling_area,
                    'voter_number' => $request->voter_number,
                ]
            );

            DB::table('step3')->updateOrInsert(
                ['registration_id' => $id],
                [
                    'total_member' => $request->total_member ?? 0,
                    'total_voter' => $request->total_voter ?? 0,
                ]
            );

            DB::commit();
            return response()->json(['status' => 'success', 'message' => 'Voter updated successfully.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }



    // member form functions
    public function membercreate()
    {
        $divisions = Division::all();
        $jatis = Jati::all();
        $categories = Category::all();
        $religions = Religion::all();
        $educations = Education::all();
        $businesses = Business::all();
        $politics = Politics::all();
        $interests = Interest::all();
        return view('admin/membership_form', compact('divisions', 'jatis', 'categories', 'religions', 'educations', 'businesses', 'politics', 'interests'));
    }

    public function getDistricts(Request $request)
    {
        $division_id = $request->division_id;

        $districts = District::where('division_id', $division_id)->get();

        return response()->json($districts);
    }

    public function memberstore(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'membership' => 'required|string',
            'mobile_1' => 'required|digits:10',
            'file' => 'required|image|max:2048',
            'voter_front' => 'required|image|max:2048',
            'voter_back' => 'required|image|max:2048',
        ]);

        DB::beginTransaction();

        try {
            $registration = new RegistrationForm();
            $registration->reference_id = $request->reference_id ?? 0;
            $registration->member_id = $request->mobile_1;
            $registration->name = $request->name;
            $registration->membership = $request->membership;
            $registration->gender = $request->gender;
            $registration->dob = Carbon::createFromFormat('Y-m-d', "{$request->date}");
            $registration->age = $request->age;
            $registration->mobile1 = $request->mobile_1;
            $registration->mobile2 = $request->mobile_2;
            $registration->mobile1_whatsapp = $request->input('mobile_1_whatsapp') == '1' ? 1 : 0;
            $registration->mobile2_whatsapp = $request->input('mobile_2_whatsapp') == '1' ? 1 : 0;
            $registration->religion = $request->religion;
            $registration->caste = $request->caste;
            $registration->jati = $request->jati;
            $registration->education = $request->education;
            $registration->business = $request->business;
            // $registration->position_id = $request->position_id ?? null;
            $registration->position = $request->position ?? '';
            $registration->father_name = $request->father_name;
            $registration->email = $request->email;
            $registration->pincode = $request->pincode;
            $registration->samagra_id = $request->samagra_id ?? '';
            $registration->otp_recieved = 0;
            $registration->date_time = now();
            $registration->type = 2;
            $registration->voter_id = $request->voter_id ?? '';
            $registration->pincode = '';

            // Upload photo
            if ($request->hasFile('file')) {
                $photoName = 'photo_' . time() . '.' . $request->file->extension();
                $request->file->move(public_path('assets/upload'), $photoName);
                $registration->photo = $photoName;
            }

            $registration->save();
            $registration_id = $registration->registration_id;

            // 2. Step 2
            $step2 = new Step2();
            $step2->registration_id = $registration_id;
            $step2->division_id = $request->division_name;
            $step2->district = $request->district;
            $step2->vidhansabha = $request->vidhansabha;
            $step2->mandal_type = $request->mandal_type;
            $step2->mandal = $request->mandal;
            $step2->nagar = $request->nagar;
            $step2->matdan_kendra_no = $request->matdan_kendra_no ?? 0;
            $step2->matdan_kendra_name = $request->matdan_kendra_name;
            $step2->area_id = $request->area_name;
            $step2->loksabha = $request->loksabha;
            $step2->voter_number = $request->voter_id ?? '';
            $step2->house = $request->house ?? '';
            $step2->post_date = now();

            if ($request->hasFile('voter_front')) {
                $frontName = 'front_' . time() . '.' . $request->voter_front->extension();
                $request->voter_front->move(public_path('assets/upload/step2'), $frontName);
                $step2->voter_front = $frontName ?? '';
            }

            if ($request->hasFile('voter_back')) {
                $backName = 'back_' . time() . '.' . $request->voter_back->extension();
                $request->voter_back->move(public_path('assets/upload/step2'), $backName);
                $step2->voter_back = $backName ?? '';
            }

            $step2->save();

            // 3. Step 3
            $step3 = new Step3();
            $step3->registration_id = $registration_id;
            $step3->total_member = $request->total_member;
            $step3->total_voter = $request->total_voter;
            $step3->member_job = $request->member_job ?? '';
            $step3->member_name_1 = $request->member_name_1 ?? '';
            $step3->member_mobile_1 = $request->member_mobile_1 ?? '';
            $step3->member_name_2 = $request->member_name_2 ?? '';
            $step3->member_mobile_2 = $request->member_mobile_2 ?? '';
            $step3->friend_name_1 = $request->friend_name_1 ?? '';
            $step3->friend_mobile_1 = $request->friend_mobile_1 ?? '';
            $step3->friend_name_2 = $request->friend_name_2 ?? '';
            $step3->friend_mobile_2 = $request->friend_mobile_2 ?? '';
            $step3->intrest = is_array($request->interest) ? implode(',', $request->interest) : '';
            $step3->vehicle1 = $request->vehicle1 ?? '';
            $step3->vehicle2 = $request->vehicle2 ?? '';
            $step3->vehicle3 = $request->vehicle3 ?? '';
            $step3->mukhiya_mobile = $request->mukhiya_mobile ?? '';
            $step3->permanent_address = $request->permanent_address ?? '';
            $step3->temp_address = $request->temp_address ?? '';
            $step3->post_date = now();
            $step3->save();

            // 4. Step 4
            $step4 = new Step4();
            $step4->registration_id = $registration_id;
            $step4->party_name = $request->party_name ?? '';
            $step4->present_post = $request->present_post ?? '';
            $step4->reason_join = $request->reason_join ?? '';
            $step4->post_date = now();
            $step4->save();

            DB::commit();
            // return redirect()->route('register.card', ['id' => $registration_id]);
            return redirect()
                ->route('membership.create')
                ->with('success', 'सदस्यता सफलतापूर्वक सबमिट की गई!');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->withErrors(['error' => $e->getMessage()]);
        }

        // return redirect()->route('membership.create')->with('success', 'सदस्यता सफलतापूर्वक सबमिट की गई!');
    }

    public function memberedit($id)
    {
        $registration = RegistrationForm::with([
            'step2',
            'step3',
            'step4',
            'step2.vidhansabhaRelation',
            'step2.division',
            'step2.districtRelation',
            'step2.mandalRelation',
            'step2.polling',
            'step2.areaRelation',
            'step2.nagarRelation',
        ])->findOrFail($id);
        $divisions = Division::all();
        $jatis = Jati::all();
        $categories = Category::all();
        $religions = Religion::all();
        $educations = Education::all();
        $businesses = Business::all();
        $politics = Politics::all();
        $interests = Interest::all();
        return view('admin/edit_membership', compact('registration', 'divisions', 'jatis', 'categories', 'religions', 'educations', 'businesses', 'politics', 'interests'));
    }

    public function memberupdate(Request $request, $id)
    {
        // dd($request->all());
        $request->validate([
            'name' => 'required|string',
            'membership' => 'required|string',
            'mobile_1' => 'required|digits:10',
            'file' => 'nullable|image|max:2048',
            'voter_front' => 'nullable|image|max:2048',
            'voter_back' => 'nullable|image|max:2048',
        ]);

        DB::beginTransaction();

        try {
            // 1. Update Step1
            $registration = RegistrationForm::findOrFail($id);
            $registration->reference_id = $request->reference_id ?? 0;
            $registration->member_id = $request->mobile_1;
            $registration->name = $request->name;
            $registration->membership = $request->membership;
            $registration->gender = $request->gender;
            $registration->dob = Carbon::createFromFormat('Y-m-d', "{$request->date}");
            $registration->age = $request->age;
            $registration->mobile1 = $request->mobile_1;
            $registration->mobile2 = $request->mobile_2;
            $registration->mobile1_whatsapp = $request->input('mobile_1_whatsapp') == '1' ? 1 : 0;
            $registration->mobile2_whatsapp = $request->input('mobile_2_whatsapp') == '1' ? 1 : 0;
            $registration->religion = $request->religion;
            $registration->caste = $request->caste;
            $registration->jati = $request->jati;
            $registration->education = $request->education;
            $registration->business = $request->business;
            // $registration->position_id = $request->position_id ?? null;
            $registration->position = $request->position ?? '';
            $registration->father_name = $request->father_name;
            $registration->email = $request->email;
            $registration->pincode = $request->pincode ?? 0;
            $registration->samagra_id = $request->samagra_id ?? '';
            $registration->voter_id = $request->voter_id ?? '';

            // Upload photo if new one provided
            if ($request->hasFile('file')) {
                $photoName = 'photo_' . time() . '.' . $request->file->extension();
                $request->file->move(public_path('assets/upload'), $photoName);
                $registration->photo = $photoName;
            }
            $registration->save();

            // 2. Step2 update
            $step2 = Step2::where('registration_id', $id)->first() ?? new Step2();
            $step2->registration_id = $id;
            $step2->division_id = $request->division_name;
            $step2->district = $request->district;
            $step2->vidhansabha = $request->vidhansabha;
            $step2->mandal_type = $request->mandal_type;
            $step2->mandal = $request->mandal;
            $step2->nagar = $request->nagar;
            $step2->matdan_kendra_no = $request->matdan_kendra_no ?? 0;
            $step2->matdan_kendra_name = $request->matdan_kendra_name;
            $step2->area_id = $request->area_name;
            $step2->loksabha = $request->loksabha;
            $step2->voter_number = $request->voter_id ?? '';
            $step2->house = $request->house ?? '';

            if ($request->hasFile('voter_front')) {
                $frontName = 'front_' . time() . '.' . $request->voter_front->extension();
                $request->voter_front->move(public_path('assets/upload/step2'), $frontName);
                $step2->voter_front = $frontName;
            }

            if ($request->hasFile('voter_back')) {
                $backName = 'back_' . time() . '.' . $request->voter_back->extension();
                $request->voter_back->move(public_path('assets/upload/step2'), $backName);
                $step2->voter_back = $backName;
            }
            $step2->save();

            // 3. Step3 update
            $step3 = Step3::where('registration_id', $id)->first() ?? new Step3();
            $step3->registration_id = $id;
            $step3->total_member = $request->total_member;
            $step3->total_voter = $request->total_voter;
            $step3->member_job = $request->member_job ?? '';
            $step3->member_name_1 = $request->member_name_1 ?? '';
            $step3->member_mobile_1 = $request->member_mobile_1 ?? '';
            $step3->member_name_2 = $request->member_name_2 ?? '';
            $step3->member_mobile_2 = $request->member_mobile_2 ?? '';
            $step3->friend_name_1 = $request->friend_name_1 ?? '';
            $step3->friend_mobile_1 = $request->friend_mobile_1 ?? '';
            $step3->friend_name_2 = $request->friend_name_2 ?? '';
            $step3->friend_mobile_2 = $request->friend_mobile_2 ?? '';
            $step3->intrest = is_array($request->interest) ? implode(',', $request->interest) : '';
            $step3->vehicle1 = $request->vehicle1 ?? '';
            $step3->vehicle2 = $request->vehicle2 ?? '';
            $step3->vehicle3 = $request->vehicle3 ?? '';
            $step3->mukhiya_mobile = $request->mukhiya_mobile ?? '';
            $step3->permanent_address = $request->permanent_address ?? '';
            $step3->temp_address = $request->temp_address ?? '';
            $step3->save();
            // 4. Step4 update
            $step4 = Step4::where('registration_id', $id)->first() ?? new Step4();
            $step4->registration_id = $id;
            $step4->party_name = $request->party_name ?? '';
            $step4->present_post = $request->present_post ?? '';
            $step4->reason_join = $request->reason_join ?? '';
            $step4->save();

            DB::commit();
            return redirect()
                ->route('membership.edit', $id)
                ->with('success', 'सदस्यता सफलतापूर्वक अपडेट की गई!');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }



    public function getSubjects($department_id)
    {
        $subjects = Subject::where('department_id', $department_id)->get(['subject_id', 'subject']);
        return response()->json($subjects);
    }


    public function districtsfetch($division_id)
    {
        $districts = District::where('division_id', $division_id)->get();
        return response()->json($districts->map(function ($d) {
            return "<option value='{$d->district_id}'>{$d->district_name}</option>";
        }));
    }

    public function vidhansabhafetch($district_id)
    {
        $vidhansabhas = VidhansabhaLokSabha::where('district_id', $district_id)->get();
        return response()->json($vidhansabhas->map(function ($v) {
            return "<option value='{$v->vidhansabha_id}'>{$v->vidhansabha}</option>";
        }));
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

    public function getgramPollings($nagar_id)
    {
        $pollings = Polling::where('nagar_id', $nagar_id)->get([
            'gram_polling_id',
            'polling_name',
            'polling_no'
        ]);

        return response()->json($pollings);
    }


    public function getPollingAndArea($nagarId)
    {
        $pollings = Polling::with('area')
            ->where('nagar_id', $nagarId)
            ->get(['gram_polling_id', 'polling_name', 'polling_no']);

        return response()->json($pollings);
    }

    public function summary($id)
    {
        $complaint = Complaint::with(['replies.followups'])->findOrFail($id);

        $complaint->replies = $complaint->replies->sortByDesc('reply_date');

        $complaint->replies->each(function ($reply) {
            $reply->followups = $reply->followups->sortByDesc('followup_date');
        });

        $totalReplies = $complaint->replies->count();

        $totalFollowups = $complaint->replies->reduce(function ($carry, $reply) {
            return $carry + $reply->followups->count();
        }, 0);

        return view('admin/details_summary', compact('complaint', 'totalReplies', 'totalFollowups'));
    }




    // detailed report routes
    // public function detailed_report_index(Request $request)
    // {
    //     $managers = User::where('role', 2)->get();
    //     $departments = Department::all();
    //     $areas = Area::all();
    //     $jatis = Jati::all();
    //     $divisions = Division::all();

    //     // Defaults (used only when no request input)
    //     $divisionId = $request->get('division_id', 2);
    //     $districtId = $request->get('district_id', 11);
    //     $vidhansabhaId = $request->get('vidhansabha_id', 49);
    //     $gramId = $request->get('gram_id');

    //     // Dropdown data
    //     $districts = District::where('division_id', $divisionId)->get();
    //     $vidhansabhas = VidhansabhaLokSabha::where('district_id', $districtId)->get();
    //     $mandalIds = Mandal::whereIn('vidhansabha_id', $vidhansabhas->pluck('vidhansabha_id'))->pluck('mandal_id');
    //     $nagars = Nagar::with('mandal')
    //         ->whereIn('mandal_id', $mandalIds)
    //         ->orderBy('nagar_name')
    //         ->get();

    //     $query = Complaint::query();
    //     $query->whereIn('complaint_type', ['समस्या', 'विकास']);

    //     // Date filters
    //     if ($request->filled('from_date')) {
    //         $query->whereDate('posted_date', '>=', $request->from_date);
    //     }
    //     if ($request->filled('to_date')) {
    //         $query->whereDate('posted_date', '<=', $request->to_date);
    //     }

    //     if ($request->filled('office_type')) {
    //         $query->where('type', $request->office_type);
    //     }

    //     $filteredComplaints = $query->get();

    //     $summaryType = $request->get('summary', 'jati');

    //     if ($summaryType === 'all' && !$request->has('all_filter')) {
    //         $summaryType = 'all_area';
    //     } elseif ($summaryType === 'all' && $request->has('all_filter')) {
    //         $summaryType = $request->all_filter; 
    //     }

    //     $departmentCounts = collect();
    //     $areaCounts = collect();
    //     $jatiCounts = collect();

    //     if ($summaryType == 'all_department') {
    //         $departmentCounts = $departments->map(function ($department) use ($filteredComplaints) {
    //             return [
    //                 'department' => $department->department_name,
    //                 'count' => $filteredComplaints->where('complaint_department', $department->department_name)->count(),
    //             ];
    //         });

    //         $anyaCount = $filteredComplaints->where('complaint_department', 'अन्य')->count();
    //         if ($anyaCount > 0 && !$departments->contains('department_name', 'अन्य')) {
    //             $departmentCounts->push([
    //                 'department' => 'अन्य',
    //                 'count' => $anyaCount,
    //             ]);
    //         }

    //         $departmentCounts = $departmentCounts
    //             ->sortByDesc('count')
    //             ->values();
    //     } elseif ($summaryType == 'all_jati') {
    //         $jatiCounts = $jatis->map(function ($jati) use ($filteredComplaints) {
    //             return [
    //                 'jati' => $jati->jati_name,
    //                 'count' => $filteredComplaints->where('jati_id', $jati->jati_id)->count()
    //             ];
    //         })->sortByDesc('count')->values();
    //     } elseif ($summaryType == 'all_area') {
    //         $areaCounts = $areas->map(function ($area) use ($filteredComplaints) {
    //             return [
    //                 'area' => $area->area_name,
    //                 'count' => $filteredComplaints->where('area_id', $area->area_id)->count()
    //             ];
    //         })->sortByDesc('count')->values();
    //     } elseif ($summaryType == 'department') {
    //         $departmentCounts = $departments->map(function ($department) use ($filteredComplaints) {
    //             $count = $filteredComplaints->where('complaint_department', $department->department_name)->count();
    //             return ['department' => $department->department_name, 'count' => $count];
    //         });

    //         $anyaCount = $filteredComplaints->where('complaint_department', 'अन्य')->count();
    //         if ($anyaCount > 0 && !$departments->contains('department_name', 'अन्य')) {
    //             $departmentCounts->push([
    //                 'department' => 'अन्य',
    //                 'count' => $anyaCount
    //             ]);
    //         }

    //         // अब filter करके sort करो
    //         $departmentCounts = $departmentCounts
    //             ->filter(fn($d) => $d['count'] > 0)
    //             ->sortByDesc('count')
    //             ->values();
    //     } elseif ($summaryType == 'area') {
    //         // Area filters ONLY here
    //         $areaQuery = Complaint::query();

    //         // Apply global filters again
    //         if ($request->filled('from_date')) {
    //             $areaQuery->whereDate('posted_date', '>=', $request->from_date);
    //         }
    //         if ($request->filled('to_date')) {
    //             $areaQuery->whereDate('posted_date', '<=', $request->to_date);
    //         }
    //         if ($request->filled('office_type')) {
    //             $areaQuery->where('type', $request->office_type);
    //         }

    //         // Apply area filters
    //         if ($divisionId) $areaQuery->where('division_id', $divisionId);
    //         if ($districtId) $areaQuery->where('district_id', $districtId);
    //         if ($vidhansabhaId) $areaQuery->where('vidhansabha_id', $vidhansabhaId);
    //         if ($gramId) $areaQuery->where('gram_id', $gramId);

    //         $areaCounts = $areaQuery
    //             ->select('area_id')
    //             ->selectRaw('count(*) as count')
    //             ->groupBy('area_id')
    //             ->get()
    //             ->map(function ($row) use ($areas) {
    //                 $areaName = $areas->firstWhere('area_id', $row->area_id)?->area_name ?? 'Unknown';
    //                 return ['area' => $areaName, 'count' => $row->count];
    //             })->sortByDesc('count')
    //             ->values();
    //     } elseif ($summaryType == 'jati') {
    //         $jatiCounts = $jatis->map(function ($jati) use ($filteredComplaints) {
    //             $count = $filteredComplaints->where('jati_id', $jati->jati_id)->count();
    //             return ['jati' => $jati->jati_name, 'count' => $count];
    //         })->filter(fn($j) => $j['count'] > 0)->sortByDesc('count')
    //             ->values();
    //     }

    //     $hasFilter = $request->filled('from_date')
    //         || $request->filled('to_date')
    //         || $request->filled('admin_id')
    //         || $request->filled('office_type')
    //         || $request->filled('division_id')
    //         || $request->filled('district_id')
    //         || $request->filled('vidhansabha_id')
    //         || $request->filled('gram_id');

    //     return view('admin.detailed_report', compact(
    //         'managers',
    //         'filteredComplaints',
    //         'departmentCounts',
    //         'areaCounts',
    //         'jatiCounts',
    //         'hasFilter',
    //         'departments',
    //         'areas',
    //         'jatis',
    //         'summaryType',
    //         'divisions',
    //         'districts',
    //         'vidhansabhas',
    //         'nagars',
    //         'divisionId',
    //         'districtId',
    //         'vidhansabhaId',
    //         'gramId'
    //     ));
    // }


    public function detailed_report_index(Request $request)
    {
        $managers = User::where('role', 2)->get();
        $departments = Department::all();
        $jatis = Jati::all();
        $divisions = Division::all();

        // Request से filter values
        $divisionId = $request->get('division_id');
        $districtId = $request->get('district_id');
        $vidhansabhaId = $request->get('vidhansabha_id');
        $gramId = $request->get('gram_id');

        // Dropdown data
        $districts = $divisionId ? District::where('division_id', $divisionId)->get() : collect();
        $vidhansabhas = $districtId ? VidhansabhaLokSabha::where('district_id', $districtId)->get() : collect();
        $mandalIds = $vidhansabhas->pluck('vidhansabha_id')->isNotEmpty()
            ? Mandal::whereIn('vidhansabha_id', $vidhansabhas->pluck('vidhansabha_id'))->pluck('mandal_id')
            : collect();
        $nagars = $mandalIds->isNotEmpty()
            ? Nagar::with('mandal')->whereIn('mandal_id', $mandalIds)->orderBy('nagar_name')->get()
            : collect();

        // Complaint query
        $query = Complaint::query();
        $query->whereIn('complaint_type', ['समस्या', 'विकास']);

        if ($request->filled('from_date')) $query->whereDate('posted_date', '>=', $request->from_date);
        if ($request->filled('to_date')) $query->whereDate('posted_date', '<=', $request->to_date);
        if ($request->filled('office_type')) $query->where('type', $request->office_type);

        $filteredComplaints = $query->get();

        $summaryType = $request->get('summary', 'jati');
        if ($summaryType === 'all' && !$request->has('all_filter')) $summaryType = 'all_area';
        elseif ($summaryType === 'all' && $request->has('all_filter')) $summaryType = $request->all_filter;

        $departmentCounts = collect();
        $areaCounts = collect();
        $jatiCounts = collect();

        // Department summary
        if (in_array($summaryType, ['all_department', 'department'])) {
            $departmentCounts = $departments->map(function ($department) use ($filteredComplaints) {
                $count = $filteredComplaints->where('complaint_department', $department->department_name)->count();
                return ['department' => $department->department_name, 'count' => $count];
            });

            $anyaCount = $filteredComplaints->where('complaint_department', 'अन्य')->count();
            if ($anyaCount > 0 && !$departments->contains('department_name', 'अन्य')) {
                $departmentCounts->push(['department' => 'अन्य', 'count' => $anyaCount]);
            }

            // For all_department, keep zeros
            if ($summaryType === 'department') {
                $departmentCounts = $departmentCounts->filter(fn($d) => $d['count'] > 0);
            }

            $departmentCounts = $departmentCounts->sortByDesc('count')->values();
        }

        // Jati summary
        if (in_array($summaryType, ['all_jati', 'jati'])) {
            $jatiCounts = $jatis->map(function ($jati) use ($filteredComplaints) {
                $count = $filteredComplaints->where('jati_id', $jati->jati_id)->count();
                return ['jati' => $jati->jati_name, 'count' => $count];
            });

            // For all_jati, do NOT filter zeros
            if ($summaryType === 'jati') {
                $jatiCounts = $jatiCounts->filter(fn($j) => $j['count'] > 0);
            }

            $jatiCounts = $jatiCounts->sortByDesc('count')->values();
        }

        // Area summary
        if (in_array($summaryType, ['all_area', 'area'])) {
            $areaQuery = Complaint::query();
            $areaQuery->whereIn('complaint_type', ['समस्या', 'विकास']);
            if ($request->filled('from_date')) $areaQuery->whereDate('posted_date', '>=', $request->from_date);
            if ($request->filled('to_date')) $areaQuery->whereDate('posted_date', '<=', $request->to_date);
            if ($request->filled('office_type')) $areaQuery->where('type', $request->office_type);

            // Apply filters
            if ($divisionId) $areaQuery->where('division_id', $divisionId);
            if ($districtId) $areaQuery->where('district_id', $districtId);
            if ($vidhansabhaId) $areaQuery->where('vidhansabha_id', $vidhansabhaId);
            if ($gramId) $areaQuery->where('gram_id', $gramId);

            // Get all areas as collection
            $allAreas = Area::all();

            if ($summaryType === 'all_area') {
                $areaCounts = $allAreas->map(function ($area) use ($areaQuery) {
                    $count = (clone $areaQuery)->where('area_id', $area->area_id)->count();
                    return ['area' => $area->area_name, 'count' => $count];
                })->sortByDesc('count')->values();
            } else {
                $areaCounts = $allAreas->map(function ($area) use ($areaQuery) {
                    $count = (clone $areaQuery)->where('area_id', $area->area_id)->count();
                    return ['area' => $area->area_name, 'count' => $count];
                })->filter(fn($a) => $a['count'] > 0)->sortByDesc('count')->values();
            }
        }

        $hasFilter = $request->filled('from_date')
            || $request->filled('to_date')
            || $request->filled('admin_id')
            || $request->filled('office_type')
            || $request->filled('division_id')
            || $request->filled('district_id')
            || $request->filled('vidhansabha_id')
            || $request->filled('gram_id');

        return view('admin.detailed_report', compact(
            'managers',
            'filteredComplaints',
            'departmentCounts',
            'areaCounts',
            'jatiCounts',
            'hasFilter',
            'departments',
            'jatis',
            'summaryType',
            'divisions',
            'districts',
            'vidhansabhas',
            'nagars',
            'divisionId',
            'districtId',
            'vidhansabhaId',
            'gramId'
        ));
    }



    // jati wise area wise and department wise reports 
    public function jatiwise_report(Request $request)
    {
        $fromDate = $request->from_date;
        $toDate = $request->to_date;
        $officeType = $request->office_type ?? '2';
        $showAll = $request->input('show_all', '0');

        $jatis = Jati::all();

        $complaints = Complaint::with('jati')
            ->where('complaint_type', 'समस्या');

        if ($fromDate) {
            $complaints->whereDate('posted_date', '>=', $fromDate);
        }
        if ($toDate) {
            $complaints->whereDate('posted_date', '<=', $toDate);
        }
        if ($officeType) {
            $complaints->where('type', $officeType);
        }

        $complaintData = $complaints->get()
            ->groupBy('jati_id')
            ->map(function ($items, $jatiId) {
                $totalRegistered = $items->count();
                $totalCancel = $items->where('complaint_status', 5)->count();
                $totalSolved = $items->where('complaint_status', 4)->count();
                $jatiName = $items->first()->jati?->jati_name ?? 'उपलब्ध नहीं है';

                return (object)[
                    'jati_id' => $jatiId,
                    'jati_name' => $jatiName,
                    'total_registered' => $totalRegistered,
                    'total_cancel' => $totalCancel,
                    'total_solved' => $totalSolved,
                ];
            })
            ->sortByDesc(fn($item) => $item->total_registered);

        if ($showAll == '1') {
            $allData = $jatis->map(function ($j) use ($complaintData) {
                return $complaintData[$j->jati_id] ?? (object)[
                    'jati_id' => $j->jati_id,
                    'jati_name' => $j->jati_name,
                    'total_registered' => 0,
                    'total_cancel' => 0,
                    'total_solved' => 0,
                ];
            });

            $finalData = $allData->filter(function ($item) {
                return $item->total_registered == 0
                    && $item->total_cancel == 0
                    && $item->total_solved == 0;
            })->chunk(8);

            $registeredData = $complaintData->values()->filter(function ($item) {
                return $item->jati_name !== 'उपलब्ध नहीं है';
            });

            // Totals
            $totalsAll = [
                'total_jati' => $jatis->count(),
            ];

            $totalsRegistered = [
                'total_jati' => $registeredData->count(),
                'total_registered' => $registeredData->sum('total_registered'),
                'total_cancel' => $registeredData->sum('total_cancel'),
                'total_solved' => $registeredData->sum('total_solved'),
            ];
        } else {
            $finalData = $complaintData->values();
            $totalsAll = $totalsRegistered = [
                'total_jati' => $finalData->count(),
                'total_registered' => $finalData->sum('total_registered'),
                'total_cancel' => $finalData->sum('total_cancel'),
                'total_solved' => $finalData->sum('total_solved'),
            ];
        }

        if ($request->get('export') === 'excel') {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            $sheet->setCellValue('A1', 'क्र.');
            $sheet->setCellValue('B1', 'जाति');
            $sheet->setCellValue('C1', 'कुल शिकायतें');
            $sheet->setCellValue('D1', 'कुल निरस्त');
            $sheet->setCellValue('E1', 'कुल समाधान');

            $exportData = $finalData;
            if ($showAll == '1') {
                $exportData = collect($finalData)->flatten(1);
            }

            // Data rows
            $row = 2;
            foreach ($exportData as $index => $data) {
                $sheet->setCellValue('A' . $row, $index + 1);
                $sheet->setCellValue('B' . $row, $data->jati_name);
                $sheet->setCellValue('C' . $row, $data->total_registered);
                $sheet->setCellValue('D' . $row, $data->total_cancel);
                $sheet->setCellValue('E' . $row, $data->total_solved);
                $row++;
            }

            $writer = new Xlsx($spreadsheet);
            $filename = "jatiwise_report.xlsx";

            return response()->streamDownload(function () use ($writer) {
                $writer->save('php://output');
            }, $filename, [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ]);
        }


        // if ($request->get('export') === 'excel') {
        //     $filename = "jatiwise_report_" . date('Y-m-d_H-i-s') . ".xls";

        //     header("Content-Type: application/vnd.ms-excel; charset=utf-8");
        //     header("Content-Disposition: attachment; filename=\"$filename\"");
        //     header("Pragma: no-cache");
        //     header("Expires: 0");

        //     echo "<table border='1'>";

        //     if ($showAll == '1') {
        //         echo "<tr>
        //             <td colspan='5' style='text-align:center;'>
        //                 कुल जाति: (" . (isset($totalsAll['total_jati']) ? $totalsAll['total_jati'] : 0) . "), 
        //                 पंजीकृत जाति: (" . (isset($totalsRegistered['total_jati']) ? $totalsRegistered['total_jati'] : 0) . ")
        //             </td>
        //         </tr>";
        //     } else {
        //         echo "<tr>
        //             <td colspan='5' style='text-align:center;'>
        //                 कुल शिकायतें: (" . $finalData->sum('total_registered') . "), 
        //                 कुल निरस्त: (" . $finalData->sum('total_cancel') . "), 
        //                 कुल समाधान: (" . $finalData->sum('total_solved') . ")
        //             </td>
        //         </tr>";
        //     }

        //     echo "<thead>
        //         <tr>
        //             <th>क्र.</th>
        //             <th>जाति</th>
        //             <th>कुल शिकायतें</th>
        //             <th>कुल निरस्त</th>
        //             <th>कुल समाधान</th>
        //         </tr>
        //     </thead>";

        //     echo "<tbody>";

        //     $counter = 1;

        //     foreach ($finalData as $chunkOrRow) {
        //         if ($chunkOrRow instanceof \Illuminate\Support\Collection) {
        //             foreach ($chunkOrRow as $row) {
        //                 echo "<tr>
        //                     <td>{$counter}</td>
        //                     <td>{$row->jati_name}</td>
        //                     <td>{$row->total_registered}</td>
        //                     <td>{$row->total_cancel}</td>
        //                     <td>{$row->total_solved}</td>
        //                 </tr>";
        //                 $counter++;
        //             }
        //         } else {
        //             $row = $chunkOrRow;
        //             echo "<tr>
        //                 <td>{$counter}</td>
        //                 <td>{$row->jati_name}</td>
        //                 <td>{$row->total_registered}</td>
        //                 <td>{$row->total_cancel}</td>
        //                 <td>{$row->total_solved}</td>
        //             </tr>";
        //             $counter++;
        //         }
        //     }

        //     echo "</tbody></table>";
        //     exit;
        // }


        return view('admin.jatiwise_report', [
            'finalData' => $finalData,
            'totalsAll' => $totalsAll,
            'totalsRegistered' => $totalsRegistered,
            'hasFilter' => $request->hasAny(['from_date', 'to_date', 'office_type', 'show_all']),
            'fromDate' => $fromDate,
            'toDate' => $toDate,
            'officeType' => $officeType,
            'showAll' => $showAll,
        ]);
    }



    public function departmentReport(Request $request)
    {
        $fromDate = $request->input('from_date');
        $toDate = $request->input('to_date');
        // $officeType = $request->input('office_type');
        $officeType = $request->office_type ?? '2';

        $departments = Department::pluck('department_name', 'department_id');

        $complaintData = Complaint::query()
            ->where('complaint_type', 'समस्या')
            ->when($fromDate, fn($q) => $q->whereDate('posted_date', '>=', $fromDate))
            ->when($toDate, fn($q) => $q->whereDate('posted_date', '<=', $toDate))
            ->when($officeType, fn($q) => $q->where('type', $officeType))
            ->selectRaw('
            complaint_department as dept_name,
            COUNT(*) as total_registered,
            SUM(CASE WHEN complaint_status = 5 THEN 1 ELSE 0 END) as total_cancel,
            SUM(CASE WHEN complaint_status = 4 THEN 1 ELSE 0 END) as total_solved
        ')
            ->groupBy('complaint_department')
            ->get()
            ->keyBy('dept_name');

        $allData = $departments->map(function ($name, $id) use ($complaintData) {
            $data = $complaintData[$name] ?? null;

            return (object)[
                'department_id'    => $id,
                'department_name'  => $name,
                'total_registered' => $data->total_registered ?? 0,
                'total_cancel'     => $data->total_cancel ?? 0,
                'total_solved'     => $data->total_solved ?? 0,
            ];
        });

        $extraDepts = $complaintData->filter(function ($_, $name) use ($departments) {
            return !$departments->contains($name);
        })->map(function ($data, $name) {
            return (object)[
                'department_id'    => null,
                'department_name'  => 'उपलब्ध नहीं है',
                'total_registered' => $data->total_registered,
                'total_cancel'     => $data->total_cancel,
                'total_solved'     => $data->total_solved,
            ];
        });

        $allData = $allData->concat($extraDepts)
            ->sortByDesc(fn($d) => $d->total_registered)
            ->values();

        $withComplaints = $allData->filter(fn($d) => $d->total_registered > 0);
        $noComplaints   = $allData->filter(fn($d) => $d->total_registered == 0);

        $totalsAll = [
            'total_department'       => $allData->count(),
            'total_registered' => $withComplaints->sum('total_registered'),
            'total_cancel'     => $withComplaints->sum('total_cancel'),
            'total_solved'     => $withComplaints->sum('total_solved'),
        ];

        $totalsRegistered = [
            'total_department'       => $withComplaints->count(),
            'total_registered' => $withComplaints->sum('total_registered'),
            'total_cancel'     => $withComplaints->sum('total_cancel'),
            'total_solved'     => $withComplaints->sum('total_solved'),
        ];


        if ($request->get('export') === 'excel') {
            $filename = "department_report_" . date('Y-m-d_H-i-s') . ".xls";

            header("Content-Type: application/vnd.ms-excel; charset=utf-8");
            header("Content-Disposition: attachment; filename=\"$filename\"");
            header("Pragma: no-cache");
            header("Expires: 0");

            echo "<table border='1'>";

            // Summary header row
            echo "<tr>
            <td colspan='5' style='text-align:center;'>
                कुल शिकायतें: ({$totalsAll['total_registered']}), 
                कुल निरस्त: ({$totalsAll['total_cancel']}), 
                कुल समाधान: ({$totalsAll['total_solved']})
            </td>
          </tr>";

            echo "<thead>
            <tr>
                <th>क्र.</th>
                <th>विभाग</th>
                <th>कुल शिकायतें</th>
                <th>कुल निरस्त</th>
                <th>कुल समाधान</th>
            </tr>
          </thead>";
            echo "<tbody>";

            $counter = 1;
            foreach ($withComplaints as $row) {
                echo "<tr>
                <td>{$counter}</td>
                <td>{$row->department_name}</td>
                <td>{$row->total_registered}</td>
                <td>{$row->total_cancel}</td>
                <td>{$row->total_solved}</td>
              </tr>";
                $counter++;
            }

            if ($noComplaints->count() > 0) {
                echo "<tr>
          </tr>";
                echo "<tr>
            <td colspan='5' style='text-align:center;'>
               अप्राप्त शिकायत: कुल विभाग: ({$totalsAll['total_department']}), 
                पंजीकृत विभाग: ({$totalsRegistered['total_department']}), 
            </td>
          </tr>";
                foreach ($noComplaints->chunk(5) as $chunk) {
                    echo "<tr>";
                    foreach ($chunk as $row) {
                        echo "<td>{$row->department_name}</td>";
                    }
                    echo "</tr>";
                }
            }

            echo "</tbody></table>";
            exit;
        }



        $hasFilter = $request->hasAny(['from_date', 'to_date', 'office_type']);
        return view('admin.departmentwise_report', compact(
            'withComplaints',
            'noComplaints',
            'totalsRegistered',
            'totalsAll',
            'hasFilter',
            'fromDate',
            'toDate',
            'officeType'
        ));
    }

    public function areareport(Request $request)
    {
        $fromDate = $request->input('from_date');
        $toDate = $request->input('to_date');
        $officeType = $request->office_type ?? '2';
        $summary = $request->input('summary');
        $complaintType = $request->input('complaint_type', 'received');

        $divisions = Division::all();

        $complaints = Complaint::query()
            ->where('complaint_type', 'समस्या')
            ->when($fromDate, fn($q) => $q->whereDate('posted_date', '>=', $fromDate))
            ->when($toDate, fn($q) => $q->whereDate('posted_date', '<=', $toDate))
            ->when($officeType, fn($q) => $q->where('type', $officeType))
            ->when($request->division_id, fn($q) => $q->where('division_id', $request->division_id))
            ->when($request->district_id, fn($q) => $q->where('district_id', $request->district_id))
            ->when($request->vidhansabha_id, fn($q) => $q->where('vidhansabha_id', $request->vidhansabha_id))
            ->when($request->mandal_id, fn($q) => $q->where('mandal_id', $request->mandal_id))
            ->when($request->gram_id, fn($q) => $q->where('gram_id', $request->gram_id))
            ->when($request->polling_id, fn($q) => $q->where('polling_id', $request->polling_id))
            ->when($request->area_id, fn($q) => $q->where('area_id', $request->area_id));

        $areaData = collect();
        $withComplaints = collect();
        $noComplaints = collect();

        // switch ($summary) {
        //     case 'sambhag':
        //         $divisions = Division::all()->keyBy('division_id');

        //         $areaData = $complaints
        //             ->selectRaw('division_id, COUNT(*) as total_registered,
        //         SUM(CASE WHEN complaint_status = 5 THEN 1 ELSE 0 END) as total_cancel,
        //         SUM(CASE WHEN complaint_status = 4 THEN 1 ELSE 0 END) as total_solved')
        //             ->groupBy('division_id')
        //             ->get()
        //             ->map(function ($item) use ($divisions) {
        //                 $item->area_name = $divisions[$item->division_id]->division_name ?? null;
        //                 return $item;
        //             });

        //         $allDivisions = Division::query()
        //             ->when($request->division_id, fn($q) => $q->where('division_id', $request->division_id))
        //             ->get();

        //         $withComplaints = $areaData->map(fn($row) => (object)[
        //             'area_name' => $row->area_name ?? 'उपलब्ध नहीं',
        //             'total_registered' => $row->total_registered,
        //             'total_cancel' => $row->total_cancel,
        //             'total_solved' => $row->total_solved,
        //         ])->sortByDesc('total_registered')->values();

        //         $registeredIds = $areaData->pluck('division_id')->toArray();
        //         $noComplaints = $allDivisions->whereNotIn('division_id', $registeredIds)
        //             ->map(fn($d) => (object)['area_name' => $d->division_name]);
        //         break;

        //     case 'jila':
        //         $areaData = $complaints
        //             ->selectRaw('district_id, COUNT(*) as total_registered,
        //             SUM(CASE WHEN complaint_status = 5 THEN 1 ELSE 0 END) as total_cancel,
        //             SUM(CASE WHEN complaint_status = 4 THEN 1 ELSE 0 END) as total_solved')
        //             ->groupBy('district_id')
        //             ->get()
        //             ->map(function ($item) {
        //                 $item->district = District::find($item->district_id);
        //                 return $item;
        //             });

        //         $allDistricts = District::query()
        //             ->when($request->division_id, fn($q) => $q->where('division_id', $request->division_id))
        //             ->get();
        //         $withComplaints = $areaData->map(function ($row) {
        //             return (object)[
        //                 'area_name' => $row->district->district_name ?? 'उपलब्ध नहीं',
        //                 'total_registered' => $row->total_registered,
        //                 'total_cancel' => $row->total_cancel,
        //                 'total_solved' => $row->total_solved,
        //             ];
        //         })->sortByDesc('total_registered')->values();
        //         $registeredIds = $areaData->pluck('district_id')->toArray();
        //         $noComplaints = $allDistricts->whereNotIn('district_id', $registeredIds)
        //             ->map(fn($d) => (object)['area_name' => $d->district_name]);
        //         break;

        //     case 'vidhansabha':
        //         $areaData = $complaints
        //             ->selectRaw('vidhansabha_id, COUNT(*) as total_registered,
        //             SUM(CASE WHEN complaint_status = 5 THEN 1 ELSE 0 END) as total_cancel,
        //             SUM(CASE WHEN complaint_status = 4 THEN 1 ELSE 0 END) as total_solved')
        //             ->groupBy('vidhansabha_id')
        //             ->get()
        //             ->map(function ($item) {
        //                 $item->vidhansabha = VidhansabhaLokSabha::find($item->vidhansabha_id);
        //                 return $item;
        //             });

        //         $allVidhansabhas = VidhansabhaLokSabha::query()
        //             ->when($request->district_id, fn($q) => $q->where('district_id', $request->district_id))
        //             ->get();
        //         $withComplaints = $areaData->map(function ($row) {
        //             return (object)[
        //                 'area_name' => $row->vidhansabha->vidhansabha ?? 'उपलब्ध नहीं',
        //                 'total_registered' => $row->total_registered,
        //                 'total_cancel' => $row->total_cancel,
        //                 'total_solved' => $row->total_solved,
        //             ];
        //         })->sortByDesc('total_registered')->values();
        //         $registeredIds = $areaData->pluck('vidhansabha_id')->toArray();
        //         $noComplaints = $allVidhansabhas->whereNotIn('vidhansabha_id', $registeredIds)
        //             ->map(fn($v) => (object)['area_name' => $v->vidhansabha]);
        //         break;

        //     case 'mandal':
        //         $areaData = $complaints
        //             ->selectRaw('mandal_id, COUNT(*) as total_registered,
        //             SUM(CASE WHEN complaint_status = 5 THEN 1 ELSE 0 END) as total_cancel,
        //             SUM(CASE WHEN complaint_status = 4 THEN 1 ELSE 0 END) as total_solved')
        //             ->groupBy('mandal_id')
        //             ->get()
        //             ->map(function ($item) {
        //                 $item->mandal = Mandal::find($item->mandal_id);
        //                 return $item;
        //             });

        //         $allMandals = Mandal::query()
        //             ->when($request->vidhansabha_id, fn($q) => $q->where('vidhansabha_id', $request->vidhansabha_id))
        //             ->get();
        //         $withComplaints = $areaData->map(function ($row) {
        //             return (object)[
        //                 'area_name' => $row->mandal->mandal_name ?? 'उपलब्ध नहीं',
        //                 'total_registered' => $row->total_registered,
        //                 'total_cancel' => $row->total_cancel,
        //                 'total_solved' => $row->total_solved,
        //             ];
        //         })->sortByDesc('total_registered')->values();
        //         $registeredIds = $areaData->pluck('mandal_id')->toArray();
        //         $noComplaints = $allMandals->whereNotIn('mandal_id', $registeredIds)
        //             ->map(fn($v) => (object)['area_name' => $v->mandal_name]);
        //         break;
        //     case 'nagar':
        //         $areaData = $complaints
        //             ->selectRaw('gram_id, COUNT(*) as total_registered,
        //             SUM(CASE WHEN complaint_status = 5 THEN 1 ELSE 0 END) as total_cancel,
        //             SUM(CASE WHEN complaint_status = 4 THEN 1 ELSE 0 END) as total_solved')
        //             ->groupBy('gram_id')
        //             ->get()
        //             ->map(function ($item) {
        //                 $item->gram = Nagar::find($item->gram_id);
        //                 return $item;
        //             });

        //         $allNagars = Nagar::all();
        //         $allNagars = Nagar::query()
        //             ->when($request->mandal_id, fn($q) => $q->where('mandal_id', $request->mandal_id))
        //             ->get();
        //         $withComplaints = $areaData->map(function ($row) {
        //             return (object)[
        //                 'area_name' => $row->gram->nagar_name ?? 'उपलब्ध नहीं',
        //                 'total_registered' => $row->total_registered,
        //                 'total_cancel' => $row->total_cancel,
        //                 'total_solved' => $row->total_solved,
        //             ];
        //         })->sortByDesc('total_registered')->values();
        //         $registeredIds = $areaData->pluck('gram_id')->toArray();
        //         $noComplaints = $allNagars->whereNotIn('nagar_id', $registeredIds)
        //             ->map(fn($v) => (object)['area_name' => $v->nagar_name]);
        //         break;

        //     case 'polling':
        //         $areaData = $complaints
        //             ->selectRaw('polling_id, COUNT(*) as total_registered,
        //             SUM(CASE WHEN complaint_status = 5 THEN 1 ELSE 0 END) as total_cancel,
        //             SUM(CASE WHEN complaint_status = 4 THEN 1 ELSE 0 END) as total_solved')
        //             ->groupBy('polling_id')
        //             ->get()
        //             ->map(function ($item) {
        //                 $item->polling = Polling::find($item->polling_id);
        //                 return $item;
        //             });

        //         $allPollings = Polling::query()
        //             ->when($request->gram_id, fn($q) => $q->where('nagar_id', $request->gram_id))
        //             ->get();
        //         $withComplaints = $areaData->map(function ($row) {
        //             return (object)[
        //                 'area_name' => $row->polling->polling_name ?? 'उपलब्ध नहीं',
        //                 'total_registered' => $row->total_registered,
        //                 'total_cancel' => $row->total_cancel,
        //                 'total_solved' => $row->total_solved,
        //             ];
        //         })->sortByDesc('total_registered')->values();
        //         $registeredIds = $areaData->pluck('polling_id')->toArray();
        //         $noComplaints = $allPollings->whereNotIn('gram_polling_id', $registeredIds)
        //             ->map(fn($p) => (object)['area_name' => $p->polling_name]);
        //         break;

        //     case 'area':
        //         $areaData = $complaints
        //             ->selectRaw('area_id, COUNT(*) as total_registered,
        //             SUM(CASE WHEN complaint_status = 5 THEN 1 ELSE 0 END) as total_cancel,
        //             SUM(CASE WHEN complaint_status = 4 THEN 1 ELSE 0 END) as total_solved')
        //             ->groupBy('area_id')
        //             ->get()
        //             ->map(function ($item) {
        //                 $item->area = Area::find($item->area_id);
        //                 return $item;
        //             });

        //         $allAreas = Area::query()
        //             ->when($request->polling_id, fn($q) => $q->where('polling_id', $request->polling_id))
        //             ->get();
        //         $withComplaints = $areaData->map(function ($row) {
        //             return (object)[
        //                 'area_name' => $row->area->area_name ?? 'उपलब्ध नहीं',
        //                 'total_registered' => $row->total_registered,
        //                 'total_cancel' => $row->total_cancel,
        //                 'total_solved' => $row->total_solved,
        //             ];
        //         })->sortByDesc('total_registered')->values();
        //         $registeredIds = $areaData->pluck('area_id')->toArray();
        //         $noComplaints = $allAreas->whereNotIn('area_id', $registeredIds)
        //             ->map(fn($p) => (object)['area_name' => $p->area_name]);
        //         break;
        // }


        switch ($summary) {
            case 'sambhag':
                $divisions = Division::all()->keyBy('division_id');

                $areaData = $complaints
                    ->selectRaw('division_id, COUNT(*) as total_registered,
            SUM(CASE WHEN complaint_status = 5 THEN 1 ELSE 0 END) as total_cancel,
            SUM(CASE WHEN complaint_status = 4 THEN 1 ELSE 0 END) as total_solved')
                    ->groupBy('division_id')
                    ->get()
                    ->map(function ($item) use ($divisions) {
                        $item->area_name = $divisions[$item->division_id]->division_name ?? null;
                        return $item;
                    });

                $allDivisions = Division::query()
                    ->when($request->division_id, fn($q) => $q->where('division_id', $request->division_id))
                    ->get();

                $withComplaints = $areaData->map(fn($row) => (object)[
                    'area_name' => $row->area_name ?? 'उपलब्ध नहीं',
                    'total_registered' => $row->total_registered,
                    'total_cancel' => $row->total_cancel,
                    'total_solved' => $row->total_solved,
                    'division_id' => $row->division_id,
                ])->sortByDesc('total_registered')->values();

                $registeredIds = $areaData->pluck('division_id')->toArray();
                $noComplaints = $allDivisions->whereNotIn('division_id', $registeredIds)
                    ->map(fn($d) => (object)['area_name' => $d->division_name]);
                break;

            case 'jila':
                // Get districts with their parent division information
                $areaData = $complaints
                    ->get()
                    ->groupBy('district_id')
                    ->map(function ($group) {
                        $firstComplaint = $group->first();
                        $district = District::find($firstComplaint->district_id);
                        return (object)[
                            'district_id' => $firstComplaint->district_id,
                            'division_id' => $district->division_id ?? $firstComplaint->division_id,
                            'total_registered' => $group->count(),
                            'total_cancel' => $group->where('complaint_status', 5)->count(),
                            'total_solved' => $group->where('complaint_status', 4)->count(),
                        ];
                    })
                    ->values();

                $allDistricts = District::query()
                    ->when($request->division_id, fn($q) => $q->where('division_id', $request->division_id))
                    ->get();

                $withComplaints = $areaData->map(function ($row) {
                    $district = District::find($row->district_id);
                    return (object)[
                        'area_name' => $district->district_name ?? 'उपलब्ध नहीं',
                        'total_registered' => $row->total_registered,
                        'total_cancel' => $row->total_cancel,
                        'total_solved' => $row->total_solved,
                        'district_id' => $row->district_id,
                        'division_id' => $district->division_id ?? $row->division_id,
                    ];
                })->sortByDesc('total_registered')->values();

                $registeredIds = $areaData->pluck('district_id')->toArray();
                $noComplaints = $allDistricts->whereNotIn('district_id', $registeredIds)
                    ->map(fn($d) => (object)['area_name' => $d->district_name]);
                break;

            case 'vidhansabha':
                $areaData = $complaints
                    ->get()
                    ->groupBy('vidhansabha_id')
                    ->map(function ($group) {
                        $firstComplaint = $group->first();
                        $vidhansabha = VidhansabhaLokSabha::with('district')->find($firstComplaint->vidhansabha_id);
                        return (object)[
                            'vidhansabha_id' => $firstComplaint->vidhansabha_id,
                            'division_id' => $vidhansabha->district->division_id ?? $firstComplaint->division_id,
                            'district_id' => $vidhansabha->district_id ?? $firstComplaint->district_id,
                            'total_registered' => $group->count(),
                            'total_cancel' => $group->where('complaint_status', 5)->count(),
                            'total_solved' => $group->where('complaint_status', 4)->count(),
                        ];
                    })
                    ->values();

                $allVidhansabhas = VidhansabhaLokSabha::query()
                    ->when($request->district_id, fn($q) => $q->where('district_id', $request->district_id))
                    ->get();

                $withComplaints = $areaData->map(function ($row) {
                    $vidhansabha = VidhansabhaLokSabha::with('district')->find($row->vidhansabha_id);
                    return (object)[
                        'area_name' => $vidhansabha->vidhansabha ?? 'उपलब्ध नहीं',
                        'total_registered' => $row->total_registered,
                        'total_cancel' => $row->total_cancel,
                        'total_solved' => $row->total_solved,
                        'vidhansabha_id' => $row->vidhansabha_id,
                        'division_id' => $vidhansabha->district->division_id ?? $row->division_id,
                        'district_id' => $vidhansabha->district_id ?? $row->district_id,
                    ];
                })->sortByDesc('total_registered')->values();

                $registeredIds = $areaData->pluck('vidhansabha_id')->toArray();
                $noComplaints = $allVidhansabhas->whereNotIn('vidhansabha_id', $registeredIds)
                    ->map(fn($v) => (object)['area_name' => $v->vidhansabha]);
                break;

            case 'mandal':
                $areaData = $complaints
                    ->get()
                    ->groupBy('mandal_id')
                    ->map(function ($group) {
                        $firstComplaint = $group->first();
                        $mandal = Mandal::with(['vidhansabha.district'])->find($firstComplaint->mandal_id);
                        return (object)[
                            'mandal_id' => $firstComplaint->mandal_id,
                            'division_id' => $mandal->vidhansabha->district->division_id ?? $firstComplaint->division_id,
                            'district_id' => $mandal->vidhansabha->district_id ?? $firstComplaint->district_id,
                            'vidhansabha_id' => $mandal->vidhansabha_id ?? $firstComplaint->vidhansabha_id,
                            'total_registered' => $group->count(),
                            'total_cancel' => $group->where('complaint_status', 5)->count(),
                            'total_solved' => $group->where('complaint_status', 4)->count(),
                        ];
                    })
                    ->values();

                $allMandals = Mandal::query()
                    ->when($request->vidhansabha_id, fn($q) => $q->where('vidhansabha_id', $request->vidhansabha_id))
                    ->get();

                $withComplaints = $areaData->map(function ($row) {
                    $mandal = Mandal::with(['vidhansabha.district'])->find($row->mandal_id);
                    return (object)[
                        'area_name' => $mandal->mandal_name ?? 'उपलब्ध नहीं',
                        'total_registered' => $row->total_registered,
                        'total_cancel' => $row->total_cancel,
                        'total_solved' => $row->total_solved,
                        'mandal_id' => $row->mandal_id,
                        'division_id' => $mandal->vidhansabha->district->division_id ?? $row->division_id,
                        'district_id' => $mandal->vidhansabha->district_id ?? $row->district_id,
                        'vidhansabha_id' => $mandal->vidhansabha_id ?? $row->vidhansabha_id,
                    ];
                })->sortByDesc('total_registered')->values();

                $registeredIds = $areaData->pluck('mandal_id')->toArray();
                $noComplaints = $allMandals->whereNotIn('mandal_id', $registeredIds)
                    ->map(fn($v) => (object)['area_name' => $v->mandal_name]);
                break;

            case 'nagar':
                // First, get all valid gram_ids that exist in Nagar table
                $validGramIds = Nagar::pluck('nagar_id')->toArray();

                $areaData = $complaints
                    ->get()
                    ->groupBy('gram_id')
                    ->map(function ($group) use ($validGramIds) {
                        $firstComplaint = $group->first();
                        $gramId = $firstComplaint->gram_id;

                        // Check if gram_id exists in Nagar table
                        if (in_array($gramId, $validGramIds)) {
                            $nagar = Nagar::with(['mandal.vidhansabha.district'])->find($gramId);
                            $areaName = $nagar->nagar_name ?? 'उपलब्ध नहीं';
                            $divisionId = $nagar->mandal->vidhansabha->district->division_id ?? $firstComplaint->division_id;
                            $districtId = $nagar->mandal->vidhansabha->district_id ?? $firstComplaint->district_id;
                            $vidhansabhaId = $nagar->mandal->vidhansabha_id ?? $firstComplaint->vidhansabha_id;
                            $mandalId = $nagar->mandal_id ?? $firstComplaint->mandal_id;
                        } else {
                            // For invalid gram_ids, group them all under one "उपलब्ध नहीं" entry
                            $areaName = 'उपलब्ध नहीं';
                            $divisionId = $firstComplaint->division_id;
                            $districtId = $firstComplaint->district_id;
                            $vidhansabhaId = $firstComplaint->vidhansabha_id;
                            $mandalId = $firstComplaint->mandal_id;
                            $gramId = null; // Set to null for invalid IDs
                        }

                        return (object)[
                            'gram_id' => $gramId,
                            'nagar_id' => $gramId,
                            'division_id' => $divisionId,
                            'district_id' => $districtId,
                            'vidhansabha_id' => $vidhansabhaId,
                            'mandal_id' => $mandalId,
                            'area_name' => $areaName,
                            'total_registered' => $group->count(),
                            'total_cancel' => $group->where('complaint_status', 5)->count(),
                            'total_solved' => $group->where('complaint_status', 4)->count(),
                        ];
                    })
                    ->values();

                // Group by area_name to combine all "उपलब्ध नहीं" entries
                $areaData = $areaData->groupBy('area_name')->map(function ($group) {
                    $firstItem = $group->first();
                    return (object)[
                        'gram_id' => $firstItem->gram_id,
                        'nagar_id' => $firstItem->nagar_id,
                        'division_id' => $firstItem->division_id,
                        'district_id' => $firstItem->district_id,
                        'vidhansabha_id' => $firstItem->vidhansabha_id,
                        'mandal_id' => $firstItem->mandal_id,
                        'area_name' => $firstItem->area_name,
                        'total_registered' => $group->sum('total_registered'),
                        'total_cancel' => $group->sum('total_cancel'),
                        'total_solved' => $group->sum('total_solved'),
                    ];
                })->values();

                $allNagars = Nagar::query()
                    ->when($request->mandal_id, fn($q) => $q->where('mandal_id', $request->mandal_id))
                    ->get();

                $withComplaints = $areaData->map(function ($row) {
                    // For "उपलब्ध नहीं", we don't need to find Nagar again
                    if ($row->area_name === 'उपलब्ध नहीं') {
                        return (object)[
                            'area_name' => $row->area_name,
                            'total_registered' => $row->total_registered,
                            'total_cancel' => $row->total_cancel,
                            'total_solved' => $row->total_solved,
                            'gram_id' => $row->gram_id,
                            'nagar_id' => $row->nagar_id,
                            'division_id' => $row->division_id,
                            'district_id' => $row->district_id,
                            'vidhansabha_id' => $row->vidhansabha_id,
                            'mandal_id' => $row->mandal_id,
                        ];
                    } else {
                        $nagar = Nagar::with(['mandal.vidhansabha.district'])->find($row->gram_id);
                        return (object)[
                            'area_name' => $nagar->nagar_name ?? 'उपलब्ध नहीं',
                            'total_registered' => $row->total_registered,
                            'total_cancel' => $row->total_cancel,
                            'total_solved' => $row->total_solved,
                            'gram_id' => $row->gram_id,
                            'nagar_id' => $row->gram_id,
                            'division_id' => $nagar->mandal->vidhansabha->district->division_id ?? $row->division_id,
                            'district_id' => $nagar->mandal->vidhansabha->district_id ?? $row->district_id,
                            'vidhansabha_id' => $nagar->mandal->vidhansabha_id ?? $row->vidhansabha_id,
                            'mandal_id' => $nagar->mandal_id ?? $row->mandal_id,
                        ];
                    }
                })->sortByDesc('total_registered')->values();

                $registeredIds = $areaData->where('gram_id', '!=', null)->pluck('gram_id')->toArray();
                $noComplaints = $allNagars->whereNotIn('nagar_id', $registeredIds)
                    ->map(fn($v) => (object)['area_name' => $v->nagar_name]);
                break;

            case 'polling':
                $areaData = $complaints
                    ->get()
                    ->groupBy('polling_id')
                    ->map(function ($group) {
                        $firstComplaint = $group->first();
                        $polling = Polling::with(['nagar.mandal.vidhansabha.district'])->find($firstComplaint->polling_id);
                        return (object)[
                            'polling_id' => $firstComplaint->polling_id,
                            'division_id' => $polling->nagar->mandal->vidhansabha->district->division_id ?? $firstComplaint->division_id,
                            'district_id' => $polling->nagar->mandal->vidhansabha->district_id ?? $firstComplaint->district_id,
                            'vidhansabha_id' => $polling->nagar->mandal->vidhansabha_id ?? $firstComplaint->vidhansabha_id,
                            'mandal_id' => $polling->nagar->mandal_id ?? $firstComplaint->mandal_id,
                            'gram_id' => $polling->nagar_id ?? $firstComplaint->gram_id,
                            'total_registered' => $group->count(),
                            'total_cancel' => $group->where('complaint_status', 5)->count(),
                            'total_solved' => $group->where('complaint_status', 4)->count(),
                        ];
                    })
                    ->values();

                $allPollings = Polling::query()
                    ->when($request->gram_id, fn($q) => $q->where('nagar_id', $request->gram_id))
                    ->get();

                $withComplaints = $areaData->map(function ($row) {
                    $polling = Polling::with(['nagar.mandal.vidhansabha.district'])->find($row->polling_id);
                    return (object)[
                        'area_name' => ($polling->polling_name ?? 'उपलब्ध नहीं') .  '(' . ($polling->polling_no ?? '') . ')',
                        'total_registered' => $row->total_registered,
                        'total_cancel' => $row->total_cancel,
                        'total_solved' => $row->total_solved,
                        'polling_id' => $row->polling_id,
                        'division_id' => $polling->nagar->mandal->vidhansabha->district->division_id ?? $row->division_id,
                        'district_id' => $polling->nagar->mandal->vidhansabha->district_id ?? $row->district_id,
                        'vidhansabha_id' => $polling->nagar->mandal->vidhansabha_id ?? $row->vidhansabha_id,
                        'mandal_id' => $polling->nagar->mandal_id ?? $row->mandal_id,
                        'gram_id' => $polling->nagar_id ?? $row->gram_id,
                    ];
                })->sortByDesc('total_registered')->values();

                $registeredIds = $areaData->pluck('polling_id')->toArray();
                $noComplaints = $allPollings->whereNotIn('gram_polling_id', $registeredIds)
                    ->map(fn($p) => (object)['area_name' => $p->polling_name]);
                break;

            case 'area':
                $areaData = $complaints
                    ->get()
                    ->groupBy('area_id')
                    ->map(function ($group) {
                        $firstComplaint = $group->first();
                        $area = Area::with(['polling.nagar.mandal.vidhansabha.district'])->find($firstComplaint->area_id);
                        return (object)[
                            'area_id' => $firstComplaint->area_id,
                            'division_id' => $area->polling->nagar->mandal->vidhansabha->district->division_id ?? $firstComplaint->division_id,
                            'district_id' => $area->polling->nagar->mandal->vidhansabha->district_id ?? $firstComplaint->district_id,
                            'vidhansabha_id' => $area->polling->nagar->mandal->vidhansabha_id ?? $firstComplaint->vidhansabha_id,
                            'mandal_id' => $area->polling->nagar->mandal_id ?? $firstComplaint->mandal_id,
                            'gram_id' => $area->polling->nagar_id ?? $firstComplaint->gram_id,
                            'polling_id' => $area->polling_id ?? $firstComplaint->polling_id,
                            'total_registered' => $group->count(),
                            'total_cancel' => $group->where('complaint_status', 5)->count(),
                            'total_solved' => $group->where('complaint_status', 4)->count(),
                        ];
                    })
                    ->values();

                $allAreas = Area::query()
                    ->when($request->polling_id, fn($q) => $q->where('polling_id', $request->polling_id))
                    ->get();

                $withComplaints = $areaData->map(function ($row) {
                    $area = Area::with(['polling.nagar.mandal.vidhansabha.district'])->find($row->area_id);
                    return (object)[
                        'area_name' => $area->area_name ?? 'उपलब्ध नहीं',
                        'total_registered' => $row->total_registered,
                        'total_cancel' => $row->total_cancel,
                        'total_solved' => $row->total_solved,
                        'area_id' => $row->area_id,
                        'division_id' => $area->polling->nagar->mandal->vidhansabha->district->division_id ?? $row->division_id,
                        'district_id' => $area->polling->nagar->mandal->vidhansabha->district_id ?? $row->district_id,
                        'vidhansabha_id' => $area->polling->nagar->mandal->vidhansabha_id ?? $row->vidhansabha_id,
                        'mandal_id' => $area->polling->nagar->mandal_id ?? $row->mandal_id,
                        'gram_id' => $area->polling->nagar_id ?? $row->gram_id,
                        'polling_id' => $area->polling_id ?? $row->polling_id,
                    ];
                })->sortByDesc('total_registered')->values();

                $registeredIds = $areaData->pluck('area_id')->toArray();
                $noComplaints = $allAreas->whereNotIn('area_id', $registeredIds)
                    ->map(fn($p) => (object)['area_name' => $p->area_name]);
                break;
        }

        // Totals
        $totalsAll = [
            'total_registered' => $areaData->sum('total_registered'),
            'total_cancel' => $areaData->sum('total_cancel'),
            'total_solved' => $areaData->sum('total_solved'),
            'total_areas' => ($withComplaints->count() + $noComplaints->count()),
        ];
        $totalsRegistered = [
            'total_areas' => $withComplaints->count(),
        ];


        if ($request->get('export') === 'excel') {
            $filename = "areawise_report_" . date('Y-m-d_H-i-s') . ".xls";

            header("Content-Type: application/vnd.ms-excel; charset=utf-8");
            header("Content-Disposition: attachment; filename=\"$filename\"");
            header("Pragma: no-cache");
            header("Expires: 0");

            echo "<table border='1'>";

            // Determine complaint type
            $complaintType = $request->get('complaint_type', 'received');

            // Helper for summary label
            $labels = [
                'sambhag' => 'संभाग',
                'jila' => 'जिला',
                'vidhansabha' => 'विधानसभा',
                'mandal' => 'मंडल',
                'nagar' => 'कमांड एरिया',
                'polling' => 'पोलिंग',
                'area' => 'ग्राम/वार्ड चौपाल',
            ];
            $summaryLabel = $labels[$summary] ?? 'क्षेत्र';

            // Header for totals
            echo "<tr>
            <td colspan='5' style='text-align:center;'>";
            if ($complaintType === 'received') {
                echo "कुल शिकायतें: ({$totalsAll['total_registered']}), 
              कुल निरस्त: ({$totalsAll['total_cancel']}), 
              कुल समाधान: ({$totalsAll['total_solved']})";
            } elseif ($complaintType === 'not_received') {
                echo "अप्राप्त शिकायतें: कुल {$summaryLabel}: (" . (isset($totalsAll['total_areas']) ? $totalsAll['total_areas'] : 0) . "), 
                पंजीकृत {$summaryLabel}: (" . (isset($totalsRegistered['total_areas']) ? $totalsRegistered['total_areas'] : 0) . ")";
            } else { // all
                echo "कुल शिकायतें: ({$totalsAll['total_registered']}), 
              कुल निरस्त: ({$totalsAll['total_cancel']}), 
              कुल समाधान: ({$totalsAll['total_solved']})";
            }
            echo "</td></tr>";

            // Column headers


            $counter = 1;

            // Data rows
            if ($complaintType === 'received') {
                echo "<tr>
                    <th>क्र.</th>
                    <th>{$summaryLabel}</th>
                    <th>कुल शिकायतें</th>
                    <th>कुल निरस्त</th>
                    <th>कुल समाधान</th>
                </tr>";
                foreach ($withComplaints as $row) {
                    echo "<tr>
                    <td>{$counter}</td>
                    <td>{$row->area_name}</td>
                    <td>{$row->total_registered}</td>
                    <td>{$row->total_cancel}</td>
                    <td>{$row->total_solved}</td>
                  </tr>";
                    $counter++;
                }
            }

            if ($complaintType === 'not_received') {
                foreach ($noComplaints->chunk(5) as $chunk) {
                    echo "<tr>";
                    foreach ($chunk as $row) {
                        echo "<td>{$row->area_name}</td>";
                    }
                    $remaining = 5 - count($chunk);
                    for ($i = 0; $i < $remaining; $i++) {
                        echo "<td></td>";
                    }
                    echo "</tr>";
                }
            }

            if ($complaintType === 'all') {
                echo "<tr>
                    <th>क्र.</th>
                    <th>{$summaryLabel}</th>
                    <th>कुल शिकायतें</th>
                    <th>कुल निरस्त</th>
                    <th>कुल समाधान</th>
                </tr>";
                foreach ($withComplaints as $row) {
                    echo "<tr>
                    <td>{$counter}</td>
                    <td>{$row->area_name}</td>
                    <td>{$row->total_registered}</td>
                    <td>{$row->total_cancel}</td>
                    <td>{$row->total_solved}</td>
                  </tr>";
                    $counter++;
                }
                echo "<tr></tr>";

                echo "<tr>
                    <td colspan='5' style='text-align:center;'>
                    अप्राप्त शिकायत: कुल {$summaryLabel}: ({$totalsAll['total_areas']}), 
                        पंजीकृत {$summaryLabel}: ({$totalsRegistered['total_areas']}), 
                    </td>
                </tr>";
                foreach ($noComplaints->chunk(5) as $chunk) {

                    echo "<tr>";
                    foreach ($chunk as $row) {
                        echo "<td>{$row->area_name}</td>";
                    }
                    // Fill remaining columns if chunk < 5
                    $remaining = 5 - count($chunk);
                    for ($i = 0; $i < $remaining; $i++) {
                        echo "<td></td>";
                    }
                    echo "</tr>";
                }
            }

            echo "</table>";
            exit;
        }

        return view('admin.areawise_report', compact(
            'divisions',
            'areaData',
            'summary',
            'fromDate',
            'toDate',
            'officeType',
            'complaintType',
            'withComplaints',
            'noComplaints',
            'totalsAll',
            'totalsRegistered',
        ));
    }

    public function referenceReport(Request $request)
    {
        $fromDate   = $request->from_date;
        $toDate     = $request->to_date;
        $officeType = $request->office_type ?? '2';

        $complaints = Complaint::query()
            ->where('complaint_type', 'समस्या')
            ->when($fromDate, fn($q) => $q->whereDate('posted_date', '>=', $fromDate))
            ->when($toDate, fn($q) => $q->whereDate('posted_date', '<=', $toDate))
            ->when($officeType, fn($q) => $q->where('type', $officeType))
            ->get();

        $referenceData = $complaints->groupBy(function ($item) {
            $ref = trim($item->reference_name ?? '');
            return $ref !== '' ? $ref : 'उपलब्ध नहीं है';
        })
            ->map(function ($items, $reference_name) {
                return (object)[
                    'reference'        => $reference_name,
                    'total_registered' => $items->count(),
                    'total_cancel'     => $items->where('complaint_status', 5)->count(),
                    'total_solved'     => $items->where('complaint_status', 4)->count(),
                ];
            })
            ->sortByDesc(fn($row) => $row->total_registered)
            ->values();

        $totals = [
            'total_references' =>  $referenceData->filter(fn($row) => $row->reference !== 'उपलब्ध नहीं है')->count(),
            'total_registered' => $referenceData->sum('total_registered'),
            'total_cancel'     => $referenceData->sum('total_cancel'),
            'total_solved'     => $referenceData->sum('total_solved'),
        ];

        // Excel export
        if ($request->get('export') === 'excel') {
            $filename = "reference_report_" . date('Y-m-d_H-i-s') . ".xls";

            header("Content-Type: application/vnd.ms-excel; charset=utf-8");
            header("Content-Disposition: attachment; filename=\"$filename\"");
            header("Pragma: no-cache");
            header("Expires: 0");

            echo "<table border='1'>";
            echo "<tr>
                <td colspan='5' style='text-align:center;'>
                    कुल संदर्भ: ({$totals['total_references']}), 
                    कुल शिकायतें: ({$totals['total_registered']}), 
                    कुल निरस्त: ({$totals['total_cancel']}), 
                    कुल समाधान: ({$totals['total_solved']})
                </td>
              </tr>";

            echo "<thead>
                <tr>
                    <th>क्र.</th>
                    <th>संदर्भ</th>
                    <th>कुल शिकायतें</th>
                    <th>कुल निरस्त</th>
                    <th>कुल समाधान</th>
                </tr>
              </thead><tbody>";

            $counter = 1;
            foreach ($referenceData as $row) {
                echo "<tr>
                    <td>{$counter}</td>
                    <td>{$row->reference}</td>
                    <td>{$row->total_registered}</td>
                    <td>{$row->total_cancel}</td>
                    <td>{$row->total_solved}</td>
                </tr>";
                $counter++;
            }

            echo "</tbody></table>";
            exit;
        }

        return view('admin.reference_report', [
            'referenceData' => $referenceData,
            'totals'        => $totals,
            'fromDate'      => $fromDate,
            'toDate'        => $toDate,
            'officeType'    => $officeType,
        ]);
    }

    public function vikashjatiwise_report(Request $request)
    {
        $fromDate = $request->from_date;
        $toDate = $request->to_date;
        $officeType = $request->office_type ?? '2';
        $showAll = $request->input('show_all', '0');

        $jatis = Jati::all();

        $complaints = Complaint::with('jati')
            ->where('complaint_type', 'विकास');

        if ($fromDate) {
            $complaints->whereDate('posted_date', '>=', $fromDate);
        }
        if ($toDate) {
            $complaints->whereDate('posted_date', '<=', $toDate);
        }
        if ($officeType) {
            $complaints->where('type', $officeType);
        }

        $complaintData = $complaints->get()
            ->groupBy('jati_id')
            ->map(function ($items, $jatiId) {
                $totalRegistered = $items->count();
                $totalCancel = $items->where('complaint_status', 5)->count();
                $totalSolved = $items->where('complaint_status', 4)->count();
                $jatiName = $items->first()->jati?->jati_name ?? 'उपलब्ध नहीं है';

                return (object)[
                    'jati_id' => $jatiId,
                    'jati_name' => $jatiName,
                    'total_registered' => $totalRegistered,
                    'total_cancel' => $totalCancel,
                    'total_solved' => $totalSolved,
                ];
            })
            ->sortByDesc(fn($item) => $item->total_registered);

        if ($showAll == '1') {
            $allData = $jatis->map(function ($j) use ($complaintData) {
                return $complaintData[$j->jati_id] ?? (object)[
                    'jati_id' => $j->jati_id,
                    'jati_name' => $j->jati_name,
                    'total_registered' => 0,
                    'total_cancel' => 0,
                    'total_solved' => 0,
                ];
            });

            $finalData = $allData->filter(function ($item) {
                return $item->total_registered == 0
                    && $item->total_cancel == 0
                    && $item->total_solved == 0;
            })->chunk(8);

            $registeredData = $complaintData->values()->filter(function ($item) {
                return $item->jati_name !== 'उपलब्ध नहीं है';
            });

            // Totals
            $totalsAll = [
                'total_jati' => $jatis->count(),
            ];

            $totalsRegistered = [
                'total_jati' => $registeredData->count(),
                'total_registered' => $registeredData->sum('total_registered'),
                'total_cancel' => $registeredData->sum('total_cancel'),
                'total_solved' => $registeredData->sum('total_solved'),
            ];
        } else {
            $finalData = $complaintData->values();
            $totalsAll = $totalsRegistered = [
                'total_jati' => $finalData->count(),
                'total_registered' => $finalData->sum('total_registered'),
                'total_cancel' => $finalData->sum('total_cancel'),
                'total_solved' => $finalData->sum('total_solved'),
            ];
        }

        if ($request->get('export') === 'excel') {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            $sheet->setCellValue('A1', 'क्र.');
            $sheet->setCellValue('B1', 'जाति');
            $sheet->setCellValue('C1', 'कुल विकास कार्य');
            $sheet->setCellValue('D1', 'कुल निरस्त');
            $sheet->setCellValue('E1', 'कुल समाधान');

            $exportData = $finalData;
            if ($showAll == '1') {
                $exportData = collect($finalData)->flatten(1);
            }

            // Data rows
            $row = 2;
            foreach ($exportData as $index => $data) {
                $sheet->setCellValue('A' . $row, $index + 1);
                $sheet->setCellValue('B' . $row, $data->jati_name);
                $sheet->setCellValue('C' . $row, $data->total_registered);
                $sheet->setCellValue('D' . $row, $data->total_cancel);
                $sheet->setCellValue('E' . $row, $data->total_solved);
                $row++;
            }

            $writer = new Xlsx($spreadsheet);
            $filename = "vikashjatiwise_report.xlsx";

            return response()->streamDownload(function () use ($writer) {
                $writer->save('php://output');
            }, $filename, [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ]);
        }





        return view('admin.vikashjatiwise_report', [
            'finalData' => $finalData,
            'totalsAll' => $totalsAll,
            'totalsRegistered' => $totalsRegistered,
            'hasFilter' => $request->hasAny(['from_date', 'to_date', 'office_type', 'show_all']),
            'fromDate' => $fromDate,
            'toDate' => $toDate,
            'officeType' => $officeType,
            'showAll' => $showAll,
        ]);
    }


    public function vikashdepartmentReport(Request $request)
    {
        $fromDate = $request->input('from_date');
        $toDate = $request->input('to_date');
        $officeType = $request->office_type ?? '2';

        $departments = Department::pluck('department_name', 'department_id');

        $complaintData = Complaint::query()
            ->where('complaint_type', 'विकास')
            ->when($fromDate, fn($q) => $q->whereDate('posted_date', '>=', $fromDate))
            ->when($toDate, fn($q) => $q->whereDate('posted_date', '<=', $toDate))
            ->when($officeType, fn($q) => $q->where('type', $officeType))
            ->selectRaw('
            complaint_department as dept_name,
            COUNT(*) as total_registered,
            SUM(CASE WHEN complaint_status = 5 THEN 1 ELSE 0 END) as total_cancel,
            SUM(CASE WHEN complaint_status = 4 THEN 1 ELSE 0 END) as total_solved
        ')
            ->groupBy('complaint_department')
            ->get()
            ->keyBy('dept_name');

        $allData = $departments->map(function ($name, $id) use ($complaintData) {
            $data = $complaintData[$name] ?? null;

            return (object)[
                'department_id'    => $id,
                'department_name'  => $name,
                'total_registered' => $data->total_registered ?? 0,
                'total_cancel'     => $data->total_cancel ?? 0,
                'total_solved'     => $data->total_solved ?? 0,
            ];
        });

        $extraDepts = $complaintData->filter(function ($_, $name) use ($departments) {
            return !$departments->contains($name);
        })->map(function ($data, $name) {
            return (object)[
                'department_id'    => null,
                'department_name'  => 'उपलब्ध नहीं है',
                'total_registered' => $data->total_registered,
                'total_cancel'     => $data->total_cancel,
                'total_solved'     => $data->total_solved,
            ];
        });

        $allData = $allData->concat($extraDepts)
            ->sortByDesc(fn($d) => $d->total_registered)
            ->values();

        $withComplaints = $allData->filter(fn($d) => $d->total_registered > 0);
        $noComplaints   = $allData->filter(fn($d) => $d->total_registered == 0);

        $totalsAll = [
            'total_department'       => $allData->count(),
            'total_registered' => $withComplaints->sum('total_registered'),
            'total_cancel'     => $withComplaints->sum('total_cancel'),
            'total_solved'     => $withComplaints->sum('total_solved'),
        ];

        $totalsRegistered = [
            'total_department'       => $withComplaints->count(),
            'total_registered' => $withComplaints->sum('total_registered'),
            'total_cancel'     => $withComplaints->sum('total_cancel'),
            'total_solved'     => $withComplaints->sum('total_solved'),
        ];

        if ($request->get('export') === 'excel') {
            $filename = "vikashdepartment_report_" . date('Y-m-d_H-i-s') . ".xls";

            header("Content-Type: application/vnd.ms-excel; charset=utf-8");
            header("Content-Disposition: attachment; filename=\"$filename\"");
            header("Pragma: no-cache");
            header("Expires: 0");

            echo "<table border='1'>";

            echo "<tr>
            <td colspan='5' style='text-align:center;'>
                कुल विकास कार्य: ({$totalsAll['total_registered']}), 
                कुल निरस्त: ({$totalsAll['total_cancel']}), 
                कुल समाधान: ({$totalsAll['total_solved']})
            </td>
          </tr>";

            echo "<thead>
            <tr>
                <th>क्र.</th>
                <th>विभाग</th>
                <th>कुल विकास कार्य</th>
                <th>कुल निरस्त</th>
                <th>कुल समाधान</th>
            </tr>
          </thead>";
            echo "<tbody>";

            $counter = 1;
            foreach ($withComplaints as $row) {
                echo "<tr>
                <td>{$counter}</td>
                <td>{$row->department_name}</td>
                <td>{$row->total_registered}</td>
                <td>{$row->total_cancel}</td>
                <td>{$row->total_solved}</td>
              </tr>";
                $counter++;
            }

            if ($noComplaints->count() > 0) {
                echo "<tr>
          </tr>";
                echo "<tr>
            <td colspan='5' style='text-align:center;'>
               अप्राप्त विकास कार्य: कुल विभाग: ({$totalsAll['total_department']}), 
                पंजीकृत विभाग: ({$totalsRegistered['total_department']}), 
            </td>
          </tr>";
                foreach ($noComplaints->chunk(5) as $chunk) {
                    echo "<tr>";
                    foreach ($chunk as $row) {
                        echo "<td>{$row->department_name}</td>";
                    }
                    echo "</tr>";
                }
            }

            echo "</tbody></table>";
            exit;
        }

        $hasFilter = $request->hasAny(['from_date', 'to_date', 'office_type']);
        return view('admin.vikashdepartmentwise_report', compact(
            'withComplaints',
            'noComplaints',
            'totalsRegistered',
            'totalsAll',
            'hasFilter',
            'fromDate',
            'toDate',
            'officeType'
        ));
    }



    public function vikashareareport(Request $request)
    {
        $fromDate = $request->input('from_date');
        $toDate = $request->input('to_date');
        $officeType = $request->office_type ?? '2';
        $summary = $request->input('summary');
        $complaintType = $request->input('complaint_type', 'received');

        $divisions = Division::all();

        $complaints = Complaint::query()
            ->where('complaint_type', 'विकास')
            ->when($fromDate, fn($q) => $q->whereDate('posted_date', '>=', $fromDate))
            ->when($toDate, fn($q) => $q->whereDate('posted_date', '<=', $toDate))
            ->when($officeType, fn($q) => $q->where('type', $officeType))
            ->when($request->division_id, fn($q) => $q->where('division_id', $request->division_id))
            ->when($request->district_id, fn($q) => $q->where('district_id', $request->district_id))
            ->when($request->vidhansabha_id, fn($q) => $q->where('vidhansabha_id', $request->vidhansabha_id))
            ->when($request->mandal_id, fn($q) => $q->where('mandal_id', $request->mandal_id))
            ->when($request->gram_id, fn($q) => $q->where('gram_id', $request->gram_id))
            ->when($request->polling_id, fn($q) => $q->where('polling_id', $request->polling_id))
            ->when($request->area_id, fn($q) => $q->where('area_id', $request->area_id));

        $areaData = collect();
        $withComplaints = collect();
        $noComplaints = collect();

        // switch ($summary) {
        //     case 'sambhag':
        //         $divisions = Division::all()->keyBy('division_id');

        //         $areaData = $complaints
        //             ->selectRaw('division_id, COUNT(*) as total_registered,
        //         SUM(CASE WHEN complaint_status = 5 THEN 1 ELSE 0 END) as total_cancel,
        //         SUM(CASE WHEN complaint_status = 4 THEN 1 ELSE 0 END) as total_solved')
        //             ->groupBy('division_id')
        //             ->get()
        //             ->map(function ($item) use ($divisions) {
        //                 $item->area_name = $divisions[$item->division_id]->division_name ?? null;
        //                 return $item;
        //             });

        //         $allDivisions = Division::query()
        //             ->when($request->division_id, fn($q) => $q->where('division_id', $request->division_id))
        //             ->get();

        //         $withComplaints = $areaData->map(fn($row) => (object)[
        //             'area_name' => $row->area_name ?? 'उपलब्ध नहीं',
        //             'total_registered' => $row->total_registered,
        //             'total_cancel' => $row->total_cancel,
        //             'total_solved' => $row->total_solved,
        //         ])->sortByDesc('total_registered')->values();

        //         $registeredIds = $areaData->pluck('division_id')->toArray();
        //         $noComplaints = $allDivisions->whereNotIn('division_id', $registeredIds)
        //             ->map(fn($d) => (object)['area_name' => $d->division_name]);
        //         break;

        //     case 'jila':
        //         $areaData = $complaints
        //             ->selectRaw('district_id, COUNT(*) as total_registered,
        //             SUM(CASE WHEN complaint_status = 5 THEN 1 ELSE 0 END) as total_cancel,
        //             SUM(CASE WHEN complaint_status = 4 THEN 1 ELSE 0 END) as total_solved')
        //             ->groupBy('district_id')
        //             ->get()
        //             ->map(function ($item) {
        //                 $item->district = District::find($item->district_id);
        //                 return $item;
        //             });

        //         $allDistricts = District::query()
        //             ->when($request->division_id, fn($q) => $q->where('division_id', $request->division_id))
        //             ->get();
        //         $withComplaints = $areaData->map(function ($row) {
        //             return (object)[
        //                 'area_name' => $row->district->district_name ?? 'उपलब्ध नहीं',
        //                 'total_registered' => $row->total_registered,
        //                 'total_cancel' => $row->total_cancel,
        //                 'total_solved' => $row->total_solved,
        //             ];
        //         })->sortByDesc('total_registered')->values();
        //         $registeredIds = $areaData->pluck('district_id')->toArray();
        //         $noComplaints = $allDistricts->whereNotIn('district_id', $registeredIds)
        //             ->map(fn($d) => (object)['area_name' => $d->district_name]);
        //         break;

        //     case 'vidhansabha':
        //         $areaData = $complaints
        //             ->selectRaw('vidhansabha_id, COUNT(*) as total_registered,
        //             SUM(CASE WHEN complaint_status = 5 THEN 1 ELSE 0 END) as total_cancel,
        //             SUM(CASE WHEN complaint_status = 4 THEN 1 ELSE 0 END) as total_solved')
        //             ->groupBy('vidhansabha_id')
        //             ->get()
        //             ->map(function ($item) {
        //                 $item->vidhansabha = VidhansabhaLokSabha::find($item->vidhansabha_id);
        //                 return $item;
        //             });

        //         $allVidhansabhas = VidhansabhaLokSabha::query()
        //             ->when($request->district_id, fn($q) => $q->where('district_id', $request->district_id))
        //             ->get();
        //         $withComplaints = $areaData->map(function ($row) {
        //             return (object)[
        //                 'area_name' => $row->vidhansabha->vidhansabha ?? 'उपलब्ध नहीं',
        //                 'total_registered' => $row->total_registered,
        //                 'total_cancel' => $row->total_cancel,
        //                 'total_solved' => $row->total_solved,
        //             ];
        //         })->sortByDesc('total_registered')->values();
        //         $registeredIds = $areaData->pluck('vidhansabha_id')->toArray();
        //         $noComplaints = $allVidhansabhas->whereNotIn('vidhansabha_id', $registeredIds)
        //             ->map(fn($v) => (object)['area_name' => $v->vidhansabha]);
        //         break;

        //     case 'mandal':
        //         $areaData = $complaints
        //             ->selectRaw('mandal_id, COUNT(*) as total_registered,
        //             SUM(CASE WHEN complaint_status = 5 THEN 1 ELSE 0 END) as total_cancel,
        //             SUM(CASE WHEN complaint_status = 4 THEN 1 ELSE 0 END) as total_solved')
        //             ->groupBy('mandal_id')
        //             ->get()
        //             ->map(function ($item) {
        //                 $item->mandal = Mandal::find($item->mandal_id);
        //                 return $item;
        //             });

        //         $allMandals = Mandal::query()
        //             ->when($request->vidhansabha_id, fn($q) => $q->where('vidhansabha_id', $request->vidhansabha_id))
        //             ->get();
        //         $withComplaints = $areaData->map(function ($row) {
        //             return (object)[
        //                 'area_name' => $row->mandal->mandal_name ?? 'उपलब्ध नहीं',
        //                 'total_registered' => $row->total_registered,
        //                 'total_cancel' => $row->total_cancel,
        //                 'total_solved' => $row->total_solved,
        //             ];
        //         })->sortByDesc('total_registered')->values();
        //         $registeredIds = $areaData->pluck('mandal_id')->toArray();
        //         $noComplaints = $allMandals->whereNotIn('mandal_id', $registeredIds)
        //             ->map(fn($v) => (object)['area_name' => $v->mandal_name]);
        //         break;
        //     case 'nagar':
        //         $areaData = $complaints
        //             ->selectRaw('gram_id, COUNT(*) as total_registered,
        //             SUM(CASE WHEN complaint_status = 5 THEN 1 ELSE 0 END) as total_cancel,
        //             SUM(CASE WHEN complaint_status = 4 THEN 1 ELSE 0 END) as total_solved')
        //             ->groupBy('gram_id')
        //             ->get()
        //             ->map(function ($item) {
        //                 $item->gram = Nagar::find($item->gram_id);
        //                 return $item;
        //             });

        //         $allNagars = Nagar::all();
        //         $allNagars = Nagar::query()
        //             ->when($request->mandal_id, fn($q) => $q->where('mandal_id', $request->mandal_id))
        //             ->get();
        //         $withComplaints = $areaData->map(function ($row) {
        //             return (object)[
        //                 'area_name' => $row->gram->nagar_name ?? 'उपलब्ध नहीं',
        //                 'total_registered' => $row->total_registered,
        //                 'total_cancel' => $row->total_cancel,
        //                 'total_solved' => $row->total_solved,
        //             ];
        //         })->sortByDesc('total_registered')->values();
        //         $registeredIds = $areaData->pluck('gram_id')->toArray();
        //         $noComplaints = $allNagars->whereNotIn('nagar_id', $registeredIds)
        //             ->map(fn($v) => (object)['area_name' => $v->nagar_name]);
        //         break;

        //     case 'polling':
        //         $areaData = $complaints
        //             ->selectRaw('polling_id, COUNT(*) as total_registered,
        //             SUM(CASE WHEN complaint_status = 5 THEN 1 ELSE 0 END) as total_cancel,
        //             SUM(CASE WHEN complaint_status = 4 THEN 1 ELSE 0 END) as total_solved')
        //             ->groupBy('polling_id')
        //             ->get()
        //             ->map(function ($item) {
        //                 $item->polling = Polling::find($item->polling_id);
        //                 return $item;
        //             });

        //         $allPollings = Polling::query()
        //             ->when($request->gram_id, fn($q) => $q->where('nagar_id', $request->gram_id))
        //             ->get();
        //         $withComplaints = $areaData->map(function ($row) {
        //             return (object)[
        //                 'area_name' => $row->polling->polling_name ?? 'उपलब्ध नहीं',
        //                 'total_registered' => $row->total_registered,
        //                 'total_cancel' => $row->total_cancel,
        //                 'total_solved' => $row->total_solved,
        //             ];
        //         })->sortByDesc('total_registered')->values();
        //         $registeredIds = $areaData->pluck('polling_id')->toArray();
        //         $noComplaints = $allPollings->whereNotIn('gram_polling_id', $registeredIds)
        //             ->map(fn($p) => (object)['area_name' => $p->polling_name]);
        //         break;

        //     case 'area':
        //         $areaData = $complaints
        //             ->selectRaw('area_id, COUNT(*) as total_registered,
        //             SUM(CASE WHEN complaint_status = 5 THEN 1 ELSE 0 END) as total_cancel,
        //             SUM(CASE WHEN complaint_status = 4 THEN 1 ELSE 0 END) as total_solved')
        //             ->groupBy('area_id')
        //             ->get()
        //             ->map(function ($item) {
        //                 $item->area = Area::find($item->area_id);
        //                 return $item;
        //             });

        //         $allAreas = Area::query()
        //             ->when($request->polling_id, fn($q) => $q->where('polling_id', $request->polling_id))
        //             ->get();
        //         $withComplaints = $areaData->map(function ($row) {
        //             return (object)[
        //                 'area_name' => $row->area->area_name ?? 'उपलब्ध नहीं',
        //                 'total_registered' => $row->total_registered,
        //                 'total_cancel' => $row->total_cancel,
        //                 'total_solved' => $row->total_solved,
        //             ];
        //         })->sortByDesc('total_registered')->values();
        //         $registeredIds = $areaData->pluck('area_id')->toArray();
        //         $noComplaints = $allAreas->whereNotIn('area_id', $registeredIds)
        //             ->map(fn($p) => (object)['area_name' => $p->area_name]);
        //         break;
        // }

        switch ($summary) {
            case 'sambhag':
                $divisions = Division::all()->keyBy('division_id');

                $areaData = $complaints
                    ->selectRaw('division_id, COUNT(*) as total_registered,
                SUM(CASE WHEN complaint_status = 5 THEN 1 ELSE 0 END) as total_cancel,
                SUM(CASE WHEN complaint_status = 4 THEN 1 ELSE 0 END) as total_solved')
                    ->groupBy('division_id')
                    ->get()
                    ->map(function ($item) use ($divisions) {
                        $item->area_name = $divisions[$item->division_id]->division_name ?? null;
                        return $item;
                    });

                $allDivisions = Division::query()
                    ->when($request->division_id, fn($q) => $q->where('division_id', $request->division_id))
                    ->get();

                $withComplaints = $areaData->map(fn($row) => (object)[
                    'area_name' => $row->area_name ?? 'उपलब्ध नहीं',
                    'total_registered' => $row->total_registered,
                    'total_cancel' => $row->total_cancel,
                    'total_solved' => $row->total_solved,
                ])->sortByDesc('total_registered')->values();

                $registeredIds = $areaData->pluck('division_id')->toArray();
                $noComplaints = $allDivisions->whereNotIn('division_id', $registeredIds)
                    ->map(fn($d) => (object)['area_name' => $d->division_name]);
                break;

            case 'jila':
                $areaData = $complaints
                    ->selectRaw('district_id, COUNT(*) as total_registered,
                    SUM(CASE WHEN complaint_status = 5 THEN 1 ELSE 0 END) as total_cancel,
                    SUM(CASE WHEN complaint_status = 4 THEN 1 ELSE 0 END) as total_solved')
                    ->groupBy('district_id')
                    ->get()
                    ->map(function ($item) {
                        $item->district = District::find($item->district_id);
                        return $item;
                    });

                $allDistricts = District::query()
                    ->when($request->division_id, fn($q) => $q->where('division_id', $request->division_id))
                    ->get();
                $withComplaints = $areaData->map(function ($row) {
                    return (object)[
                        'area_name' => $row->district->district_name ?? 'उपलब्ध नहीं',
                        'total_registered' => $row->total_registered,
                        'total_cancel' => $row->total_cancel,
                        'total_solved' => $row->total_solved,
                    ];
                })->sortByDesc('total_registered')->values();
                $registeredIds = $areaData->pluck('district_id')->toArray();
                $noComplaints = $allDistricts->whereNotIn('district_id', $registeredIds)
                    ->map(fn($d) => (object)['area_name' => $d->district_name]);
                break;

            case 'vidhansabha':
                $areaData = $complaints
                    ->selectRaw('vidhansabha_id, COUNT(*) as total_registered,
                    SUM(CASE WHEN complaint_status = 5 THEN 1 ELSE 0 END) as total_cancel,
                    SUM(CASE WHEN complaint_status = 4 THEN 1 ELSE 0 END) as total_solved')
                    ->groupBy('vidhansabha_id')
                    ->get()
                    ->map(function ($item) {
                        $item->vidhansabha = VidhansabhaLokSabha::find($item->vidhansabha_id);
                        return $item;
                    });

                $allVidhansabhas = VidhansabhaLokSabha::query()
                    ->when($request->district_id, fn($q) => $q->where('district_id', $request->district_id))
                    ->get();
                $withComplaints = $areaData->map(function ($row) {
                    return (object)[
                        'area_name' => $row->vidhansabha->vidhansabha ?? 'उपलब्ध नहीं',
                        'total_registered' => $row->total_registered,
                        'total_cancel' => $row->total_cancel,
                        'total_solved' => $row->total_solved,
                    ];
                })->sortByDesc('total_registered')->values();
                $registeredIds = $areaData->pluck('vidhansabha_id')->toArray();
                $noComplaints = $allVidhansabhas->whereNotIn('vidhansabha_id', $registeredIds)
                    ->map(fn($v) => (object)['area_name' => $v->vidhansabha]);
                break;

            case 'mandal':
                $areaData = $complaints
                    ->selectRaw('mandal_id, COUNT(*) as total_registered,
                    SUM(CASE WHEN complaint_status = 5 THEN 1 ELSE 0 END) as total_cancel,
                    SUM(CASE WHEN complaint_status = 4 THEN 1 ELSE 0 END) as total_solved')
                    ->groupBy('mandal_id')
                    ->get()
                    ->map(function ($item) {
                        $item->mandal = Mandal::find($item->mandal_id);
                        return $item;
                    });

                $allMandals = Mandal::query()
                    ->when($request->vidhansabha_id, fn($q) => $q->where('vidhansabha_id', $request->vidhansabha_id))
                    ->get();
                $withComplaints = $areaData->map(function ($row) {
                    return (object)[
                        'area_name' => $row->mandal->mandal_name ?? 'उपलब्ध नहीं',
                        'total_registered' => $row->total_registered,
                        'total_cancel' => $row->total_cancel,
                        'total_solved' => $row->total_solved,
                    ];
                })->sortByDesc('total_registered')->values();
                $registeredIds = $areaData->pluck('mandal_id')->toArray();
                $noComplaints = $allMandals->whereNotIn('mandal_id', $registeredIds)
                    ->map(fn($v) => (object)['area_name' => $v->mandal_name]);
                break;
            case 'nagar':
                $areaData = $complaints
                    ->selectRaw('gram_id, COUNT(*) as total_registered,
                    SUM(CASE WHEN complaint_status = 5 THEN 1 ELSE 0 END) as total_cancel,
                    SUM(CASE WHEN complaint_status = 4 THEN 1 ELSE 0 END) as total_solved')
                    ->groupBy('gram_id')
                    ->get()
                    ->map(function ($item) {
                        $item->gram = Nagar::find($item->gram_id);
                        return $item;
                    });

                $allNagars = Nagar::all();
                $allNagars = Nagar::query()
                    ->when($request->mandal_id, fn($q) => $q->where('mandal_id', $request->mandal_id))
                    ->get();
                $withComplaints = $areaData->map(function ($row) {
                    return (object)[
                        'area_name' => $row->gram->nagar_name ?? 'उपलब्ध नहीं',
                        'total_registered' => $row->total_registered,
                        'total_cancel' => $row->total_cancel,
                        'total_solved' => $row->total_solved,
                    ];
                })->sortByDesc('total_registered')->values();
                $registeredIds = $areaData->pluck('gram_id')->toArray();
                $noComplaints = $allNagars->whereNotIn('nagar_id', $registeredIds)
                    ->map(fn($v) => (object)['area_name' => $v->nagar_name]);
                break;

            case 'polling':
                $areaData = $complaints
                    ->selectRaw('polling_id, COUNT(*) as total_registered,
                    SUM(CASE WHEN complaint_status = 5 THEN 1 ELSE 0 END) as total_cancel,
                    SUM(CASE WHEN complaint_status = 4 THEN 1 ELSE 0 END) as total_solved')
                    ->groupBy('polling_id')
                    ->get()
                    ->map(function ($item) {
                        $item->polling = Polling::find($item->polling_id);
                        return $item;
                    });

                $allPollings = Polling::query()
                    ->when($request->gram_id, fn($q) => $q->where('nagar_id', $request->gram_id))
                    ->get();
                $withComplaints = $areaData->map(function ($row) {
                    return (object)[
                        'area_name' => $row->polling->polling_name ?? 'उपलब्ध नहीं',
                        'total_registered' => $row->total_registered,
                        'total_cancel' => $row->total_cancel,
                        'total_solved' => $row->total_solved,
                    ];
                })->sortByDesc('total_registered')->values();
                $registeredIds = $areaData->pluck('polling_id')->toArray();
                $noComplaints = $allPollings->whereNotIn('gram_polling_id', $registeredIds)
                    ->map(fn($p) => (object)['area_name' => $p->polling_name]);
                break;

            case 'area':
                $areaData = $complaints
                    ->selectRaw('area_id, COUNT(*) as total_registered,
                    SUM(CASE WHEN complaint_status = 5 THEN 1 ELSE 0 END) as total_cancel,
                    SUM(CASE WHEN complaint_status = 4 THEN 1 ELSE 0 END) as total_solved')
                    ->groupBy('area_id')
                    ->get()
                    ->map(function ($item) {
                        $item->area = Area::find($item->area_id);
                        return $item;
                    });

                $allAreas = Area::query()
                    ->when($request->polling_id, fn($q) => $q->where('polling_id', $request->polling_id))
                    ->get();
                $withComplaints = $areaData->map(function ($row) {
                    return (object)[
                        'area_name' => $row->area->area_name ?? 'उपलब्ध नहीं',
                        'total_registered' => $row->total_registered,
                        'total_cancel' => $row->total_cancel,
                        'total_solved' => $row->total_solved,
                    ];
                })->sortByDesc('total_registered')->values();
                $registeredIds = $areaData->pluck('area_id')->toArray();
                $noComplaints = $allAreas->whereNotIn('area_id', $registeredIds)
                    ->map(fn($p) => (object)['area_name' => $p->area_name]);
                break;
        }


        // Totals
        $totalsAll = [
            'total_registered' => $areaData->sum('total_registered'),
            'total_cancel' => $areaData->sum('total_cancel'),
            'total_solved' => $areaData->sum('total_solved'),
            'total_areas' => ($withComplaints->count() + $noComplaints->count()),
        ];
        $totalsRegistered = [
            'total_areas' => $withComplaints->count(),
        ];


        if ($request->get('export') === 'excel') {
            $filename = "vikashareawise_report_" . date('Y-m-d_H-i-s') . ".xls";

            header("Content-Type: application/vnd.ms-excel; charset=utf-8");
            header("Content-Disposition: attachment; filename=\"$filename\"");
            header("Pragma: no-cache");
            header("Expires: 0");

            echo "<table border='1'>";

            // Determine complaint type
            $complaintType = $request->get('complaint_type', 'received');

            // Helper for summary label
            $labels = [
                'sambhag' => 'संभाग',
                'jila' => 'जिला',
                'vidhansabha' => 'विधानसभा',
                'mandal' => 'मंडल',
                'nagar' => 'कमांड एरिया',
                'polling' => 'पोलिंग',
                'area' => 'ग्राम/वार्ड चौपाल',
            ];
            $summaryLabel = $labels[$summary] ?? 'क्षेत्र';

            // Header for totals
            echo "<tr>
            <td colspan='5' style='text-align:center;'>";
            if ($complaintType === 'received') {
                echo "कुल विकास कार्य: ({$totalsAll['total_registered']}), 
              कुल निरस्त: ({$totalsAll['total_cancel']}), 
              कुल समाधान: ({$totalsAll['total_solved']})";
            } elseif ($complaintType === 'not_received') {
                echo "अप्राप्त विकास कार्य: कुल {$summaryLabel}: (" . (isset($totalsAll['total_areas']) ? $totalsAll['total_areas'] : 0) . "), 
                पंजीकृत {$summaryLabel}: (" . (isset($totalsRegistered['total_areas']) ? $totalsRegistered['total_areas'] : 0) . ")";
            } else { // all
                echo "कुल विकास कार्य: ({$totalsAll['total_registered']}), 
              कुल निरस्त: ({$totalsAll['total_cancel']}), 
              कुल समाधान: ({$totalsAll['total_solved']})";
            }
            echo "</td></tr>";

            // Column headers


            $counter = 1;

            // Data rows
            if ($complaintType === 'received') {
                echo "<tr>
                    <th>क्र.</th>
                    <th>{$summaryLabel}</th>
                    <th>कुल विकास कार्य</th>
                    <th>कुल निरस्त</th>
                    <th>कुल समाधान</th>
                </tr>";
                foreach ($withComplaints as $row) {
                    echo "<tr>
                    <td>{$counter}</td>
                    <td>{$row->area_name}</td>
                    <td>{$row->total_registered}</td>
                    <td>{$row->total_cancel}</td>
                    <td>{$row->total_solved}</td>
                  </tr>";
                    $counter++;
                }
            }

            if ($complaintType === 'not_received') {
                foreach ($noComplaints->chunk(5) as $chunk) {
                    echo "<tr>";
                    foreach ($chunk as $row) {
                        echo "<td>{$row->area_name}</td>";
                    }
                    $remaining = 5 - count($chunk);
                    for ($i = 0; $i < $remaining; $i++) {
                        echo "<td></td>";
                    }
                    echo "</tr>";
                }
            }

            if ($complaintType === 'all') {
                echo "<tr>
                    <th>क्र.</th>
                    <th>{$summaryLabel}</th>
                    <th>कुल विकास कार्य</th>
                    <th>कुल निरस्त</th>
                    <th>कुल समाधान</th>
                </tr>";
                foreach ($withComplaints as $row) {
                    echo "<tr>
                    <td>{$counter}</td>
                    <td>{$row->area_name}</td>
                    <td>{$row->total_registered}</td>
                    <td>{$row->total_cancel}</td>
                    <td>{$row->total_solved}</td>
                  </tr>";
                    $counter++;
                }
                echo "<tr></tr>";

                echo "<tr>
                    <td colspan='5' style='text-align:center;'>
                    अप्राप्त विकास कार्य: कुल {$summaryLabel}: ({$totalsAll['total_areas']}), 
                        पंजीकृत {$summaryLabel}: ({$totalsRegistered['total_areas']}), 
                    </td>
                </tr>";
                foreach ($noComplaints->chunk(5) as $chunk) {

                    echo "<tr>";
                    foreach ($chunk as $row) {
                        echo "<td>{$row->area_name}</td>";
                    }
                    // Fill remaining columns if chunk < 5
                    $remaining = 5 - count($chunk);
                    for ($i = 0; $i < $remaining; $i++) {
                        echo "<td></td>";
                    }
                    echo "</tr>";
                }
            }

            echo "</table>";
            exit;
        }

        return view('admin.vikashareawise_report', compact(
            'divisions',
            'areaData',
            'summary',
            'fromDate',
            'toDate',
            'officeType',
            'complaintType',
            'withComplaints',
            'noComplaints',
            'totalsAll',
            'totalsRegistered',
        ));
    }

    public function vikashreferenceReport(Request $request)
    {
        $fromDate   = $request->from_date;
        $toDate     = $request->to_date;
        $officeType = $request->office_type ?? '2';

        $complaints = Complaint::query()
            ->where('complaint_type', 'विकास')
            ->when($fromDate, fn($q) => $q->whereDate('posted_date', '>=', $fromDate))
            ->when($toDate, fn($q) => $q->whereDate('posted_date', '<=', $toDate))
            ->when($officeType, fn($q) => $q->where('type', $officeType))
            ->get();

        $referenceData = $complaints->groupBy(function ($item) {
            $ref = trim($item->reference_name ?? '');
            return $ref !== '' ? $ref : 'उपलब्ध नहीं है';
        })
            ->map(function ($items, $reference_name) {
                return (object)[
                    'reference'        => $reference_name,
                    'total_registered' => $items->count(),
                    'total_cancel'     => $items->where('complaint_status', 5)->count(),
                    'total_solved'     => $items->where('complaint_status', 4)->count(),
                ];
            })
            ->sortByDesc(fn($row) => $row->total_registered)
            ->values();

        $totals = [
            'total_references' =>  $referenceData->filter(fn($row) => $row->reference !== 'उपलब्ध नहीं है')->count(),
            'total_registered' => $referenceData->sum('total_registered'),
            'total_cancel'     => $referenceData->sum('total_cancel'),
            'total_solved'     => $referenceData->sum('total_solved'),
        ];

        // Excel export
        if ($request->get('export') === 'excel') {
            $filename = "vikashreference_report_" . date('Y-m-d_H-i-s') . ".xls";

            header("Content-Type: application/vnd.ms-excel; charset=utf-8");
            header("Content-Disposition: attachment; filename=\"$filename\"");
            header("Pragma: no-cache");
            header("Expires: 0");

            echo "<table border='1'>";
            echo "<tr>
                <td colspan='5' style='text-align:center;'>
                    कुल संदर्भ: ({$totals['total_references']}), 
                    कुल विकास कार्य: ({$totals['total_registered']}), 
                    कुल निरस्त: ({$totals['total_cancel']}), 
                    कुल समाधान: ({$totals['total_solved']})
                </td>
              </tr>";

            echo "<thead>
                <tr>
                    <th>क्र.</th>
                    <th>संदर्भ</th>
                    <th>कुल विकास कार्य</th>
                    <th>कुल निरस्त</th>
                    <th>कुल समाधान</th>
                </tr>
              </thead><tbody>";

            $counter = 1;
            foreach ($referenceData as $row) {
                echo "<tr>
                    <td>{$counter}</td>
                    <td>{$row->reference}</td>
                    <td>{$row->total_registered}</td>
                    <td>{$row->total_cancel}</td>
                    <td>{$row->total_solved}</td>
                </tr>";
                $counter++;
            }

            echo "</tbody></table>";
            exit;
        }

        return view('admin.vikashreference_report', [
            'referenceData' => $referenceData,
            'totals'        => $totals,
            'fromDate'      => $fromDate,
            'toDate'        => $toDate,
            'officeType'    => $officeType,
        ]);
    }

    public function suchnajatiwise_report(Request $request)
    {
        $fromDate = $request->from_date;
        $toDate = $request->to_date;
        $officeType = $request->office_type ?? '2';
        $showAll = $request->input('show_all', '0');

        $jatis = Jati::all();

        $complaints = Complaint::with('jati')
            ->whereIn('complaint_type', ['अशुभ सुचना', 'शुभ सुचना']);

        if ($fromDate) {
            $complaints->whereDate('posted_date', '>=', $fromDate);
        }
        if ($toDate) {
            $complaints->whereDate('posted_date', '<=', $toDate);
        }
        if ($officeType) {
            $complaints->where('type', $officeType);
        }

        $complaintData = $complaints->get()
            ->groupBy('jati_id')
            ->map(function ($items, $jatiId) {
                $totalRegistered = $items->count();
                $totalSolved = $items->whereIn('complaint_status', [13, 14, 15, 16, 17])->count();
                $totalCancel = $items->where('complaint_status', 18)->count();
                $jatiName = $items->first()->jati?->jati_name ?? 'उपलब्ध नहीं है';

                return (object)[
                    'jati_id' => $jatiId,
                    'jati_name' => $jatiName,
                    'total_registered' => $totalRegistered,
                    'total_cancel' => $totalCancel,
                    'total_solved' => $totalSolved,
                ];
            })
            ->sortByDesc(fn($item) => $item->total_registered);

        if ($showAll == '1') {
            $allData = $jatis->map(function ($j) use ($complaintData) {
                return $complaintData[$j->jati_id] ?? (object)[
                    'jati_id' => $j->jati_id,
                    'jati_name' => $j->jati_name,
                    'total_registered' => 0,
                    'total_cancel' => 0,
                    'total_solved' => 0,
                ];
            });

            $finalData = $allData->filter(function ($item) {
                return $item->total_registered == 0
                    && $item->total_cancel == 0
                    && $item->total_solved == 0;
            })->chunk(8);

            $registeredData = $complaintData->values()->filter(function ($item) {
                return $item->jati_name !== 'उपलब्ध नहीं है';
            });

            // Totals
            $totalsAll = [
                'total_jati' => $jatis->count(),
            ];

            $totalsRegistered = [
                'total_jati' => $registeredData->count(),
                'total_registered' => $registeredData->sum('total_registered'),
                'total_cancel' => $registeredData->sum('total_cancel'),
                'total_solved' => $registeredData->sum('total_solved'),
            ];
        } else {
            $finalData = $complaintData->values();
            $totalsAll = $totalsRegistered = [
                'total_jati' => $finalData->count(),
                'total_registered' => $finalData->sum('total_registered'),
                'total_cancel' => $finalData->sum('total_cancel'),
                'total_solved' => $finalData->sum('total_solved'),
            ];
        }

        if ($request->get('export') === 'excel') {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            $sheet->setCellValue('A1', 'क्र.');
            $sheet->setCellValue('B1', 'जाति');
            $sheet->setCellValue('C1', 'कुल सुचना');
            $sheet->setCellValue('D1', 'कुल निरस्त');
            $sheet->setCellValue('E1', 'कुल समाधान');

            $exportData = $finalData;
            if ($showAll == '1') {
                $exportData = collect($finalData)->flatten(1);
            }

            // Data rows
            $row = 2;
            foreach ($exportData as $index => $data) {
                $sheet->setCellValue('A' . $row, $index + 1);
                $sheet->setCellValue('B' . $row, $data->jati_name);
                $sheet->setCellValue('C' . $row, $data->total_registered);
                $sheet->setCellValue('D' . $row, $data->total_cancel);
                $sheet->setCellValue('E' . $row, $data->total_solved);
                $row++;
            }

            $writer = new Xlsx($spreadsheet);
            $filename = "suchnajatiwise_report.xlsx";

            return response()->streamDownload(function () use ($writer) {
                $writer->save('php://output');
            }, $filename, [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ]);
        }





        return view('admin.suchnajatiwise_report', [
            'finalData' => $finalData,
            'totalsAll' => $totalsAll,
            'totalsRegistered' => $totalsRegistered,
            'hasFilter' => $request->hasAny(['from_date', 'to_date', 'office_type', 'show_all']),
            'fromDate' => $fromDate,
            'toDate' => $toDate,
            'officeType' => $officeType,
            'showAll' => $showAll,
        ]);
    }

    public function suchnaareareport(Request $request)
    {
        $fromDate = $request->input('from_date');
        $toDate = $request->input('to_date');
        $officeType = $request->input('office_type');
        $summary = $request->input('summary');
        $complaintType = $request->input('complaint_type', 'received');

        $divisions = Division::all();

        $complaints = Complaint::query()
            ->whereIn('complaint_type', ['अशुभ सुचना', 'शुभ सुचना'])
            ->when($fromDate, fn($q) => $q->whereDate('posted_date', '>=', $fromDate))
            ->when($toDate, fn($q) => $q->whereDate('posted_date', '<=', $toDate))
            ->when($officeType, fn($q) => $q->where('type', $officeType))
            ->when($request->division_id, fn($q) => $q->where('division_id', $request->division_id))
            ->when($request->district_id, fn($q) => $q->where('district_id', $request->district_id))
            ->when($request->vidhansabha_id, fn($q) => $q->where('vidhansabha_id', $request->vidhansabha_id))
            ->when($request->mandal_id, fn($q) => $q->where('mandal_id', $request->mandal_id))
            ->when($request->gram_id, fn($q) => $q->where('gram_id', $request->gram_id))
            ->when($request->polling_id, fn($q) => $q->where('polling_id', $request->polling_id))
            ->when($request->area_id, fn($q) => $q->where('area_id', $request->area_id));

        $areaData = collect();
        $withComplaints = collect();
        $noComplaints = collect();

        switch ($summary) {
            case 'sambhag':
                $divisions = Division::all()->keyBy('division_id');

                $areaData = $complaints
                    ->selectRaw('division_id, COUNT(*) as total_registered,
                SUM(CASE WHEN complaint_status = 18 THEN 1 ELSE 0 END) as total_cancel,
                SUM(CASE WHEN complaint_status IN (13, 14, 15, 16, 17) THEN 1 ELSE 0 END) as total_solved')
                    ->groupBy('division_id')
                    ->get()
                    ->map(function ($item) use ($divisions) {
                        $item->area_name = $divisions[$item->division_id]->division_name ?? null;
                        return $item;
                    });

                $allDivisions = Division::query()
                    ->when($request->division_id, fn($q) => $q->where('division_id', $request->division_id))
                    ->get();

                $withComplaints = $areaData->map(fn($row) => (object)[
                    'area_name' => $row->area_name ?? 'उपलब्ध नहीं',
                    'total_registered' => $row->total_registered,
                    'total_cancel' => $row->total_cancel,
                    'total_solved' => $row->total_solved,
                ])->sortByDesc('total_registered')->values();

                $registeredIds = $areaData->pluck('division_id')->toArray();
                $noComplaints = $allDivisions->whereNotIn('division_id', $registeredIds)
                    ->map(fn($d) => (object)['area_name' => $d->division_name]);
                break;

            case 'jila':
                $areaData = $complaints
                    ->selectRaw('district_id, COUNT(*) as total_registered,
                    SUM(CASE WHEN complaint_status = 18 THEN 1 ELSE 0 END) as total_cancel,
                SUM(CASE WHEN complaint_status IN (13, 14, 15, 16, 17) THEN 1 ELSE 0 END) as total_solved')
                    ->groupBy('district_id')
                    ->get()
                    ->map(function ($item) {
                        $item->district = District::find($item->district_id);
                        return $item;
                    });

                $allDistricts = District::query()
                    ->when($request->division_id, fn($q) => $q->where('division_id', $request->division_id))
                    ->get();
                $withComplaints = $areaData->map(function ($row) {
                    return (object)[
                        'area_name' => $row->district->district_name ?? 'उपलब्ध नहीं',
                        'total_registered' => $row->total_registered,
                        'total_cancel' => $row->total_cancel,
                        'total_solved' => $row->total_solved,
                    ];
                })->sortByDesc('total_registered')->values();
                $registeredIds = $areaData->pluck('district_id')->toArray();
                $noComplaints = $allDistricts->whereNotIn('district_id', $registeredIds)
                    ->map(fn($d) => (object)['area_name' => $d->district_name]);
                break;

            case 'vidhansabha':
                $areaData = $complaints
                    ->selectRaw('vidhansabha_id, COUNT(*) as total_registered,
                    SUM(CASE WHEN complaint_status = 18 THEN 1 ELSE 0 END) as total_cancel,
                SUM(CASE WHEN complaint_status IN (13, 14, 15, 16, 17) THEN 1 ELSE 0 END) as total_solved')
                    ->groupBy('vidhansabha_id')
                    ->get()
                    ->map(function ($item) {
                        $item->vidhansabha = VidhansabhaLokSabha::find($item->vidhansabha_id);
                        return $item;
                    });

                $allVidhansabhas = VidhansabhaLokSabha::query()
                    ->when($request->district_id, fn($q) => $q->where('district_id', $request->district_id))
                    ->get();
                $withComplaints = $areaData->map(function ($row) {
                    return (object)[
                        'area_name' => $row->vidhansabha->vidhansabha ?? 'उपलब्ध नहीं',
                        'total_registered' => $row->total_registered,
                        'total_cancel' => $row->total_cancel,
                        'total_solved' => $row->total_solved,
                    ];
                })->sortByDesc('total_registered')->values();
                $registeredIds = $areaData->pluck('vidhansabha_id')->toArray();
                $noComplaints = $allVidhansabhas->whereNotIn('vidhansabha_id', $registeredIds)
                    ->map(fn($v) => (object)['area_name' => $v->vidhansabha]);
                break;

            case 'mandal':
                $areaData = $complaints
                    ->selectRaw('mandal_id, COUNT(*) as total_registered,
                    SUM(CASE WHEN complaint_status = 18 THEN 1 ELSE 0 END) as total_cancel,
                SUM(CASE WHEN complaint_status IN (13, 14, 15, 16, 17) THEN 1 ELSE 0 END) as total_solved')
                    ->groupBy('mandal_id')
                    ->get()
                    ->map(function ($item) {
                        $item->mandal = Mandal::find($item->mandal_id);
                        return $item;
                    });

                $allMandals = Mandal::query()
                    ->when($request->vidhansabha_id, fn($q) => $q->where('vidhansabha_id', $request->vidhansabha_id))
                    ->get();
                $withComplaints = $areaData->map(function ($row) {
                    return (object)[
                        'area_name' => $row->mandal->mandal_name ?? 'उपलब्ध नहीं',
                        'total_registered' => $row->total_registered,
                        'total_cancel' => $row->total_cancel,
                        'total_solved' => $row->total_solved,
                    ];
                })->sortByDesc('total_registered')->values();
                $registeredIds = $areaData->pluck('mandal_id')->toArray();
                $noComplaints = $allMandals->whereNotIn('mandal_id', $registeredIds)
                    ->map(fn($v) => (object)['area_name' => $v->mandal_name]);
                break;
            case 'nagar':
                $areaData = $complaints
                    ->selectRaw('gram_id, COUNT(*) as total_registered,
                    SUM(CASE WHEN complaint_status = 18 THEN 1 ELSE 0 END) as total_cancel,
                SUM(CASE WHEN complaint_status IN (13, 14, 15, 16, 17) THEN 1 ELSE 0 END) as total_solved')
                    ->groupBy('gram_id')
                    ->get()
                    ->map(function ($item) {
                        $item->gram = Nagar::find($item->gram_id);
                        return $item;
                    });

                $allNagars = Nagar::all();
                $allNagars = Nagar::query()
                    ->when($request->mandal_id, fn($q) => $q->where('mandal_id', $request->mandal_id))
                    ->get();
                $withComplaints = $areaData->map(function ($row) {
                    return (object)[
                        'area_name' => $row->gram->nagar_name ?? 'उपलब्ध नहीं',
                        'total_registered' => $row->total_registered,
                        'total_cancel' => $row->total_cancel,
                        'total_solved' => $row->total_solved,
                    ];
                })->sortByDesc('total_registered')->values();
                $registeredIds = $areaData->pluck('gram_id')->toArray();
                $noComplaints = $allNagars->whereNotIn('nagar_id', $registeredIds)
                    ->map(fn($v) => (object)['area_name' => $v->nagar_name]);
                break;

            case 'polling':
                $areaData = $complaints
                    ->selectRaw('polling_id, COUNT(*) as total_registered,
                  SUM(CASE WHEN complaint_status = 18 THEN 1 ELSE 0 END) as total_cancel,
                SUM(CASE WHEN complaint_status IN (13, 14, 15, 16, 17) THEN 1 ELSE 0 END) as total_solved')
                    ->groupBy('polling_id')
                    ->get()
                    ->map(function ($item) {
                        $item->polling = Polling::find($item->polling_id);
                        return $item;
                    });

                $allPollings = Polling::query()
                    ->when($request->gram_id, fn($q) => $q->where('nagar_id', $request->gram_id))
                    ->get();
                $withComplaints = $areaData->map(function ($row) {
                    return (object)[
                        'area_name' => $row->polling->polling_name ?? 'उपलब्ध नहीं',
                        'total_registered' => $row->total_registered,
                        'total_cancel' => $row->total_cancel,
                        'total_solved' => $row->total_solved,
                    ];
                })->sortByDesc('total_registered')->values();
                $registeredIds = $areaData->pluck('polling_id')->toArray();
                $noComplaints = $allPollings->whereNotIn('gram_polling_id', $registeredIds)
                    ->map(fn($p) => (object)['area_name' => $p->polling_name]);
                break;

            case 'area':
                $areaData = $complaints
                    ->selectRaw('area_id, COUNT(*) as total_registered,
                    SUM(CASE WHEN complaint_status = 18 THEN 1 ELSE 0 END) as total_cancel,
                SUM(CASE WHEN complaint_status IN (13, 14, 15, 16, 17) THEN 1 ELSE 0 END) as total_solved')
                    ->groupBy('area_id')
                    ->get()
                    ->map(function ($item) {
                        $item->area = Area::find($item->area_id);
                        return $item;
                    });

                $allAreas = Area::query()
                    ->when($request->polling_id, fn($q) => $q->where('polling_id', $request->polling_id))
                    ->get();
                $withComplaints = $areaData->map(function ($row) {
                    return (object)[
                        'area_name' => $row->area->area_name ?? 'उपलब्ध नहीं',
                        'total_registered' => $row->total_registered,
                        'total_cancel' => $row->total_cancel,
                        'total_solved' => $row->total_solved,
                    ];
                })->sortByDesc('total_registered')->values();
                $registeredIds = $areaData->pluck('area_id')->toArray();
                $noComplaints = $allAreas->whereNotIn('area_id', $registeredIds)
                    ->map(fn($p) => (object)['area_name' => $p->area_name]);
                break;
        }

        // Totals
        $totalsAll = [
            'total_registered' => $areaData->sum('total_registered'),
            'total_cancel' => $areaData->sum('total_cancel'),
            'total_solved' => $areaData->sum('total_solved'),
            'total_areas' => ($withComplaints->count() + $noComplaints->count()),
        ];
        $totalsRegistered = [
            'total_areas' => $withComplaints->count(),
        ];


        if ($request->get('export') === 'excel') {
            $filename = "suchnaareawise_report_" . date('Y-m-d_H-i-s') . ".xls";

            header("Content-Type: application/vnd.ms-excel; charset=utf-8");
            header("Content-Disposition: attachment; filename=\"$filename\"");
            header("Pragma: no-cache");
            header("Expires: 0");

            echo "<table border='1'>";

            // Determine complaint type
            $complaintType = $request->get('complaint_type', 'received');

            // Helper for summary label
            $labels = [
                'sambhag' => 'संभाग',
                'jila' => 'जिला',
                'vidhansabha' => 'विधानसभा',
                'mandal' => 'मंडल',
                'nagar' => 'कमांड एरिया',
                'polling' => 'पोलिंग',
                'area' => 'ग्राम/वार्ड चौपाल',
            ];
            $summaryLabel = $labels[$summary] ?? 'क्षेत्र';

            // Header for totals
            echo "<tr>
            <td colspan='5' style='text-align:center;'>";
            if ($complaintType === 'received') {
                echo "कुल सुचना: ({$totalsAll['total_registered']}), 
              कुल निरस्त: ({$totalsAll['total_cancel']}), 
              कुल समाधान: ({$totalsAll['total_solved']})";
            } elseif ($complaintType === 'not_received') {
                echo "अप्राप्त सुचना: कुल {$summaryLabel}: (" . (isset($totalsAll['total_areas']) ? $totalsAll['total_areas'] : 0) . "), 
                पंजीकृत {$summaryLabel}: (" . (isset($totalsRegistered['total_areas']) ? $totalsRegistered['total_areas'] : 0) . ")";
            } else { // all
                echo "कुल सुचना: ({$totalsAll['total_registered']}), 
              कुल निरस्त: ({$totalsAll['total_cancel']}), 
              कुल समाधान: ({$totalsAll['total_solved']})";
            }
            echo "</td></tr>";

            // Column headers


            $counter = 1;

            // Data rows
            if ($complaintType === 'received') {
                echo "<tr>
                    <th>क्र.</th>
                    <th>{$summaryLabel}</th>
                    <th>कुल सुचना</th>
                    <th>कुल निरस्त</th>
                    <th>कुल समाधान</th>
                </tr>";
                foreach ($withComplaints as $row) {
                    echo "<tr>
                    <td>{$counter}</td>
                    <td>{$row->area_name}</td>
                    <td>{$row->total_registered}</td>
                    <td>{$row->total_cancel}</td>
                    <td>{$row->total_solved}</td>
                  </tr>";
                    $counter++;
                }
            }

            if ($complaintType === 'not_received') {
                foreach ($noComplaints->chunk(5) as $chunk) {
                    echo "<tr>";
                    foreach ($chunk as $row) {
                        echo "<td>{$row->area_name}</td>";
                    }
                    $remaining = 5 - count($chunk);
                    for ($i = 0; $i < $remaining; $i++) {
                        echo "<td></td>";
                    }
                    echo "</tr>";
                }
            }

            if ($complaintType === 'all') {
                echo "<tr>
                    <th>क्र.</th>
                    <th>{$summaryLabel}</th>
                    <th>कुल सुचना</th>
                    <th>कुल निरस्त</th>
                    <th>कुल समाधान</th>
                </tr>";
                foreach ($withComplaints as $row) {
                    echo "<tr>
                    <td>{$counter}</td>
                    <td>{$row->area_name}</td>
                    <td>{$row->total_registered}</td>
                    <td>{$row->total_cancel}</td>
                    <td>{$row->total_solved}</td>
                  </tr>";
                    $counter++;
                }
                echo "<tr></tr>";

                echo "<tr>
                    <td colspan='5' style='text-align:center;'>
                    अप्राप्त सुचना: कुल {$summaryLabel}: ({$totalsAll['total_areas']}), 
                        पंजीकृत {$summaryLabel}: ({$totalsRegistered['total_areas']}), 
                    </td>
                </tr>";
                foreach ($noComplaints->chunk(5) as $chunk) {

                    echo "<tr>";
                    foreach ($chunk as $row) {
                        echo "<td>{$row->area_name}</td>";
                    }
                    // Fill remaining columns if chunk < 5
                    $remaining = 5 - count($chunk);
                    for ($i = 0; $i < $remaining; $i++) {
                        echo "<td></td>";
                    }
                    echo "</tr>";
                }
            }

            echo "</table>";
            exit;
        }

        return view('admin.suchnaareawise_report', compact(
            'divisions',
            'areaData',
            'summary',
            'fromDate',
            'toDate',
            'officeType',
            'complaintType',
            'withComplaints',
            'noComplaints',
            'totalsAll',
            'totalsRegistered',
        ));
    }

    public function suchnareferenceReport(Request $request)
    {
        $fromDate   = $request->from_date;
        $toDate     = $request->to_date;
        $officeType = $request->office_type;

        $complaints = Complaint::query()
            ->whereIn('complaint_type', ['अशुभ सुचना', 'शुभ सुचना'])
            ->when($fromDate, fn($q) => $q->whereDate('posted_date', '>=', $fromDate))
            ->when($toDate, fn($q) => $q->whereDate('posted_date', '<=', $toDate))
            ->when($officeType, fn($q) => $q->where('type', $officeType))
            ->get();

        $referenceData = $complaints->groupBy(function ($item) {
            $ref = trim($item->reference_name ?? '');
            return $ref !== '' ? $ref : 'उपलब्ध नहीं है';
        })
            ->map(function ($items, $reference_name) {
                return (object)[
                    'reference'        => $reference_name,
                    'total_registered' => $items->count(),
                    'total_cancel'     => $items->where('complaint_status', 18)->count(),
                    'total_solved'     => $items->whereIn('complaint_status', [13, 14, 15, 16, 17])->count(),
                ];
            })
            ->sortByDesc(fn($row) => $row->total_registered)
            ->values();

        $totals = [
            'total_references' =>  $referenceData->filter(fn($row) => $row->reference !== 'उपलब्ध नहीं है')->count(),
            'total_registered' => $referenceData->sum('total_registered'),
            'total_cancel'     => $referenceData->sum('total_cancel'),
            'total_solved'     => $referenceData->sum('total_solved'),
        ];

        // Excel export
        if ($request->get('export') === 'excel') {
            $filename = "suchnareference_report_" . date('Y-m-d_H-i-s') . ".xls";

            header("Content-Type: application/vnd.ms-excel; charset=utf-8");
            header("Content-Disposition: attachment; filename=\"$filename\"");
            header("Pragma: no-cache");
            header("Expires: 0");

            echo "<table border='1'>";
            echo "<tr>
                <td colspan='5' style='text-align:center;'>
                    कुल संदर्भ: ({$totals['total_references']}), 
                    कुल सुचना: ({$totals['total_registered']}), 
                    कुल निरस्त: ({$totals['total_cancel']}), 
                    कुल समाधान: ({$totals['total_solved']})
                </td>
              </tr>";

            echo "<thead>
                <tr>
                    <th>क्र.</th>
                    <th>संदर्भ</th>
                    <th>कुल सुचना</th>
                    <th>कुल निरस्त</th>
                    <th>कुल समाधान</th>
                </tr>
              </thead><tbody>";

            $counter = 1;
            foreach ($referenceData as $row) {
                echo "<tr>
                    <td>{$counter}</td>
                    <td>{$row->reference}</td>
                    <td>{$row->total_registered}</td>
                    <td>{$row->total_cancel}</td>
                    <td>{$row->total_solved}</td>
                </tr>";
                $counter++;
            }

            echo "</tbody></table>";
            exit;
        }

        return view('admin.suchnareference_report', [
            'referenceData' => $referenceData,
            'totals'        => $totals,
            'fromDate'      => $fromDate,
            'toDate'        => $toDate,
            'officeType'    => $officeType,
        ]);
    }



    public function teamReport(Request $request)
    {
        $managers = DB::table('admin_master')->where('role', 2)->get();
        $offices = DB::table('admin_master')->where('role', 3)->get();
        $fields = DB::table('assign_position as ap')
            ->where('ap.position_id', 8)
            ->join('registration_form as rf', 'ap.member_id', '=', 'rf.registration_id')
            ->select('ap.member_id', 'rf.name')
            ->get();

        $selectedRole = $request->summary ?? null;
        $selectedManagerId = $request->manager_id ?? null;
        $selectedOperatorId = $request->operator_id ?? null;
        $selectedMemberId = $request->commander_id ?? null;

        $fromDate = $request->from_date;
        $toDate = $request->to_date;

        // Load all complaints (filter by posted_date)
        $complaints = Complaint::query()
            ->when($fromDate, fn($q) => $q->whereDate('posted_date', '>=', $fromDate))
            ->when($toDate, fn($q) => $q->whereDate('posted_date', '<=', $toDate))
            ->get();

        $managerReport = [];
        $reportOperator = [];
        $reportMember = [];

        // Generate reports based on selected role
        if ($selectedRole === 'manager') {
            if ($selectedManagerId) {
                $managerReport[$selectedManagerId] = $this->getManagerReport('manager', $selectedManagerId, $request);
            } else {
                foreach ($managers as $m) {
                    $data = $this->getManagerReport('manager', $m->admin_id, $request);
                    if (!empty($data)) $managerReport[$m->admin_id] = $data;
                }
            }
        } elseif ($selectedRole === 'operator') {
            if ($selectedOperatorId) {
                $reportOperator[$selectedOperatorId] = $this->generateOperatorReport('operator', $selectedOperatorId, $request);
            } else {
                foreach ($offices as $op) {
                    $data = $this->generateOperatorReport('operator', $op->admin_id, $request);
                    if (!empty($data)) $reportOperator[$op->admin_id] = $data;
                }
            }
        } elseif ($selectedRole === 'member') {
            if ($selectedMemberId) {
                $reportMember[$selectedMemberId] = $this->getMemberReport($complaints, $selectedMemberId, $request);
            } else {
                foreach ($fields as $f) {
                    $data = $this->getMemberReport($complaints, $f->member_id, $request);
                    if (!empty($data)) $reportMember[$f->member_id] = $data;
                }
            }
        } else {
            $managerReport['Grand Total'] = $this->getManagerReport('manager', null, $request);
            $reportOperator['Grand Total'] = $this->generateOperatorReport('operator', null, $request);
            $memberTotal = [];
            foreach ($fields as $f) {
                $data = $this->getMemberReport($complaints, $f->member_id, $request);
                foreach ($data as $prakar => $values) {
                    if (!isset($memberTotal[$prakar])) $memberTotal[$prakar] = 0;
                    $memberTotal[$prakar] += isset($values['total']) && is_numeric($values['total']) ? $values['total'] : 0;
                }
            }
            $grandMemberTotal = [];
            foreach ($memberTotal as $prakar => $total) {
                $grandMemberTotal[$prakar] = ['total' => $total];
            }
            $reportMember['Grand Total'] = $grandMemberTotal;
        }


        if ($request->get('export') === 'excel') {
            $filename = "team_report_" . date('Y-m-d_H-i-s') . ".xls";

            header("Content-Type: application/vnd.ms-excel; charset=utf-8");
            header("Content-Disposition: attachment; filename=\"$filename\"");
            header("Pragma: no-cache");
            header("Expires: 0");

            function sumFraction($current, $addition)
            {
                if (strpos($current, '/') === false) $current .= '/0';
                if (strpos($addition, '/') === false) $addition .= '/0';
                [$curA, $curB] = explode('/', $current);
                [$addA, $addB] = explode('/', $addition);
                return ($curA + $addA) . '/' . ($curB + $addB);
            }

            // Prepare date range text
            $dateRangeText = '';
            if ($request->from_date || $request->to_date) {
                $fromDateText = $request->from_date ? \Carbon\Carbon::parse($request->from_date)->format('d-m-Y') : '';
                $toDateText = $request->to_date ? \Carbon\Carbon::parse($request->to_date)->format('d-m-Y') : '';
                $dateRangeText = trim("{$fromDateText} से {$toDateText}");
            } else {
                $dateRangeText = \Carbon\Carbon::now()->format('d-m-Y') . ' तक';
            }

            echo "<table border='1'>";

            // Manager Report
            if (!empty($managerReport)) {
                foreach ($managerReport as $managerId => $reports) {
                    $managerName = $managerId === 'Grand Total' ? 'पूर्ण' : $managers->firstWhere('admin_id', $managerId)->admin_name ?? '';
                    echo "<tr><th colspan='10' style='text-align:center; background-color: blanchedalmond;'>मैनेजर रिपोर्ट: {$managerName} ({$dateRangeText})</th></tr>";
                    echo "<tr>
                        <th style='background-color:#e2e3e5;'>प्रकार</th>
                        <th style='background-color:#e2e3e5;'>कुल प्राप्त</th>
                        <th style='background-color:#e2e3e5;'>कुल फॉरवर्ड</th>
                        <th style='background-color:#e2e3e5;'>कुल जवाब दर्ज</th>
                        <th style='background-color:#e2e3e5;'>कुल जवाब आगे भेजे</th>
                        <th style='background-color:#e2e3e5;'>कुल जवाब आगे नहीं भेजे</th>
                        <th style='background-color:#e2e3e5;'>कुल समाधान</th>
                        <th style='background-color:#e2e3e5;'>कुल निरस्त</th>
                        <th style='background-color:#e2e3e5;'>कुल रीव्यू पर</th>
                        <th style='background-color:#e2e3e5;'>कुल अपडेट</th>
                    </tr>";

                    $totals = [
                        'total' => 0,
                        'total_replies' => '0/0',
                        'replies' => 0,
                        'reply_from' => 0,
                        'not_forward' => 0,
                        'total_solved' => 0,
                        'total_cancelled' => 0,
                        'total_reviewed' => '0/0',
                        'total_updates' => 0,
                    ];

                    foreach ($reports as $prakar => $data) {
                        foreach (['total', 'replies', 'reply_from', 'total_solved', 'not_forward', 'total_cancelled', 'total_updates'] as $key) {
                            $totals[$key] += isset($data[$key]) && is_numeric($data[$key]) ? $data[$key] : 0;
                        }
                        foreach (['total_replies', 'total_reviewed'] as $key) {
                            if (isset($data[$key])) $totals[$key] = sumFraction($totals[$key], $data[$key]);
                        }

                        echo "<tr style='text-align:center;'>
                            <td style='mso-number-format:\@; font-weight:bold;'>{$prakar}</td>
                            <td style='mso-number-format:\@;'>{$data['total']}</td>
                            <td style='mso-number-format:\@;'>{$data['total_replies']}</td>
                            <td style='mso-number-format:\@;'>{$data['replies']}</td>
                            <td style='mso-number-format:\@;'>{$data['reply_from']}</td>
                            <td style='mso-number-format:\@;'>{$data['not_forward']}</td>
                            <td style='mso-number-format:\@;'>{$data['total_solved']}</td>
                            <td style='mso-number-format:\@;'>{$data['total_cancelled']}</td>
                            <td style='mso-number-format:\@;'>{$data['total_reviewed']}</td>
                            <td style='mso-number-format:\@;'>{$data['total_updates']}</td>
                        </tr>";
                    }

                    // Totals row
                    echo "<tr style='font-weight:bold; text-align:center;'>
                        <td style='background-color:#e2e3e5;'>कुल</td>
                        <td style='mso-number-format:\\@; background-color:#e2e3e5;'>{$totals['total']}</td>
                        <td style='mso-number-format:\\@; background-color:#e2e3e5;'>" . strval($totals['total_replies']) . "</td>
                        <td style='mso-number-format:\\@; background-color:#e2e3e5;'>{$totals['replies']}</td>
                        <td style='mso-number-format:\\@; background-color:#e2e3e5;'>{$totals['reply_from']}</td>
                        <td style='mso-number-format:\\@; background-color:#e2e3e5;'>{$totals['not_forward']}</td>
                        <td style='mso-number-format:\\@; background-color:#e2e3e5;'>{$totals['total_solved']}</td>
                        <td style='mso-number-format:\\@; background-color:#e2e3e5;'>{$totals['total_cancelled']}</td>
                        <td style='mso-number-format:\\@; background-color:#e2e3e5;'>" . strval($totals['total_reviewed']) . "</td>
                        <td style='mso-number-format:\\@; background-color:#e2e3e5;'>{$totals['total_updates']}</td>
                    </tr>";

                    echo "<tr><td colspan='10'>&nbsp;</td></tr>";
                }
            }

            // Operator Report
            if (!empty($reportOperator)) {
                foreach ($reportOperator as $operatorId => $data) {
                    $operatorName = $operatorId === 'Grand Total' ? 'पूर्ण' : $offices->firstWhere('admin_id', $operatorId)->admin_name ?? '';
                    echo "<tr><th colspan='8' style='text-align:center; background-color: blanchedalmond;'>ऑपरेटर रिपोर्ट: {$operatorName} ({$dateRangeText})</th></tr>";
                    echo "<tr>
                        <th style='background-color:#e2e3e5;'>प्रकार</th>
                        <th style='background-color:#e2e3e5;'>कुल पंजीकृत</th>
                        <th style='background-color:#e2e3e5;'>कुल फ़ॉलोअप</th>
                        <th style='background-color:#e2e3e5;'>पूर्ण फ़ॉलोअप</th>
                        <th style='background-color:#e2e3e5;'>कुल प्राप्त कॉल</th>
                        <th style='background-color:#e2e3e5;'>प्राप्त फ़ॉलोअप प्रतिक्रिया</th>
                        <th style='background-color:#e2e3e5;'>समस्या स्थिति</th>
                        <th style='background-color:#e2e3e5;'>समस्या स्थिति अपडेट</th>
                    </tr>";

                    $totals = [
                        'total' => 0,
                        'followups' => '0/0',
                        'completed_followups' => 0,
                        'overall_incoming' => 0,
                        'incoming_reason1' => '0/0',
                        'incoming_reason2' => 0,
                        'incoming_reason3' => 0,
                    ];

                    foreach ($data as $prakar => $d) {
                        foreach (['total', 'completed_followups', 'overall_incoming', 'incoming_reason2', 'incoming_reason3'] as $key) {
                            $totals[$key] += isset($d[$key]) && is_numeric($d[$key]) ? $d[$key] : 0;
                        }
                        foreach (['followups', 'incoming_reason1'] as $key) {
                            if (isset($d[$key])) $totals[$key] = sumFraction($totals[$key], $d[$key]);
                        }

                        echo "<tr style='text-align:center;'>
                            <td style='mso-number-format:\@; font-weight:bold;'>{$prakar}</td>
                            <td style='mso-number-format:\@;'>{$d['total']}</td>
                            <td style='mso-number-format:\@;'>{$d['followups']}</td>
                            <td style='mso-number-format:\@;'>{$d['completed_followups']}</td>
                            <td style='mso-number-format:\@;'>{$d['overall_incoming']}</td>
                            <td style='mso-number-format:\@;'>{$d['incoming_reason1']}</td>
                            <td style='mso-number-format:\@;'>{$d['incoming_reason2']}</td>
                            <td style='mso-number-format:\@;'>{$d['incoming_reason3']}</td>
                        </tr>";
                    }



                    echo "<tr style='font-weight:bold; text-align:center;'>
                        <td style='background-color:#e2e3e5;'>कुल</td>
                        <td style='mso-number-format:\\@; background-color:#e2e3e5;'>{$totals['total']}</td>
                        <td style='mso-number-format:\\@; background-color:#e2e3e5;'>" . strval($totals['followups']) . "</td>
                        <td style='mso-number-format:\\@; background-color:#e2e3e5;'>{$totals['completed_followups']}</td>
                        <td style='mso-number-format:\\@; background-color:#e2e3e5;'>{$totals['overall_incoming']}</td>
                        <td style='mso-number-format:\\@; background-color:#e2e3e5;'>" . strval($totals['incoming_reason1']) . "</td>
                        <td style='mso-number-format:\\@; background-color:#e2e3e5;'>{$totals['incoming_reason2']}</td>
                        <td style='mso-number-format:\\@; background-color:#e2e3e5;'>{$totals['incoming_reason3']}</td>
                    </tr>";

                    echo "<tr><td colspan='8'>&nbsp;</td></tr>";
                }
            }

            // Member Report
            if (!empty($reportMember)) {
                foreach ($reportMember as $memberId => $memberData) {
                    $memberName = $memberId === 'Grand Total' ? 'पूर्ण' : $fields->firstWhere('member_id', $memberId)->name ?? '';
                    echo "<tr><th colspan='2' style='text-align:center; background-color: blanchedalmond;'>कमांडर रिपोर्ट: {$memberName} ({$dateRangeText})</th></tr>";
                    echo "<tr>
                        <th style='background-color: #e2e3e5;'>प्रकार</th>
                        <th style='background-color: #e2e3e5;'>कुल</th>
                    </tr>";


                    $totals = ['total' => 0];
                    foreach ($memberData as $prakar => $d) {
                        $totals['total'] += $d['total'] ?? 0;
                        echo "<tr style='text-align:center;'>
                            <td style='mso-number-format:\@; font-weight:bold;'>{$prakar}</td>
                            <td style='mso-number-format:\@; background-color: #e2e3e5;'>{$d['total']}</td>
                        </tr>";
                    }
                    echo "<tr style='font-weight:bold; text-align:center; '>
                        <td style='background-color:#e2e3e5;'>कुल</td>
                        <td style='background-color:#e2e3e5;'>{$totals['total']}</td>
                    </tr>";
                    echo "<tr><td colspan='2'>&nbsp;</td></tr>";
                }
            }

            echo "</table>";
            exit;
        }





        return view('admin.team_report', [
            'managerReport' => $managerReport ?? [],
            'reportOperator' => $reportOperator ?? [],
            'reportMember' => $reportMember ?? [],
            'managers' => $managers ?? collect(),
            'offices' => $offices ?? collect(),
            'fields' => $fields ?? collect(),
        ]);
    }

    private function getManagerReport($selectedRole, $selectedManagerId, $request)
    {
        $managerReport = [];
        if ($selectedRole !== 'manager') return $managerReport;

        $fromDate = $request->from_date ? Carbon::parse($request->from_date)->startOfDay() : null;
        $toDate = $request->to_date ? Carbon::parse($request->to_date)->endOfDay() : null;

        $complaints = \App\Models\Complaint::with(['replies'])->get();
        $complaintTypes = $complaints->pluck('complaint_type')->unique();

        foreach ($complaintTypes as $type) {
            $complaintsOfType = $complaints->where('complaint_type', $type);

            // Total complaints filtered by posted_date
            $filteredComplaints = $complaintsOfType->filter(function ($c) use ($fromDate, $toDate) {
                $posted = Carbon::parse($c->posted_date);
                if ($fromDate && $posted->lt($fromDate)) return false;
                if ($toDate && $posted->gt($toDate)) return false;
                return true;
            });
            $totalComplaints = $filteredComplaints->count();


            $displayRepliesCount = $complaintsOfType->sum(function ($c) use ($selectedManagerId, $fromDate, $toDate) {
                return $c->replies->filter(function ($r) use ($selectedManagerId, $fromDate, $toDate) {
                    if ($selectedManagerId && $r->reply_from != $selectedManagerId) return false;

                    $replyDate = Carbon::parse($r->reply_date);
                    if ($fromDate && $replyDate->lt($fromDate)) return false;
                    if ($toDate && $replyDate->gt($toDate)) return false;

                    return true;
                })->count();
            });


            // Total replies
            // Total replies
            $totalReplies = $complaintsOfType->sum(function ($c) use ($selectedManagerId, $fromDate, $toDate) {
                return $c->replies->filter(function ($r) use ($selectedManagerId, $fromDate, $toDate) {
                    if ($r->forwarded_to === null) return false;

                    // Only count replies from other than selected manager if manager selected
                    if ($selectedManagerId && $r->forwarded_to != $selectedManagerId) return false;
                    if ($selectedManagerId && $r->reply_from == $selectedManagerId) return false;

                    $replyDate = Carbon::parse($r->reply_date);
                    if ($fromDate && $replyDate->lt($fromDate)) return false;
                    if ($toDate && $replyDate->gt($toDate)) return false;

                    return true;
                })->count();
            });

            // Complaints with replies
            $complaintsWithReplies = $complaintsOfType->filter(function ($c) use ($selectedManagerId, $fromDate, $toDate) {
                return $c->replies->filter(function ($r) use ($selectedManagerId, $fromDate, $toDate) {
                    if ($r->forwarded_to === null) return false;

                    if ($selectedManagerId && $r->forwarded_to != $selectedManagerId) return false;
                    if ($selectedManagerId && $r->reply_from == $selectedManagerId) return false;

                    $replyDate = Carbon::parse($r->reply_date);
                    if ($fromDate && $replyDate->lt($fromDate)) return false;
                    if ($toDate && $replyDate->gt($toDate)) return false;

                    return true;
                })->isNotEmpty();
            })->count();

            // Replies from manager
            // Total replies from manager (or all if no manager selected) and not forwarded to self
            $totalRepliesFrom = $complaintsOfType->sum(function ($c) use ($selectedManagerId, $fromDate, $toDate) {
                return $c->replies->filter(function ($r) use ($selectedManagerId, $fromDate, $toDate) {
                    if ($r->reply_from === null || $r->forwarded_to === null) return false;

                    // Only include replies from selected manager (or all if none selected)
                    if ($selectedManagerId && $r->reply_from != $selectedManagerId) return false;

                    // Exclude replies where reply_from == forwarded_to
                    if ($r->reply_from == $r->forwarded_to) return false;

                    $replyDate = Carbon::parse($r->reply_date);
                    if ($fromDate && $replyDate->lt($fromDate)) return false;
                    if ($toDate && $replyDate->gt($toDate)) return false;

                    return true;
                })->count();
            });

            // Complaints that have such replies
            $complaintsWithRepliesFrom = $complaintsOfType->filter(function ($c) use ($selectedManagerId, $fromDate, $toDate) {
                return $c->replies->filter(function ($r) use ($selectedManagerId, $fromDate, $toDate) {
                    if ($r->reply_from === null || $r->forwarded_to === null) return false;

                    if ($selectedManagerId && $r->reply_from != $selectedManagerId) return false;

                    if ($r->reply_from == $r->forwarded_to) return false;

                    $replyDate = Carbon::parse($r->reply_date);
                    if ($fromDate && $replyDate->lt($fromDate)) return false;
                    if ($toDate && $replyDate->gt($toDate)) return false;

                    return true;
                })->isNotEmpty();
            })->count();


            // Replies where reply_from != forwarded_to
            // Total replies where reply_from = forwarded_to (filtered by manager if selected)
            $totalRepliesNotForwarded = $complaintsOfType->sum(function ($c) use ($selectedManagerId, $fromDate, $toDate) {
                return $c->replies->filter(function ($r) use ($selectedManagerId, $fromDate, $toDate) {
                    if ($r->reply_from === null || $r->forwarded_to === null) return false;

                    // Only include replies where reply_from = forwarded_to
                    if ($r->reply_from != $r->forwarded_to) return false;

                    // If a manager is selected, only include rows where both equal the selected manager
                    if ($selectedManagerId && $r->reply_from != $selectedManagerId) return false;

                    $replyDate = Carbon::parse($r->reply_date);
                    if ($fromDate && $replyDate->lt($fromDate)) return false;
                    if ($toDate && $replyDate->gt($toDate)) return false;

                    return true;
                })->count();
            });

            // Complaints that have such replies
            $complaintsWithRepliesNotForwarded = $complaintsOfType->filter(function ($c) use ($selectedManagerId, $fromDate, $toDate) {
                return $c->replies->filter(function ($r) use ($selectedManagerId, $fromDate, $toDate) {
                    if ($r->reply_from === null || $r->forwarded_to === null) return false;

                    if ($r->reply_from != $r->forwarded_to) return false;

                    if ($selectedManagerId && $r->reply_from != $selectedManagerId) return false;

                    $replyDate = Carbon::parse($r->reply_date);
                    if ($fromDate && $replyDate->lt($fromDate)) return false;
                    if ($toDate && $replyDate->gt($toDate)) return false;

                    return true;
                })->isNotEmpty();
            })->count();



            // Solved & Cancelled
            $totalSolved = $complaintsOfType->sum(function ($c) use ($selectedManagerId, $type, $fromDate, $toDate) {
                return $c->replies->filter(function ($r) use ($selectedManagerId, $type, $fromDate, $toDate) {
                    if ($selectedManagerId && $r->reply_from != $selectedManagerId) return false;

                    $replyDate = Carbon::parse($r->reply_date);
                    if ($fromDate && $replyDate->lt($fromDate)) return false;
                    if ($toDate && $replyDate->gt($toDate)) return false;

                    if (in_array($type, ['समस्या', 'विकास'])) return $r->complaint_status == 4;
                    if (in_array($type, ['अशुभ सुचना', 'शुभ सुचना'])) return in_array($r->complaint_status, [13, 14, 15, 16]);

                    return false;
                })->count();
            });

            $totalCancelled = $complaintsOfType->sum(function ($c) use ($selectedManagerId, $type, $fromDate, $toDate) {
                return $c->replies->filter(function ($r) use ($selectedManagerId, $type, $fromDate, $toDate) {
                    if ($selectedManagerId && $r->reply_from != $selectedManagerId) return false;

                    $replyDate = Carbon::parse($r->reply_date);
                    if ($fromDate && $replyDate->lt($fromDate)) return false;
                    if ($toDate && $replyDate->gt($toDate)) return false;

                    if (in_array($type, ['समस्या', 'विकास'])) return $r->complaint_status == 5;
                    if (in_array($type, ['अशुभ सुचना', 'शुभ सुचना'])) return $r->complaint_status == 18;

                    return false;
                })->count();
            });

            // Reviewed
            $complaintsWithReview = $complaintsOfType->filter(function ($c) use ($selectedManagerId, $fromDate, $toDate) {
                return $c->replies->filter(function ($r) use ($selectedManagerId, $fromDate, $toDate) {
                    if ($selectedManagerId && $r->reply_from != $selectedManagerId) return false;
                    if (!$r->review_date) return false;

                    $reviewDate = Carbon::parse($r->review_date);
                    if ($fromDate && $reviewDate->lt($fromDate)) return false;
                    if ($toDate && $reviewDate->gt($toDate)) return false;

                    return true;
                })->isNotEmpty();
            })->count();

            $totalRepliesWithReview = $complaintsOfType->sum(function ($c) use ($selectedManagerId, $fromDate, $toDate) {
                return $c->replies->filter(function ($r) use ($selectedManagerId, $fromDate, $toDate) {
                    if ($selectedManagerId && $r->reply_from != $selectedManagerId) return false;
                    if (!$r->review_date) return false;

                    $reviewDate = Carbon::parse($r->review_date);
                    if ($fromDate && $reviewDate->lt($fromDate)) return false;
                    if ($toDate && $reviewDate->gt($toDate)) return false;

                    return true;
                })->count();
            });

            // Updates filtered by created_at
            $totalUpdates = \DB::table('update_complaints')
                ->when($selectedManagerId, fn($q) => $q->where('updated_by', $selectedManagerId))
                ->where('complaint_type', $type)
                ->when($fromDate, fn($q) => $q->whereDate('created_at', '>=', $fromDate))
                ->when($toDate, fn($q) => $q->whereDate('created_at', '<=', $toDate))
                ->count();

            $managerReport[$type] = [
                'total' => $totalComplaints,
                'replies' => $displayRepliesCount,
                'total_replies' => $complaintsWithReplies . ' / ' . $totalReplies,
                // 'reply_from' => $complaintsWithRepliesFrom . ' / ' . $totalRepliesFrom,
                'reply_from' => $totalRepliesFrom,
                // 'not_forward' => $complaintsWithRepliesNotForwarded . ' / ' . $totalRepliesNotForwarded,
                'not_forward' => $totalRepliesNotForwarded,
                'total_solved' => $totalSolved,
                'total_cancelled' => $totalCancelled,
                'total_reviewed' => $complaintsWithReview . ' / ' . $totalRepliesWithReview,
                'total_updates' => $totalUpdates,
            ];
        }

        return $managerReport;
    }


    private function generateOperatorReport($selectedRole, $selectedOperatorId, $request)
    {
        $report = [];
        if ($selectedRole !== 'operator') return $report;

        $fromDate = $request->from_date ? Carbon::parse($request->from_date)->startOfDay() : null;
        $toDate = $request->to_date ? Carbon::parse($request->to_date)->endOfDay() : null;

        $complaints = Complaint::all();
        $complaintTypes = $complaints->pluck('complaint_type')->unique();
        $complaintsById = $complaints->keyBy('complaint_id');

        $operatorComplaintIds = $selectedOperatorId
            ? $complaints->where('complaint_created_by', $selectedOperatorId)->pluck('complaint_id')->all()
            : $complaints->pluck('complaint_id')->all();

        foreach ($complaintTypes as $type) {
            $complaintsOfType = $complaints->where('complaint_type', $type);

            // Total complaints filtered by posted_date and type=2
            $totalComplaints = $complaintsOfType->filter(function ($c) use ($request, $selectedOperatorId, $fromDate, $toDate) {
                if ($c->type != 2) return false;
                if ($selectedOperatorId && $c->complaint_created_by != $selectedOperatorId) return false;

                $posted = Carbon::parse($c->posted_date);
                if ($fromDate && $posted->lt($fromDate)) return false;
                if ($toDate && $posted->gt($toDate)) return false;

                return true;
            })->count();

            // Followups
            $followupsAll = \App\Models\FollowupStatus::query()
                ->whereIn('complaint_id', $operatorComplaintIds)
                ->when($selectedOperatorId, fn($q) => $q->where('followup_created_by', $selectedOperatorId))
                ->when($fromDate, fn($q) => $q->whereDate('followup_date', '>=', $fromDate))
                ->when($toDate, fn($q) => $q->whereDate('followup_date', '<=', $toDate))
                ->get();

            $followupsGrouped = $followupsAll->groupBy(fn($f) => $complaintsById->get($f->complaint_id)?->complaint_type ?? 'unknown');

            $followupGroup = ($followupsGrouped[$type] ?? collect())->filter(function ($f) use ($fromDate, $toDate) {
                $followupDate = Carbon::parse($f->followup_date);
                if ($fromDate && $followupDate->lt($fromDate)) return false;
                if ($toDate && $followupDate->gt($toDate)) return false;
                return true;
            });

            $followupRepliesByType = $followupGroup->count();
            $followupComplaintsByType = $followupGroup->pluck('complaint_id')->unique()->count();
            $completedFollowupsByType = $followupGroup->where('followup_status', 2)->count();

            // Incoming calls
            $incomingAll = \DB::table('incoming_calls')
                ->whereIn('complaint_id', $operatorComplaintIds)
                ->when($selectedOperatorId, fn($q) => $q->where('incoming_created_by', $selectedOperatorId))
                ->when($fromDate, fn($q) => $q->whereDate('created_at', '>=', $fromDate))
                ->when($toDate, fn($q) => $q->whereDate('created_at', '<=', $toDate))
                ->get();

            $incomingGroupedByType = $incomingAll->groupBy(fn($call) => $complaintsById->get($call->complaint_id)?->complaint_type ?? 'unknown');

            $incomingReason1 = collect($incomingGroupedByType[$type] ?? [])->where('reason', 1);
            $incomingReason2 = collect($incomingGroupedByType[$type] ?? [])->where('reason', 2);
            $incomingReason3 = collect($incomingGroupedByType[$type] ?? [])->where('reason', 3);

            $overallIncoming = collect($incomingGroupedByType[$type] ?? []);

            $report[$type] = [
                'total' => $totalComplaints,
                'followups' => $followupComplaintsByType . ' / ' . $followupRepliesByType,
                'completed_followups' => $completedFollowupsByType,
                'incoming_reason1' => count($incomingReason1) . ' / ' . count($incomingReason1),
                'incoming_reason2' => count($incomingReason2),
                'incoming_reason3' => count($incomingReason3),
                'overall_incoming' => $overallIncoming->count(),
            ];
        }

        return $report;
    }

    private function getMemberReport($complaints, $memberId, $request)
    {
        $complaintsFiltered = $complaints
            ->filter(fn($c) => $c->type == 1 && $c->complaint_created_by_member == $memberId);

        $report = [];
        foreach ($complaintsFiltered->groupBy('complaint_type') as $type => $items) {
            $report[$type] = ['total' => $items->count()];
        }
        return $report;
    }


    // activity log functions 
    public function activity_log(Request $request)
    {
        $fromDate = $request->get('from_date', date('Y-m-d'));
        $toDate = $request->get('to_date', date('Y-m-d'));

        $admins = DB::table('admin_master')->where('role', 1)->get();
        $managers = DB::table('admin_master')->where('role', 2)->get();
        $offices = DB::table('admin_master')->where('role', 3)->get();
        $fields = DB::table('assign_position as ap')
            ->where('ap.position_id', 8)
            ->join('registration_form as rf', 'ap.member_id', '=', 'rf.registration_id')
            ->select('ap.member_id', 'rf.name')
            ->get();

        if ($request->ajax()) {
            $query = DB::table('login_history')
                ->leftJoin('admin_master', 'login_history.admin_id', '=', 'admin_master.admin_id')
                ->leftJoin('registration_form', 'login_history.registration_id', '=', 'registration_form.registration_id')
                ->leftJoin('assign_position as ap', 'ap.member_id', '=', 'registration_form.registration_id')
                ->select(
                    'login_history.login_history_id',
                    'login_history.login_date_time',
                    'login_history.logout_date_time',
                    'login_history.ip',
                    'login_history.user_agent',
                    'admin_master.admin_name',
                    'admin_master.role',
                    'registration_form.name as member_name',
                    'ap.position_id'
                )
                ->whereDate('login_history.login_date_time', '>=', $fromDate)
                ->whereDate('login_history.login_date_time', '<=', $toDate);

            if ($request->admin_id) {
                $query->where('admin_master.role', 1)->where('admin_master.admin_id', $request->admin_id);
            }
            if ($request->manager_id) {
                $query->where('admin_master.role', 2)->where('admin_master.admin_id', $request->manager_id);
            }
            if ($request->office_id) {
                $query->where('admin_master.role', 3)->where('admin_master.admin_id', $request->office_id);
            }
            if ($request->field_id) {
                $query->where('ap.position_id', 8)
                    ->where('ap.member_id', $request->field_id);
            }


            $start = $request->input('start', 0);
            $length = $request->input('length', 10);
            $total = $query->count();

            $rows = $query->orderBy('login_date_time', 'desc')
                ->offset($start)
                ->limit($length)
                ->get();

            $data = $rows->map(function ($row) {
                $agent = new Agent();
                $agent->setUserAgent($row->user_agent);

                $row->browser = $agent->browser();
                $row->os = $agent->platform();
                $row->device = $agent->device();

                $ip = $row->ip;
                if (in_array($ip, ['127.0.0.1', '::1'])) {
                    $row->location = "Localhost";
                } else {
                    $location = @file_get_contents("http://ip-api.com/json/{$ip}?fields=status,message,city,regionName,country");
                    if ($location) {
                        $locData = json_decode($location);
                        if (isset($locData->status) && $locData->status === "success") {
                            $city    = $locData->city ?? '';
                            $region  = $locData->regionName ?? '';
                            $country = $locData->country ?? '';
                            $row->location = trim("{$city}, {$region}, {$country}", ", ");
                        } else {
                            $row->location = "Unknown";
                        }
                    } else {
                        $row->location = "Unknown";
                    }
                }

                return $row;
            });

            return response()->json([
                "draw" => intval($request->input('draw')),
                "recordsTotal" => $total,
                "recordsFiltered" => $total,
                "data" => $data,
            ]);
        }

        return view('admin.login_history', compact('fromDate', 'toDate', 'admins', 'managers', 'offices', 'fields'));
    }
}
