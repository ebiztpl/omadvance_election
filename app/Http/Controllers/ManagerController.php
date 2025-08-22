<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Division;
use App\Models\Category;
use App\Models\Interest;
use App\Models\Business;
use App\Models\Education;
use App\Models\Politics;
use App\Models\Religion;
use App\Models\Department;
use Illuminate\Validation\ValidationException;
use App\Models\ComplaintReply;
use App\Models\Adhikari;
use App\Models\User;
use App\Models\Subject;
use App\Models\Designation;
use App\Models\District;
use App\Models\VidhansabhaLokSabha;
use App\Models\RegistrationForm;
use App\Models\Mandal;
use App\Models\Nagar;
use App\Models\Polling;
use App\Models\Area;
use App\Models\Level;
use App\Models\Jati;
use App\Models\Position;
use App\Models\Complaint;
use App\Models\Reply;
use App\Models\JatiwiseVoter;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ManagerController extends Controller
{
    // dashboard functions
    public function dashboard()
    {
        return view('manager/dashboard');
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

    // public function fetchSuchna()
    // {
    //     $today = \Carbon\Carbon::today();
    //     $tomorrow = \Carbon\Carbon::tomorrow();
    //     $weekEnd = \Carbon\Carbon::today()->addDays(6);

    //     $records = Complaint::with('area')
    //         ->whereIn('complaint_type', ['शुभ सुचना', 'अशुभ सुचना'])
    //         ->whereBetween('program_date', [$today, $weekEnd])
    //         ->orderBy('program_date', 'asc')
    //         ->get([
    //             'complaint_id',
    //             'name',
    //             'mobile_number',
    //             'area_id',
    //             'issue_description',
    //             'complaint_type',
    //             'program_date',
    //             'news_time'
    //         ]);

    //     $todayData = [];
    //     $tomorrowData = [];
    //     $weekData = [];

    //     foreach ($records as $row) {
    //         $date = \Carbon\Carbon::parse($row->program_date)->toDateString();

    //         $recordData = $row->toArray();
    //         $recordData['area_name'] = $row->area->area_name ?? 'N/A';

    //         if ($date == $today->toDateString()) {
    //             $todayData[] = $recordData;
    //         } elseif ($date == $tomorrow->toDateString()) {
    //             $tomorrowData[] = $recordData;
    //         }

    //         $weekData[] = $recordData;
    //     }

    //     return response()->json([
    //         'today' => $todayData,
    //         'tomorrow' => $tomorrowData,
    //         'week' => $weekData
    //     ]);
    // }

    public function fetchSuchna()
    {
        $loggedInId = session('user_id');
        if (!$loggedInId) {
            return response()->json(['error' => 'User not logged in.'], 401);
        }

        $today = \Carbon\Carbon::today();
        $tomorrow = \Carbon\Carbon::tomorrow();
        $weekEnd = \Carbon\Carbon::today()->addDays(6);

        // Subquery: get latest reply per complaint
        $latestRepliesSub = \App\Models\Reply::selectRaw('MAX(reply_date) as latest_date, complaint_id')
            ->groupBy('complaint_id');

        // Get the table name from the Complaint model
        $table = (new \App\Models\Complaint)->getTable();

        // Get complaints forwarded to this manager
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

    // public function fetchStatus()
    // {
    //     $statusLabels = [
    //         1 => 'शिकायत दर्ज',
    //         2 => 'प्रक्रिया में',
    //         3 => 'स्थगित',
    //         4 => 'पूर्ण',
    //         5 => 'रद्द',
    //     ];

    //     $data = DB::table('complaint')
    //         ->select('complaint_status', DB::raw('COUNT(*) as total'))
    //         ->whereNotNull('complaint_status')
    //         ->where('complaint_status', '!=', '')
    //         ->whereIn('complaint_type', ['समस्या', 'विकास'])
    //         ->groupBy('complaint_status')
    //         ->orderBy('total', 'DESC')
    //         ->get()
    //         ->map(function ($item) use ($statusLabels) {
    //             return [
    //                 'status' => $statusLabels[$item->complaint_status] ?? 'अन्य',
    //                 'total' => $item->total
    //             ];
    //         });

    //     return response()->json($data);
    // }

    // public function fetchStatus(Request $request)
    // {
    //     $statusLabels = [
    //         1 => 'शिकायत दर्ज',
    //         2 => 'प्रक्रिया में',
    //         3 => 'स्थगित',
    //         4 => 'पूर्ण',
    //         5 => 'रद्द',
    //     ];

    //     $query = DB::table('complaint')
    //         ->select('complaint_status', DB::raw('COUNT(*) as total'))
    //         ->whereNotNull('complaint_status')
    //         ->where('complaint_status', '!=', '')
    //         ->whereIn('complaint_type', ['समस्या', 'विकास']);


    //     $filter = $request->input('filter', 'all');

    //     switch ($filter) {
    //         case 'आज':
    //             $query->whereDate('posted_date', Carbon::today());
    //             break;
    //         case 'कल':
    //             $query->whereDate('posted_date', Carbon::yesterday());
    //             break;
    //         case 'पिछला सप्ताह':
    //             $query->whereBetween('posted_date', [Carbon::now()->subWeek(), Carbon::now()]);
    //             break;
    //         case 'पिछला माह':
    //             $query->whereBetween('posted_date', [Carbon::now()->subMonth(), Carbon::now()]);
    //             break;
    //     }

    //     $data = $query
    //         ->groupBy('complaint_status')
    //         ->orderBy('total', 'DESC')
    //         ->get()
    //         ->map(function ($item) use ($statusLabels) {
    //             return [
    //                 'status' => $statusLabels[$item->complaint_status] ?? 'अन्य',
    //                 'total' => $item->total
    //             ];
    //         });

    //     return response()->json($data);
    // }


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

    // public function getForwardedComplaintsPerManager()
    // {
    //     $loggedInId = session('user_id');

    //     if (!$loggedInId) {
    //         return response()->json(['error' => 'User not logged in.'], 401);
    //     }

    //     // Step 1: Subquery to get latest replies
    //     $latestRepliesSub = \App\Models\Reply::selectRaw('MAX(reply_date) as latest_date, complaint_id')
    //         ->groupBy('complaint_id');

    //     // Step 2: Join to get latest forwarded replies with valid complaint types and statuses
    //     $latestReplies = \App\Models\Reply::joinSub($latestRepliesSub, 'latest', function ($join) {
    //         $join->on('complaint_reply.reply_date', '=', 'latest.latest_date')
    //             ->on('complaint_reply.complaint_id', '=', 'latest.complaint_id');
    //     })
    //         ->whereNotNull('forwarded_to')
    //         ->whereHas('complaint', function ($query) {
    //             $query->whereNotIn('complaint_status', [4, 5])
    //                 ->whereIn('complaint_type', ['समस्या', 'विकास']);
    //         })
    //         ->select('forwarded_to', DB::raw('COUNT(DISTINCT complaint_reply.complaint_id) as total'))
    //         ->groupBy('forwarded_to')
    //         ->get();

    //     // Step 3: Get all manager users except the logged-in one
    //     $managers = \App\Models\User::where('role', 2)
    //         ->where('admin_id', '!=', $loggedInId)
    //         ->get(['admin_id', 'admin_name']);

    //     // Step 4: Merge count with manager names
    //     $result = $managers->map(function ($manager) use ($latestReplies) {
    //         $count = $latestReplies->firstWhere('forwarded_to', $manager->admin_id);
    //         return [
    //             'forward' => $manager->admin_name,
    //             'total' => $count ? $count->total : 0,
    //         ];
    //     });

    //     return response()->json($result);
    // }

    public function getForwardedComplaintsPerManager()
    {
        // $loggedInId = session('user_id');

        // if (!$loggedInId) {
        //     return response()->json(['error' => 'User not logged in.'], 401);
        // }

        $latestRepliesSub = \App\Models\Reply::selectRaw('MAX(reply_date) as latest_date, complaint_id')
            ->groupBy('complaint_id');

        // Join to fetch only latest reply per complaint
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

        // Count complaints by manager + type
        $counts = $latestReplies->groupBy(['forwarded_to', fn($item) => $item->complaint->complaint_type]);

        // All managers except logged-in one
        $managers = \App\Models\User::where('role', 2)
            // ->where('admin_id', '!=', $loggedInId)
            ->get(['admin_id', 'admin_name']);

        // Build result structure
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
                    'आज' => [Carbon::today(), Carbon::today()],
                    'कल' => [Carbon::yesterday(), Carbon::yesterday()],
                    'पिछले सात दिन' => [Carbon::now()->subWeek(), Carbon::now()],
                    'पिछले तीस दिन' => [Carbon::now()->subMonth(), Carbon::now()],
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
                    'आज' => [Carbon::today(), Carbon::today()],
                    'कल' => [Carbon::yesterday(), Carbon::yesterday()],
                    'पिछले सात दिन' => [Carbon::now()->subWeek(), Carbon::now()],
                    'पिछले तीस दिन' => [Carbon::now()->subMonth(), Carbon::now()],
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

        return view('manager/dashboard_details', compact('complaints', 'title', 'section'));
    }

    private function getComplaintsBetween($start, $end, $type = null, $user = null)
    {
        $query = Complaint::with(['division', 'district', 'vidhansabha', 'mandal', 'gram', 'polling', 'area', 'registrationDetails', 'admin'])
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

        return view('manager/contact_voter_details', compact('title'));
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



    // division master controller functions
    public function index()
    {
        $divisions = Division::all();
        return view('manager/division_master', compact('divisions'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'city_name' => 'required|unique:division_master,division_name'
        ]);

        Division::create(['division_name' => $request->city_name]);

        return redirect()->back()->with('insert_msg', 'संभाग जोड़ा गया!');
    }

    public function edit($id)
    {
        $division = Division::findOrFail($id);
        return view('manager/edit_division', compact('division'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'city_name' => 'required|unique:division_master,division_name,' . $id . ',division_id'
        ]);

        $division = Division::findOrFail($id);
        $division->update(['division_name' => $request->city_name]);

        return redirect()->route('division.index')->with('update_msg', 'संभाग अपडेट किया गया!');
    }

    public function destroy($id)
    {
        Division::destroy($id);
        return redirect()->back()->with('delete_msg', 'संभाग हटाया गया!');
    }

    // city master controller functions
    public function cityMaster()
    {
        $divisions = DB::table('division_master')->get();

        $districts = DB::table('district_master as d')
            ->leftJoin('division_master as v', 'v.division_id', '=', 'd.division_id')
            ->select('d.district_id', 'd.district_name', 'v.division_name')
            ->orderBy('d.division_id')
            ->get();

        return view('manager/city_master', compact('divisions', 'districts'));
    }

    public function storeCity(Request $request)
    {
        $request->validate([
            'division_id' => 'required|exists:division_master,division_id',
            'district_name' => 'required|string|max:255'
        ]);

        DB::table('district_master')->insert([
            'division_id' => $request->division_id,
            'district_name' => $request->district_name
        ]);

        return redirect()->route('city.master')->with('success', 'जिला जोड़ा गया!');
    }

    public function editCity($id)
    {
        $district = District::findOrFail($id);
        $divisions = Division::all();

        return view('manager/edit_city', compact('district', 'divisions'));
    }

    public function updateCity(Request $request, $id)
    {
        $request->validate([
            'division_id' => 'required|exists:division_master,division_id',
            'district_name' => 'required|string|max:255'
        ]);

        $district = District::findOrFail($id);
        $district->update([
            'division_id' => $request->division_id,
            'district_name' => $request->district_name
        ]);

        return redirect()->route('city.master')->with('update_msg', 'जिला अपडेट किया गया!');
    }

    // vidhansabha loksabha master controller functions
    public function indexVidhansabha()
    {
        $districts = District::orderBy('district_name')->get();
        $records = VidhansabhaLokSabha::with('district')->orderBy('district_id')->get();
        return view('manager/vidhansabha_loksabha_master', compact('districts', 'records'));
    }

    public function storeVidhansabha(Request $request)
    {
        $request->validate([
            'district_id.*'   => 'required|exists:district_master,district_id',
            'vidhansabha.*'   => 'required|string|max:255',
            'loksabha.*'      => 'required|string|max:255',
        ]);

        foreach ($request->district_id as $i => $districtId) {
            VidhansabhaLokSabha::create([
                'district_id'   => $districtId,
                'vidhansabha'   => $request->vidhansabha[$i],
                'loksabha'      => $request->loksabha[$i],
            ]);
        }

        return redirect()->route('vidhansabha.index')->with('success', 'विधानसभा/लोकसभा जोड़ा गया!');
    }

    public function editVidhansabha($id)
    {
        $entry = VidhansabhaLokSabha::findOrFail($id);
        $districts = District::orderBy('district_name')->get();
        return view('manager/edit_vidhansabha_loksabha', compact('entry', 'districts'));
    }

    public function updateVidhansabha(Request $request, $id)
    {
        $request->validate([
            'district_id'   => 'required|exists:district_master,district_id',
            'vidhansabha'   => 'required|string|max:255',
            'loksabha'      => 'required|string|max:255',
        ]);

        $entry = VidhansabhaLokSabha::findOrFail($id);
        $entry->update([
            'district_id'   => $request->district_id,
            'vidhansabha'   => $request->vidhansabha,
            'loksabha'      => $request->loksabha,
        ]);

        return redirect()->route('vidhansabha.index')->with('update_msg', 'विधानसभा/लोकसभा अपडेट किया गया!');
    }

    // mandal master controller functions
    public function mandalindex()
    {
        $districts = DB::table('district_master')->get();
        $mandals = Mandal::with('vidhansabha')->get();

        return view('manager/mandal_master', compact('districts', 'mandals'));
    }

    public function mandalstore(Request $request)
    {
        $request->validate([
            'txtvidhansabha' => 'required|exists:vidhansabha_loksabha,vidhansabha_id',
            'txtmadal' => 'required|string|max:255'
        ]);

        Mandal::create([
            'vidhansabha_id' => $request->txtvidhansabha,
            'mandal_name' => $request->txtmadal
        ]);

        return redirect()->route('mandal.index')->with('success', 'मंडल जोड़ा गया!');
    }

    public function mandaledit($id)
    {
        $mandal = Mandal::findOrFail($id);
        $districts = DB::table('district_master')->get();

        $district_id = DB::table('vidhansabha_loksabha')
            ->where('vidhansabha_id', $mandal->vidhansabha_id)
            ->value('district_id');

        $vidhansabhas = DB::table('vidhansabha_loksabha')
            ->where('district_id', $district_id)
            ->get();
        return view('manager/edit_mandal', compact('mandal', 'districts', 'vidhansabhas'));
    }

    public function mandalupdate(Request $request, $id)
    {
        $request->validate([
            'txtvidhansabha' => 'required|exists:vidhansabha_loksabha,vidhansabha_id',
            'txtmadal' => 'required|string|max:255'
        ]);

        $mandal = Mandal::findOrFail($id);
        $mandal->update([
            'vidhansabha_id' => $request->txtvidhansabha,
            'mandal_name' => $request->txtmadal
        ]);

        return redirect()->route('mandal.index')->with('update_msg', 'मंडल अपडेट किया गया!');
    }

    public function getVidhansabha(Request $request)
    {
        $district_id = $request->id;

        $vidhansabhas = DB::table('vidhansabha_loksabha')
            ->where('district_id', $district_id)
            ->get();

        $options = "<option value=''>--Select Vidhansabha--</option>";
        foreach ($vidhansabhas as $v) {
            $options .= '<option value="' . $v->vidhansabha_id . '">' . $v->vidhansabha . '</option>';
        }

        return response()->json(['options' => $options]);
    }

    // nagar kendra master controller functions
    public function nagarIndex()
    {
        $districts = DB::table('district_master')->get();
        $mandals = DB::table('mandal')->get();
        $nagar_list = DB::table('nagar_master')->get();

        $dash = [];

        foreach ($nagar_list as $key => $nagar) {
            $mandal = DB::table('mandal')->where('mandal_id', $nagar->mandal_id)->first();

            // Replaced match with if-else for PHP 7 compatibility
            if ($nagar->mandal_type == 1) {
                $mandal_tyname = 'ग्रामीण मंडल';
            } elseif ($nagar->mandal_type == 2) {
                $mandal_tyname = 'नगर मंडल';
            } else {
                $mandal_tyname = '';
            }

            $nagarList[] = [
                'sr' => $key + 1,
                'mandal_name' => $mandal->mandal_name ?? '',
                'mandal_tyname' => $mandal_tyname,
                'gram_name' => $nagar->nagar_name,
                'nagar_id' => $nagar->nagar_id
            ];
        }

        return view('manager/gram_master', compact('districts', 'nagarList'));
    }

    public function nagarStore(Request $request)
    {
        $mandalId = $request->txtmandal;
        $gramNames = $request->gram_name;
        $mandalType = $request->mandal_type;
        $date = now();

        foreach ($gramNames as $name) {
            $exists = DB::table('nagar_master')
                ->where('mandal_id', $mandalId)
                ->where('nagar_name', $name)
                ->exists();

            if ($exists) {
                return redirect()->back()->with('error', 'ये नगर और मंडल पहले से हैं');
            }

            DB::table('nagar_master')->insert([
                'mandal_id' => $mandalId,
                'mandal_type' => $mandalType,
                'nagar_name' => $name,
                'post_date' => $date,
            ]);
        }

        return redirect()->route('nagar.index')->with('success', 'नगर केंद्र/ग्राम केंद्र जोड़ा गया!');
    }

    public function nagarEdit($id)
    {
        $nagar = Nagar::findOrFail($id);
        $districts = DB::table('district_master')->get();
        $mandals = Mandal::all();

        return view('manager/edit_gram', compact('nagar', 'districts', 'mandals'));
    }

    public function nagarUpdate(Request $request, $id)
    {
        $request->validate([
            'txtmandal' => 'required|exists:mandal,mandal_id',
            'mandal_type' => 'required|in:1,2',
            'gram_name' => 'required|string|max:255'
        ]);

        $nagar = Nagar::findOrFail($id);
        $nagar->update([
            'mandal_id' => $request->txtmandal,
            'mandal_type' => $request->mandal_type,
            'nagar_name' => $request->gram_name
        ]);

        return redirect()->route('nagar.index')->with('update_msg', 'नगर केंद्र/ग्राम केंद्र अपडेट किया गया!');
    }

    public function getMandal(Request $request)
    {
        $id = $request->id;
        $data = DB::table('mandal')
            ->where('vidhansabha_id', $id)
            ->pluck('mandal_name', 'mandal_id');

        $options = '<option value="">--Select Mandal--</option>';
        foreach ($data as $key => $value) {
            $options .= "<option value='$key'>$value</option>";
        }

        return response()->json(['options' => $options]);
    }


    // polling master controller functions
    public function pollingIndex()
    {
        $districts = District::all();
        $pollings = Polling::with(['mandal', 'nagar'])->get()->map(fn($p, $i) => [
            'sr' => $i + 1,
            'gram_polling_id' => $p->gram_polling_id,
            'mandal_name' => $p->mandal->mandal_name ?? '',
            'mandal_tyname' => $p->nagar->mandal_type == 1 ? 'ग्रामीण' : 'नगर',
            'gram_name' => $p->nagar->nagar_name,
            'polling_name' => $p->polling_name,
            'polling_number' => $p->polling_no,
        ]);

        return view('manager/polling_master', compact('districts', 'pollings'));
    }

    public function pollingStore(Request $r)
    {
        $r->validate([
            'district_id' => 'required|exists:district_master,district_id',
            'vidhansabha_id' => 'required|exists:vidhansabha_loksabha,vidhansabha_id',
            'mandal_id' => 'required|exists:mandal,mandal_id',
            'nagar_id' => 'required|exists:nagar_master,nagar_id',
            'polling_name.*' => 'required|string',
            'polling_number.*' => 'required|string',
        ]);

        foreach ($r->polling_name as $i => $name) {
            if (Polling::where('polling_name', $name)
                ->where('polling_no', $r->polling_number[$i])
                ->exists()
            ) {
                return back()->with('error', 'Duplicate polling entry.');
            }
            Polling::create([
                'mandal_id' => $r->mandal_id,
                'nagar_id' => $r->nagar_id,
                'polling_name' => $name,
                'polling_no' => $r->polling_number[$i],
                'post_date' => now(),
            ]);
        }

        return redirect()->route('polling.index')->with('success', 'मतदान केंद्र/क्रमांक जोड़ा गया!');
    }

    public function pollingEdit($id)
    {
        $polling = Polling::where('gram_polling_id', $id)->firstOrFail();

        $districts = District::all();
        $vidhansabhas = VidhansabhaLokSabha::where('district_id', $polling->mandal->vidhansabha_id)->get();
        $mandals = Mandal::where('vidhansabha_id', $polling->mandal->vidhansabha_id)->get();
        $nagars = Nagar::where('mandal_id', $polling->mandal_id)->get();
        return view('manager/edit_polling', compact('polling', 'districts', 'vidhansabhas', 'mandals', 'nagars'));
    }

    public function pollingUpdate(Request $r, $id)
    {
        $r->validate([
            'polling_name' => 'required|string',
            'polling_no' => 'required|string',
        ]);

        if (Polling::where('polling_name', $r->polling_name)
            ->where('polling_no', $r->polling_no)
            ->where('gram_polling_id', '<>', $id)
            ->exists()
        ) {
            return back()->with('error', 'Duplicate polling entry.');
        }

        Polling::where('gram_polling_id', $id)->update([
            'polling_name' => $r->polling_name,
            'polling_no' => $r->polling_no,
        ]);

        return redirect()->route('polling.index')->with('update_msg', 'मतदान केंद्र/क्रमांक अपडेट किया गया!');
    }

    public function getNagar(Request $r)
    {
        $options = Nagar::where('mandal_id', $r->id)
            ->pluck('nagar_name', 'nagar_id')
            ->map(fn($v, $k) => "<option value='$k'>$v</option>")
            ->prepend('<option value="">--Select--</option>')
            ->implode('');
        return response()->json(['options' => $options]);
    }


    // area master controller functions
    public function areaIndex()
    {
        $vidhansabhas = VidhansabhaLokSabha::all();
        $areas = Area::with('polling.nagar.mandal')->get();
        return view('manager/area_master', compact('vidhansabhas', 'areas'));
    }

    public function areaStore(Request $r)
    {
        $r->validate([
            'polling_id' => 'required|exists:gram_polling,gram_polling_id',
            'area_name.*' => 'required|string|max:255',
        ]);

        foreach ($r->area_name as $name) {
            Area::firstOrCreate([
                'polling_id' => $r->polling_id,
                'area_name' => $name
            ], [
                'post_date' => now()
            ]);
        }

        return redirect()->route('area.index')->with('success', 'मतदान क्षेत्र जोड़ा गया!');
    }

    public function ajax(Request $r)
    {
        $type = $r->type;
        $id = $r->id;
        $html = '<option value="">--Select--</option>';

        if ($type === 'mandal') {
            $data = Mandal::where('vidhansabha_id', $id)->pluck('mandal_name', 'mandal_id');
        } elseif ($type === 'nagar') {
            $data = Nagar::where('mandal_id', $id)->pluck('nagar_name', 'nagar_id');
        } elseif ($type === 'polling') {
            $data = Polling::where('nagar_id', $id)
                ->selectRaw("gram_polling_id, CONCAT(polling_name, ' - ', polling_no) as name")
                ->pluck('name', 'gram_polling_id');
        }

        foreach ($data ?? [] as $id => $name) {
            $html .= "<option value='{$id}'>{$name}</option>";
        }

        return response()->json(['html' => $html]);
    }

    public function areaEdit($id)
    {
        $area = Area::findOrFail($id);
        return view('manager/edit_area', compact('area'));
    }

    public function areaUpdate(Request $r, $id)
    {
        $r->validate(['area_name' => 'required|string|max:255']);
        $area = Area::findOrFail($id);
        $area->update(['area_name' => $r->area_name]);
        return redirect()->route('area.index')->with('update_msg', 'मतदान क्षेत्र अपडेट किया गया!');
    }


    // level master controller functions
    public function levelIndex()
    {
        $levels = Level::with('parent')->get();
        return view('manager/level_master', compact('levels'));
    }

    public function levelStore(Request $r)
    {
        $r->validate([
            'level_name' => 'required|string|max:255',
            'ref_level_id' => 'nullable|exists:level_master,level_id',
        ]);

        Level::create([
            'level_name' => $r->level_name,
            'ref_level_id' => $r->ref_level_id,
            'post_date' => now()->toDateString(),
        ]);

        return redirect()->route('level.index')->with('success', 'कार्य क्षेत्र जोड़ा गया!');
    }

    public function levelEdit($id)
    {
        $level = Level::findOrFail($id);
        $parents = Level::where('level_id', '<>', $id)->get();
        return view('manager/edit_level', compact('level', 'parents'));
    }

    public function levelUpdate(Request $r, $id)
    {
        $r->validate([
            'level_name' => 'required|string|max:255',
            'ref_level_id' => 'nullable|exists:level_master,level_id',
        ]);

        $lvl = Level::findOrFail($id);
        $lvl->update([
            'level_name' => $r->level_name,
            'ref_level_id' => $r->ref_level_id,
        ]);

        return redirect()->route('level.index')->with('update_msg', 'कार्य क्षेत्र अपडेट किया गया!');
    }

    // position master controller functions
    public function positionIndex(Request $request)
    {
        $positions = Position::orderBy('position_id')->get();
        return view('manager/position_master', compact('positions'));
    }

    public function positionStore(Request $request)
    {
        $request->validate([
            'position_name' => 'required|string|max:255',
            'level' => 'required|integer|between:1,10',
        ]);

        Position::create([
            'position_name' => $request->position_name,
            'level' => $request->level,
            'post_date' => now()->toDateString(),
        ]);

        return redirect()->route('positions.index')->with('success', 'दायित्व जोड़ा गया!');
    }

    public function positionEdit($id)
    {
        $position = Position::findOrFail($id);
        return view('manager/edit_position', compact('position'));
    }

    public function positionUpdate(Request $request, $id)
    {
        $request->validate([
            'position_name' => 'required|string|max:255',
            'level' => 'required|integer|between:1,10',
        ]);

        $position = Position::findOrFail($id);
        $position->update([
            'position_name' => $request->position_name,
            'level' => $request->level,
        ]);

        return redirect()->route('positions.index')->with('update_msg', 'दायित्व अपडेट किया गया!');
    }


    // jati master controller functions
    public function jatiIndex()
    {
        $jatis = Jati::all();
        return view('manager/jati_master', compact('jatis'));
    }

    public function jatiStore(Request $request)
    {
        $request->validate([
            'jati_name' => 'required|unique:jati_master,jati_name',
        ]);

        $exists = Jati::where('jati_name', $request->jati_name)->exists();
        if ($exists) {
            return redirect()->back()->with('error', 'जाति पहले से मौजूद है');
        }

        Jati::create([
            'jati_name' => $request->jati_name,
        ]);

        return redirect()->route('jati.index')->with('success', 'जाति सफलतापूर्वक जोड़ी गई!');
    }

    public function jatiEdit($id)
    {
        $jati = Jati::findOrFail($id);
        return view('manager/edit_jati', compact('jati'));
    }

    public function jatiUpdate(Request $request, $id)
    {
        $request->validate([
            'jati_name' => 'required|unique:jati_master,jati_name,' . $id . ',jati_id',
        ]);

        $jati = Jati::findOrFail($id);
        $jati->update([
            'jati_name' => $request->jati_name,
        ]);

        return redirect()->route('jati.index')->with('success', 'जाति सफलतापूर्वक अपडेट की गई');
    }

    // jati polling controller functions
    public function jatiPollingIndex()
    {
        $areas = Area::with('polling')->get();
        $districts = District::all();
        $jatis = Jati::all();

        return view('manager/jati_polling', compact('areas', 'districts', 'jatis'));
    }

    public function jatiPollingStore(Request $request)
    {
        $request->validate([
            'txtvidhansabha' => 'required',
            'txtmandal' => 'required',
            'txtgram' => 'required',
            'txtpolling' => 'required',
            'jati_id.*' => 'required|exists:jati_master,jati_id',
            'total_voter.*' => 'required|integer|min:1',
        ]);

        foreach ($request->jati_id as $i => $jatiId) {
            JatiwiseVoter::create([
                'vidhansabha_id' => $request->txtvidhansabha,
                'mandal_id' => $request->txtmandal,
                'gram_id' => $request->txtgram,
                'polling_id' => $request->txtpolling,
                'jati_id' => $jatiId,
                'voter_total' => $request->total_voter[$i],
                'post_date' => now(),
            ]);
        }

        return redirect()->route('jati_polling.index')->with('success', 'जातिगत मतदाता सफलतापूर्वक जोड़ी गई!');
    }


    public function getPolling(Request $request)
    {
        $pollings = Polling::where('nagar_id', $request->id)
            ->select('gram_polling_id as id', DB::raw("CONCAT(polling_no, ' - ', polling_name) as name"))
            ->get();

        return response()->json($pollings);
    }

    public function getGrams(Request $request)
    {
        $nagar = Nagar::where('mandal_id', $request->mandalid)
            ->where('mandal_type', $request->mandal_type)
            ->select('nagar_id as id', 'nagar_name as name')
            ->get();

        return response()->json($nagar);
    }


    // jatiwise members controller functions
    public function jatiwiseIndex()
    {
        $areas = Area::with('polling')->get();
        $districts = District::all();
        $jatis = Jati::all();
        $vidhansabhas = VidhansabhaLokSabha::where('district_id', 11)->get();
        return view('manager/jatiwise_members', compact('areas', 'districts', 'jatis', 'vidhansabhas'));
    }

    public function getDropdown(Request $request)
    {
        switch ($request->type) {
            case 'vidhansabha':
                return VidhansabhaLokSabha::where('district_id', $request->id)->get();
            case 'mandal':
                return Mandal::where('vidhansabha_id', $request->id)->get();
            case 'gram':
                return Nagar::where('mandal_id', $request->id)->get();
            case 'polling':
                return Polling::where('nagar_id', $request->id)->get();
        }
        return [];
    }

    public function searchJatiwise(Request $request)
    {
        $where = [];

        if ($request->vidhansabha_id) {
            $where[] = ['vidhansabha_id', '=', $request->vidhansabha_id];
        }

        if ($request->mandal_id) {
            $where[] = ['mandal_id', '=', $request->mandal_id];
        }

        if ($request->gram_id) {
            $where[] = ['gram_id', '=', $request->gram_id];
        }

        if ($request->polling_id) {
            $where[] = ['polling_id', '=', $request->polling_id];
        }

        $voters = DB::table('jatiwise_voter')
            ->select(DB::raw('SUM(voter_total) as total'), 'jati_id')
            ->when(!empty($where), function ($query) use ($where) {
                foreach ($where as $condition) {
                    $query->where($condition[0], $condition[1], $condition[2]);
                }
            })
            ->groupBy('jati_id')
            ->orderByDesc('total')
            ->get();

        $html = "<div class='row'>";

        foreach ($voters as $voter) {
            $jati = DB::table('jati_master')->where('jati_id', $voter->jati_id)->first();
            $jatiName = $jati->jati_name ?? 'Unknown';

            $html .= "<div class='col-sm-3 p-2 text-center' style='border:solid 1px #e4e4e4;'>
                    <h1>{$jatiName}</h1>
                    <h3 style='color:blue; font-size:30px;'>{$voter->total}</h3>
                  </div>";
        }

        $html .= "</div>";

        return response($html);
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


    public function viewCommanderComplaints(Request $request)
    {
        $query = Complaint::with('registrationDetails', 'replies.forwardedToManager')->where('type', 1)->whereIn('complaint_type', ['समस्या', 'विकास']);

        if ($request->filled('complaint_status')) {
            $query->where('complaint_status', $request->complaint_status);
        }

        if ($request->filled('complaint_type')) {
            $query->where('complaint_type', $request->complaint_type);
        } else if (!$request->has('filter')) {
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

                case 'critical':
                    $query->whereHas('replies', function ($q) {
                        $q->whereNotNull('criticality')
                            ->whereRaw('reply_date = (
                                    SELECT MAX(reply_date)
                                    FROM complaint_reply
                                    WHERE complaint_reply.complaint_id = complaint.complaint_id
                                )');
                    })->orderByRaw("FIELD(
                                (SELECT criticality 
                                FROM complaint_reply 
                                WHERE complaint_reply.complaint_id = complaint.complaint_id 
                                ORDER BY reply_date DESC 
                                LIMIT 1),
                                'अत्यधिक', 'मध्यम', 'कम'
                            )");
                    break;

                case 'closed': 
                    $query->where('complaint_status', 4);
                    break;

                case 'cancel': 
                    $query->where('complaint_status', 5);
                    break;

                case 'forwarded_manager':
                    $loggedInId = session('user_id'); 

                    $query->whereHas('replies', function ($q) use ($loggedInId) {
                        $q->where('forwarded_to', $loggedInId)
                            ->whereRaw('reply_date = (
                        SELECT MAX(reply_date)
                        FROM complaint_reply
                        WHERE complaint_reply.complaint_id = complaint.complaint_id
                    )');
                    });
                    break;

            }
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
            $complaint->forwarded_reply_date = $lastReply?->reply_date?->format('d-m-Y h:i') ?? '-';
        }

        if ($request->ajax()) {
            $html = '';

            foreach ($complaints as $index => $complaint) {
                $html .= '<tr>';
                $html .= '<td>' . ($index + 1) . '</td>';
                $html .= '<td>
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

                $html .= '<td>' . ($complaint->registrationDetails->name ?? '') . '</td>';

                $html .= '<td>' . ($complaint->forwarded_to_name ?? '-') . '<br>' . ($complaint->forwarded_reply_date ?? '-') . '</td>';

                // Action Button
                if ($complaint->complaint_type === 'शुभ सुचना' || $complaint->complaint_type === 'अशुभ सुचना') {
                    $html .= '<td><a href="' . route('suchna_show.details', $complaint->complaint_id) . '" class="btn btn-sm btn-primary" style="white-space: nowrap;">क्लिक करें</a></td>';
                } elseif ($complaint->complaint_type === 'समस्या' || $complaint->complaint_type === 'विकास') {
                    $html .= '<td><a href="' . route('complaints_show.details', $complaint->complaint_id) . '" class="btn btn-sm btn-primary" style="white-space: nowrap;">क्लिक करें</a></td>';
                }

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

        return view('manager/commander_complaints', compact(
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

    // public function viewOperatorComplaints(Request $request)
    // {
    //     $complaints = Complaint::where('type', 2)->get();

    //     foreach ($complaints as $complaint) {
    //         $admin = DB::table('admin_master')->where('admin_id', $complaint->complaint_created_by)->first();
    //         $complaint->admin_name = $admin->admin_name ?? '';
    //     }

    //     foreach ($complaints as $complaint) {
    //         if (!in_array($complaint->complaint_status, [4, 5])) {
    //             $complaint->pending_days = Carbon::parse($complaint->posted_date)->diffInDays(now());
    //         } else {
    //             $complaint->pending_days = 0;
    //         }
    //     }

    //     return view('manager/operator_complaints', compact('complaints'));
    // }

    public function viewOperatorComplaints(Request $request)
    {
        // $vidhansabhaId = 49;
        // $districtId = 11;
        // $divisionId = 2;

        $query = Complaint::with('registrationDetails', 'replies.forwardedToManager')->where('type', 2)->whereIn('complaint_type', ['समस्या', 'विकास']);
        // ->where('district_id', $districtId)
        // ->where('vidhansabha_id', $vidhansabhaId);

        // Filters

        if ($request->filled('complaint_status')) {
            $query->where('complaint_status', $request->complaint_status);
        }

        if ($request->filled('complaint_type')) {
            $query->where('complaint_type', $request->complaint_type);
        } else if (!$request->has('filter')) {
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

                case 'critical':
                    $query->whereHas('replies', function ($q) {
                        $q->whereNotNull('criticality')
                            ->whereRaw('reply_date = (
                            SELECT MAX(reply_date)
                            FROM complaint_reply
                            WHERE complaint_reply.complaint_id = complaint.complaint_id
                        )');
                                    })->orderByRaw("FIELD(
                        (SELECT criticality 
                        FROM complaint_reply 
                        WHERE complaint_reply.complaint_id = complaint.complaint_id 
                        ORDER BY reply_date DESC 
                        LIMIT 1),
                        'अत्यधिक', 'मध्यम', 'कम'
                    )");
                    break;

                case 'closed':
                    $query->where('complaint_status', 4);
                    break;

                case 'cancel':
                    $query->where('complaint_status', 5);
                    break;

                case 'forwarded_manager':
                    $loggedInId = session('user_id');

                    $query->whereHas('replies', function ($q) use ($loggedInId) {
                        $q->where('forwarded_to', $loggedInId)
                            ->whereRaw('reply_date = (
                        SELECT MAX(reply_date)
                        FROM complaint_reply
                        WHERE complaint_reply.complaint_id = complaint.complaint_id
                    )');
                    });
                    break;
            }
        }
        $complaints = $query->orderBy('posted_date', 'desc')->get();

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
            $html = '';

            foreach ($complaints as $index => $complaint) {
                $html .= '<tr>';
                $html .= '<td>' . ($index + 1) . '</td>';
                $html .= '<td>
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
                // $html .= '<td>' . \Carbon\Carbon::parse($complaint->posted_date)->format('d-m-Y') . '</td>';

                // // Pending Days or Status
                // if (in_array($complaint->complaint_status, [4, 5])) {
                //     $html .= '<td></td>';
                // } else {
                //     $html .= '<td>' . $complaint->pending_days . ' दिन</td>';
                // }

                // Status Text
                // $html .= '<td>' . strip_tags($complaint->statusTextPlain()) . '</td>';
                $html .= '<td>' . ($complaint->admin_name ?? '') . '</td>';

                $html .= '<td>' . ($complaint->forwarded_to_name ?? '-') . '<br>' . ($complaint->forwarded_reply_date ?? '-') . '</td>';


                // Action Button
                if ($complaint->complaint_type === 'शुभ सुचना' || $complaint->complaint_type === 'अशुभ सुचना') {
                    $html .= '<td><a href="' . route('suchna_show.details', $complaint->complaint_id) . '" class="btn btn-sm btn-primary" style="white-space: nowrap;">क्लिक करें</a></td>';
                } elseif ($complaint->complaint_type === 'समस्या' || $complaint->complaint_type === 'विकास') {
                    $html .= '<td><a href="' . route('complaints_show.details', $complaint->complaint_id) . '" class="btn btn-sm btn-primary" style="white-space: nowrap;">क्लिक करें</a></td>';
                }
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

        return view('manager.operator_complaints', compact(
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





    public function viewCommanderSuchnas(Request $request)
    {
        $query = Complaint::with('registrationDetails', 'replies.forwardedToManager')->where('type', 1)->whereIn('complaint_type', ['अशुभ सुचना', 'शुभ सुचना']);

        if ($request->filled('complaint_status')) {
            $query->where('complaint_status', $request->complaint_status);
        }

        if ($request->filled('complaint_type')) {
            $query->where('complaint_type', $request->complaint_type);
        } else if (!$request->has('filter')) {
            $query->where('complaint_type', 'शुभ सुचना');
        }


        if ($request->filled('admin_id')) {
            $query->whereHas('latestReply', function ($q) use ($request) {
                $q->where('forwarded_to', $request->admin_id);
            });
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


        if ($request->filled('filter')) {
            switch ($request->filter) {
                case 'not_opened':
                    $query->whereHas('replies', function ($q) {
                        $q->where('complaint_status', 11)
                            ->where('complaint_reply', 'सुचना दर्ज की गई है।')
                            ->where('forwarded_to', 6)
                            ->whereNull('selected_reply');
                    })->has('replies', '=', 1);
                    break;

                case 'cancel':
                    $query->where('complaint_status', 18);
                    break;

                case 'forwarded_manager':
                    $loggedInId = session('user_id');

                    $query->whereHas('replies', function ($q) use ($loggedInId) {
                        $q->where('forwarded_to', $loggedInId)
                            ->whereRaw('reply_date = (
                        SELECT MAX(reply_date)
                        FROM complaint_reply
                        WHERE complaint_reply.complaint_id = complaint.complaint_id
                        )');
                    });
                    break;
            }
        }

        $complaints = $query->orderBy('posted_date', 'desc')->get();

        foreach ($complaints as $complaint) {
            if (!in_array($complaint->complaint_status, [13,14,15,16,17,18])) {
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
            $html = '';

            foreach ($complaints as $index => $complaint) {
                $html .= '<tr>';
                $html .= '<td>' . ($index + 1) . '</td>';
                $html .= '<td>
                <strong>सुचना क्र.: </strong>' . ($complaint->complaint_number ?? 'N/A') . '<br>
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


                $html .= '<td>
                <strong>तिथि: ' . \Carbon\Carbon::parse($complaint->posted_date)->format('d-m-Y h:i A') . '</strong><br>';
                if ($complaint->complaint_status == 13) {
                    $html .= 'सम्मिलित हुए';
                } elseif ($complaint->complaint_status == 14) {
                    $html .= 'सम्मिलित नहीं हुए';
                } elseif ($complaint->complaint_status == 15) {
                    $html .= 'फोन पर संपर्क किया';
                } elseif ($complaint->complaint_status == 16) {
                    $html .= 'ईमेल पर संपर्क किया';
                } elseif ($complaint->complaint_status == 17) {
                    $html .= 'व्हाट्सएप पर संपर्क किया';
                } elseif ($complaint->complaint_status == 18) {
                    $html .= 'रद्द';
                } else {
                    $html .= $complaint->pending_days . ' दिन';
                }
                $html .= '
            </td>';

                $html .= '<td>' . ($complaint->registrationDetails->name ?? '') . '</td>';

                $html .= '<td>' . ($complaint->forwarded_to_name ?? '-') . '<br>' . ($complaint->forwarded_reply_date ?? '-') . '</td>';

                $html .= '<td>' . ($complaint->issue_title ?? '') . '</td>';
                $html .= '<td>' . ($complaint->program_date ?? '') . '</td>';

                // Action Button
                if ($complaint->complaint_type === 'शुभ सुचना' || $complaint->complaint_type === 'अशुभ सुचना') {
                    $html .= '<td><a href="' . route('suchna_show.details', $complaint->complaint_id) . '" class="btn btn-sm btn-primary" style="white-space: nowrap;">क्लिक करें</a></td>';
                } elseif ($complaint->complaint_type === 'समस्या' || $complaint->complaint_type === 'विकास') {
                    $html .= '<td><a href="' . route('complaints_show.details', $complaint->complaint_id) . '" class="btn btn-sm btn-primary" style="white-space: nowrap;">क्लिक करें</a></td>';
                }

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
        $managers = User::where('role', 2)->get();

        return view('manager/commander_suchna', compact(
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

    public function viewOperatorSuchnas(Request $request)
    {
        $query = Complaint::with('registrationDetails', 'replies.forwardedToManager')->where('type', 2)->whereIn('complaint_type', ['अशुभ सुचना', 'शुभ सुचना']);
      
        if ($request->filled('complaint_status')) {
            $query->where('complaint_status', $request->complaint_status);
        }

        if ($request->filled('complaint_type')) {
            $query->where('complaint_type', $request->complaint_type);
        } else if (!$request->has('filter')) {
            $query->where('complaint_type', 'शुभ सुचना');
        }

        if ($request->filled('admin_id')) {
            $query->whereHas('latestReply', function ($q) use ($request) {
                $q->where('forwarded_to', $request->admin_id);
            });
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

        if ($request->filled('filter')) {
            switch ($request->filter) {
                case 'not_opened':
                    $query->whereHas('replies', function ($q) {
                        $q->where('complaint_status', 11)
                            ->where('complaint_reply', 'सुचना दर्ज की गई है।')
                            ->where('forwarded_to', 6)
                            ->whereNull('selected_reply');
                    })->has('replies', '=', 1);
                    break;

                case 'cancel':
                    $query->where('complaint_status', 18);
                    break;

                case 'forwarded_manager':
                    $loggedInId = session('user_id');

                    $query->whereHas('replies', function ($q) use ($loggedInId) {
                        $q->where('forwarded_to', $loggedInId)
                            ->whereRaw('reply_date = (
                        SELECT MAX(reply_date)
                        FROM complaint_reply
                        WHERE complaint_reply.complaint_id = complaint.complaint_id
                        )');
                    });
                    break;
            }
        }
        $complaints = $query->orderBy('posted_date', 'desc')->get();

        // Add extra data to each complaint
        foreach ($complaints as $complaint) {
            $admin = DB::table('admin_master')->where('admin_id', $complaint->complaint_created_by)->first();
            $complaint->admin_name = $admin->admin_name ?? '';
            $complaint->pending_days = in_array($complaint->complaint_status, [13, 14, 15, 16, 17, 18])
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
            $html = '';

            foreach ($complaints as $index => $complaint) {
                $html .= '<tr>';
                $html .= '<td>' . ($index + 1) . '</td>';
                $html .= '<td>
            <strong>सुचना क्र.: </strong>' . ($complaint->complaint_number ?? 'N/A') . '<br>
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

                $html .= '<td>
            <strong>तिथि: ' . \Carbon\Carbon::parse($complaint->posted_date)->format('d-m-Y h:i A') . '</strong><br>';
                if ($complaint->complaint_status == 13) {
                    $html .= 'सम्मिलित हुए';
                } elseif ($complaint->complaint_status == 14) {
                    $html .= 'सम्मिलित नहीं हुए';
                } elseif ($complaint->complaint_status == 15) {
                    $html .= 'फोन पर संपर्क किया';
                } elseif ($complaint->complaint_status == 16) {
                    $html .= 'ईमेल पर संपर्क किया';
                } elseif ($complaint->complaint_status == 17) {
                    $html .= 'व्हाट्सएप पर संपर्क किया';
                } elseif ($complaint->complaint_status == 18) {
                    $html .= 'रद्द';
                } else {
                    $html .= $complaint->pending_days . ' दिन';
                }
                $html .= '
        </td>';

                $html .= '<td>' . ($complaint->admin_name ?? '') . '</td>';

                $html .= '<td>' . ($complaint->forwarded_to_name ?? '-') . '<br>' . ($complaint->forwarded_reply_date ?? '-') . '</td>';

                $html .= '<td>' . ($complaint->program_date ?? '') . '</td>';
                $html .= '<td>' . ($complaint->issue_title ?? '') . '</td>';

                // Action Button
                if ($complaint->complaint_type === 'शुभ सुचना' || $complaint->complaint_type === 'अशुभ सुचना') {
                    $html .= '<td><a href="' . route('suchna_show.details', $complaint->complaint_id) . '" class="btn btn-sm btn-primary" style="white-space: nowrap;">क्लिक करें</a></td>';
                } elseif ($complaint->complaint_type === 'समस्या' || $complaint->complaint_type === 'विकास') {
                    $html .= '<td><a href="' . route('complaints_show.details', $complaint->complaint_id) . '" class="btn btn-sm btn-primary" style="white-space: nowrap;">क्लिक करें</a></td>';
                }
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
        $managers = User::where('role', 2)->get();

        return view('manager.operator_suchna', compact(
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


    public function getgramPollings($nagar_id)
    {
        $pollings = Polling::where('nagar_id', $nagar_id)->get([
            'gram_polling_id',
            'polling_name',
            'polling_no'
        ]);

        return response()->json($pollings);
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

        $mandals = Mandal::where('vidhansabha_id', $complaint->vidhansabha_id)->pluck('mandal_id');

        // 2. Fetch nagars that belong to those mandals, and eager load the related mandal
        $nagars = Nagar::with('mandal')
            ->whereIn('mandal_id', $mandals)
            ->orderBy('nagar_name')
            ->get();
        $replyOptions = ComplaintReply::all();
        $departments = Department::all();
        $loggedInManagerId = session('user_id');

        $managers = User::where('role', 2)
            ->where('admin_id', '!=', $loggedInManagerId) // exclude logged-in manager
            ->get();

        return view('manager/details_complaints', [
            'complaint' => $complaint,
            'nagars' => $nagars,
            'replyOptions' => $replyOptions,
            'departments' => $departments,
            'managers' => $managers,
            'loggedInManagerId' => $loggedInManagerId
        ]);
    }

    public function allsuchnas_show($id)
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

        $mandals = Mandal::where('vidhansabha_id', $complaint->vidhansabha_id)->pluck('mandal_id');

        // 2. Fetch nagars that belong to those mandals, and eager load the related mandal
        $nagars = Nagar::with('mandal')
            ->whereIn('mandal_id', $mandals)
            ->orderBy('nagar_name')
            ->get();
        $replyOptions = ComplaintReply::all();
        $departments = Department::all();
        $loggedInManagerId = session('user_id');

        $managers = User::where('role', 2)
            ->where('admin_id', '!=', $loggedInManagerId) 
            ->get();

        return view('manager/suchna_details', [
            'complaint' => $complaint,
            'nagars' => $nagars,
            'replyOptions' => $replyOptions,
            'departments' => $departments,
            'managers' => $managers,
            'loggedInManagerId' => $loggedInManagerId
        ]);
    }

    public function complaintsReply(Request $request, $id)
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
        $reply->selected_reply = $request->selected_reply ?? 0;
        $reply->reply_from = session('user_id') ?? 0;
        $reply->reply_date = now();
        $reply->complaint_status = $request->cmp_status;
        $reply->review_date = $request->review_date ?? null;
        $reply->importance = $request->importance ?? null;
        $reply->criticality = $request->criticality ?? null;

        if ($request->filled('c_video')) {
            $reply->c_video = $request->c_video;
        }

        $userId = session('user_id');
        $userRole = session('logged_in_role');

        if (in_array((int)$request->cmp_status, [4, 5, 18, 17, 16, 15, 14, 13])) {
            $reply->forwarded_to = 0;
        } else {
            if ($request->filled('forwarded_to')) {
                // Use the selected forwarded_to if present
                $reply->forwarded_to = $request->forwarded_to;
            } elseif ($userRole == 2 && $userId) {
                // Use logged-in manager ID if no forwarded_to selected
                $reply->forwarded_to = $userId;
            } else {
                $reply->forwarded_to = null;
            }
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

        if ($complaint->type == 1) { 
            if ($complaint->complaint_type === 'शुभ सुचना' || $complaint->complaint_type === 'अशुभ सुचना') {
                $successMessage = 'कमांडर सूचना का उत्तर सफलतापूर्वक दर्ज किया गया और स्थिति अपडेट हो गई।';
            } else {
                $successMessage = 'कमांडर शिकायत के लिए जवाब दर्ज किया गया और शिकायत अपडेट हुई।';
            }
            return redirect()->route('commander.complaints.view', $id)
                ->with('success', $successMessage);
        } else { // Operator
            if ($complaint->complaint_type === 'शुभ सुचना' || $complaint->complaint_type === 'अशुभ सुचना') {
                $successMessage = 'सूचना का उत्तर सफलतापूर्वक दर्ज किया गया और स्थिति अपडेट हो गई।';
            } else {
                $successMessage = 'कार्यालय शिकायत के लिए जवाब दर्ज किया गया और शिकायत अपडेट हुई।';
            }
            return redirect()->route('operator.complaints.view', $id)
                ->with('success', $successMessage);
        }

        // if ($complaint->type == 1) {
        //     return redirect()->route('commander.complaints.view', $id)
        //         ->with('success', 'कमांडर शिकायत के लिए जवाब दर्ज किया गया और शिकायत अपडेट हुई।');
        // } else {
        //     return redirect()->route('operator.complaints.view', $id)
        //         ->with('success', 'कार्यालय शिकायत के लिए जवाब दर्ज किया गया और शिकायत अपडेट हुई।');
        // }
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

    public function getDesignations($department_name)
    {
        $department = Department::where('department_name', $department_name)->first();

        if (!$department) {
            return response()->json([]);
        }

        $designations = Designation::where('department_id', $department->department_id)->get();

        return response()->json($designations);
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
                // 'txtmandal' => 'required',
                'txtgram' => 'required|integer',
                'txtpolling' => 'required|integer',
                // 'txtarea' => 'required',
                'type' => 'required|string',
                'CharCounter' => 'nullable|string|max:100',
                'NameText' => 'required|string|max:2000',
                'department' => 'nullable',
                'post' => 'nullable',
                'from_date' => 'nullable|date',
                'program_date' => 'nullable|date',
                'to_date' => 'nullable',
                'file_attach' => 'nullable|file|max:20480'
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

        $complaint = Complaint::findOrFail($id);
        $complaint_number = $complaint->complaint_number;

        $nagar = Nagar::with('mandal')->find($request->txtgram);
        $mandal_id = $nagar?->mandal?->mandal_id;

        $polling = Polling::with('area')->where('gram_polling_id', $request->txtpolling)->first();
        $area_id = $polling?->area?->area_id;

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
        $complaint->mandal_id = $mandal_id;
        $complaint->gram_id = $request->txtgram;
        $complaint->polling_id = $request->txtpolling;
        $complaint->area_id = $area_id;
        $complaint->complaint_department = $request->department ?? '';
        $complaint->complaint_designation = $request->post ?? '';
        $complaint->issue_title = $request->CharCounter ?? '';
        $complaint->issue_description = $request->NameText;
        $complaint->news_date = $request->from_date;
        $complaint->program_date = $request->program_date;
        $complaint->news_time = $request->to_date;

        if ($request->hasFile('file_attach')) {
            $file = $request->file('file_attach');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('assets/upload/complaints'), $filename);
            $complaint->issue_attachment = $filename;
        }

        $complaint->save();

        $message = "आपकी शिकायत क्रमांक $complaint_number सफलतापूर्वक अपडेट कर दी गई है।";
        if ($complaint->type == 1) {
            $mobile = RegistrationForm::where('registration_id', $complaint->complaint_created_by)->value('mobile1');
            $this->messageSent($message, $mobile);
        }

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => $message]);
        }

        if ($complaint->type == 1) {
            return redirect()->route('commander.complaints.view')
                ->with('success', 'कमांडर शिकायत सफलतापूर्वक अपडेट हुई और संदेश भेजा गया।');
        } else {
            return redirect()->route('operator.complaints.view')
                ->with('success', 'कार्यालय शिकायत सफलतापूर्वक अपडेट हुई');
        }
    }

    public function updateSuchna(Request $request, $id)
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
                // 'txtmandal' => 'required',
                'txtgram' => 'required|integer',
                'txtpolling' => 'required|integer',
                // 'txtarea' => 'required',
                'type' => 'required|string',
                'CharCounter' => 'nullable|string|max:100',
                'NameText' => 'required|string|max:2000',
                'department' => 'nullable',
                'post' => 'nullable',
                'from_date' => 'nullable|date',
                'program_date' => 'nullable|date',
                'to_date' => 'nullable',
                'file_attach' => 'nullable|file|max:20480'
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

        $complaint = Complaint::findOrFail($id);
        $complaint_number = $complaint->complaint_number;

        $nagar = Nagar::with('mandal')->find($request->txtgram);
        $mandal_id = $nagar?->mandal?->mandal_id;

        $polling = Polling::with('area')->where('gram_polling_id', $request->txtpolling)->first();
        $area_id = $polling?->area?->area_id;

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
        $complaint->mandal_id = $mandal_id;
        $complaint->gram_id = $request->txtgram;
        $complaint->polling_id = $request->txtpolling;
        $complaint->area_id = $area_id;
        $complaint->complaint_department = $request->department ?? '';
        $complaint->complaint_designation = $request->post ?? '';
        $complaint->issue_title = $request->CharCounter ?? '';
        $complaint->issue_description = $request->NameText;
        $complaint->news_date = $request->from_date;
        $complaint->program_date = $request->program_date;
        $complaint->news_time = $request->to_date;

        if ($request->hasFile('file_attach')) {
            $file = $request->file('file_attach');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('assets/upload/complaints'), $filename);
            $complaint->issue_attachment = $filename;
        }

        $complaint->save();

        $message = "आपकी सूचना क्रमांक $complaint_number सफलतापूर्वक अपडेट कर दी गई है।";
        if ($complaint->type == 1) {
            $mobile = RegistrationForm::where('registration_id', $complaint->complaint_created_by)->value('mobile1');
            $this->messageSent($message, $mobile);
        }

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => $message]);
        }

        if ($complaint->type == 1) {
            return redirect()->route('commander.complaints.view')
                ->with('success', 'कमांडर सूचना सफलतापूर्वक अपडेट हुई और संदेश भेजा गया।');
        } else {
            return redirect()->route('operator.complaints.view')
                ->with('success', 'कार्यालय सूचना सफलतापूर्वक अपडेट हुई');
        }
    }

    // department master functions
    public function department_index()
    {
        $departments = Department::all();
        return view('manager/department_master', compact('departments'));
    }

    public function department_store(Request $request)
    {
        $request->validate([
            'department_name' => 'required|unique:department_master,department_name'
        ]);

        Department::create(['department_name' => $request->department_name]);

        return redirect()->back()->with('insert_msg', 'विभाग जोड़ा गया!');
    }

    public function department_edit($id)
    {
        $department = Department::findOrFail($id);
        return view('manager/edit_department', compact('department'));
    }

    public function department_update(Request $request, $id)
    {
        $request->validate([
            'department_name' => 'required|unique:department_master,department_name,' . $id . ',department_id'
        ]);

        $department = Department::findOrFail($id);
        $department->update(['department_name' => $request->department_name]);

        return redirect()->route('department.index')->with('update_msg', 'विभाग अपडेट किया गया!');
    }



    // designation master controller functions
    public function indexDesignation()
    {
        $departments = DB::table('department_master')->get();

        $designations = DB::table('designation_master as d')
            ->leftJoin('department_master as v', 'v.department_id', '=', 'd.department_id')
            ->select('d.designation_id', 'd.designation_name', 'v.department_name')
            ->orderBy('d.department_id')
            ->get();

        return view('manager/designation_master', compact('departments', 'designations'));
    }

    public function designationStore(Request $request)
    {
        $request->validate([
            'department_id' => 'required|exists:department_master,department_id',
            'designation_name' => 'required|string|max:255'
        ]);

        DB::table('designation_master')->insert([
            'department_id' => $request->department_id,
            'designation_name' => $request->designation_name
        ]);

        return redirect()->route('designation.master')->with('success', 'पद जोड़ा गया!');
    }

    public function designationEdit($id)
    {
        $designation = Designation::findOrFail($id);
        $departments = Department::all();

        return view('manager/edit_designation', compact('designation', 'departments'));
    }

    public function designationUpdate(Request $request, $id)
    {
        $request->validate([
            'department_id' => 'required|exists:department_master,department_id',
            'designation_name' => 'required|string|max:255'
        ]);

        $designation = Designation::findOrFail($id);
        $designation->update([
            'department_id' => $request->department_id,
            'designation_name' => $request->designation_name
        ]);

        return redirect()->route('designation.master')->with('update_msg', 'पद अपडेट किया गया!');
    }


    // subject master controller functions
    public function indexComplaint()
    {
        $departments = DB::table('department_master')->get();

        $subjects = DB::table('complaint_subject_master as d')
            ->leftJoin('department_master as v', 'v.department_id', '=', 'd.department_id')
            ->select('d.subject_id', 'd.subject', 'v.department_name')
            ->orderBy('d.department_id')
            ->get();

        return view('manager/complaint_subject_master', compact('departments', 'subjects'));
    }

    public function complaintSubjectStore(Request $request)
    {
        $request->validate([
            'department_id' => 'required|exists:department_master,department_id',
            'subject' => 'required|string|max:255'
        ]);

        DB::table('complaint_subject_master')->insert([
            'department_id' => $request->department_id,
            'subject' => $request->subject
        ]);

        return redirect()->route('complaintSubject.master')->with('success', 'शिकायत का विषय जोड़ा गया!');
    }

    public function complaintSubjectEdit($id)
    {
        $subject = Subject::findOrFail($id);
        $departments = Department::all();

        return view('manager/edit_complaint_subject', compact('subject', 'departments'));
    }

    public function complaintSubjectUpdate(Request $request, $id)
    {
        $request->validate([
            'department_id' => 'required|exists:department_master,department_id',
            'subject' => 'required|string|max:255'
        ]);

        $subject = subject::findOrFail($id);
        $subject->update([
            'department_id' => $request->department_id,
            'subject' => $request->subject
        ]);

        return redirect()->route('complaintReply.index')->with('update_msg', 'शिकायत का विषय अपडेट किया गया!');
    }



    // complaint reply master functions
    public function complaintReplyIndex()
    {
        $replies = ComplaintReply::all();
        return view('manager/complaint_reply_master', compact('replies'));
    }

    public function complaintReplyStore(Request $request)
    {
        $request->validate([
            'reply' => 'required|unique:complaint_reply_master,reply'
        ]);

        ComplaintReply::create(['reply' => $request->reply]);

        return redirect()->back()->with('insert_msg', 'शिकायत का जवाब जोड़ा गया!');
    }

    public function complaintReplyEdit($id)
    {
        $reply = ComplaintReply::findOrFail($id);
        return view('manager/edit_complaint_reply', compact('reply'));
    }

    public function complaintReplyUpdate(Request $request, $id)
    {
        $request->validate([
            'reply' => 'required|unique:complaint_reply_master,reply,' . $id . ',reply_id'
        ]);

        $reply = ComplaintReply::findOrFail($id);
        $reply->update(['reply' => $request->reply]);

        return redirect()->route('complaintReply.index')->with('update_msg', 'शिकायत का जवाब अपडेट किया गया!');
    }



    // create adhikari master controller functions
    public function adhikariIndex()
    {
        $departments = Department::all();
        $records = Adhikari::with(['department', 'designation'])->get();

        return view('manager.create_adhikari_master', compact('departments', 'records'));
    }

    public function adhikariStore(Request $request)
    {
        $request->validate([
            'department_id' => 'required|exists:department_master,department_id',
            'designation_id' => 'required|exists:designation_master,designation_id',
            'name' => 'required|string|max:255',
            'mobile' => 'required|digits:10',
            'email' => 'required|email|max:255'
        ]);

        $exists = DB::table('create_adhikari_master')
            ->where('department_id', $request->department_id)
            ->where('designation_id', $request->designation_id)
            ->where('mobile', $request->mobile)
            ->where('email', $request->email)
            ->exists();

        if ($exists) {
            return redirect()->back()->with('error', 'यह अधिकारी पहले से मौजूद है');
        }

        DB::table('create_adhikari_master')->insert([
            'department_id' => $request->department_id,
            'designation_id' => $request->designation_id,
            'name' => $request->name,
            'mobile' => $request->mobile,
            'email' => $request->email,
            'created_at' => now(),
        ]);

        return redirect()->route('adhikari.index')->with('success', 'अधिकारी सफलतापूर्वक जोड़ा गया!');
    }

    public function adhikariEdit($id)
    {
        $adhikari = Adhikari::findOrFail($id);
        $departments = Department::all();
        $designations = Designation::where('department_id', $adhikari->department_id)->get();

        return view('manager/edit_adhikari', compact('adhikari', 'departments', 'designations'));
    }

    public function adhikariUpdate(Request $request, $id)
    {
        $request->validate([
            'department_id' => 'required|exists:department_master,department_id',
            'designation_id' => 'required|exists:designation_master,designation_id',
            'name' => 'required|string|max:255',
            'mobile' => 'required|digits:10',
            'email' => 'required|email|max:255'
        ]);

        $adhikari = Adhikari::findOrFail($id);

        $adhikari->update([
            'department_id' => $request->department_id,
            'designation_id' => $request->designation_id,
            'name' => $request->name,
            'mobile' => $request->mobile,
            'email' => $request->email,
            'updated_at' => now(),
        ]);

        return redirect()->route('adhikari.index')->with('update_msg', 'अधिकारी अपडेट किया गया!');
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






    // category master controller functions
    public function categoryIndex()
    {
        $categories = Category::all();
        return view('manager/category_master', compact('categories'));
    }

    public function categoryStore(Request $request)
    {
        $request->validate([
            'category' => 'required|unique:category_master,category'
        ]);

        Category::create(['category' => $request->category]);

        return redirect()->back()->with('insert_msg', 'श्रेणी जोड़ी गई!');
    }

    public function categoryEdit($id)
    {
        $category = Category::findOrFail($id);
        return view('manager/edit_category', compact('category'));
    }

    public function categoryUpdate(Request $request, $id)
    {
        $request->validate([
            'category' => 'required|unique:category_master,category,' . $id . ',id'
        ]);

        $category = Category::findOrFail($id);
        $category->update(['category' => $request->category]);

        return redirect()->route('category.index')->with('update_msg', 'श्रेणी अपडेट की गई!');
    }

    public function categoryDestroy($id)
    {
        Category::destroy($id);
        return redirect()->back()->with('delete_msg', 'श्रेणी हटाई गई!');
    }


    // interest master controller functions
    public function interestIndex()
    {
        $interests = Interest::all();
        return view('manager/interest_master', compact('interests'));
    }

    public function interestStore(Request $request)
    {
        $request->validate([
            'interest' => 'required|unique:interest_master,interest_name'
        ]);

        Interest::create(['interest_name' => $request->interest]);

        return redirect()->back()->with('insert_msg', 'रुचि जोड़ी गई!');
    }

    public function interestEdit($id)
    {
        $interest = Interest::findOrFail($id);
        return view('manager/edit_interest', compact('interest'));
    }

    public function interestUpdate(Request $request, $id)
    {
        $request->validate([
            'interest' => 'required|unique:interest_master,interest_name,' . $id . ',id'
        ]);

        $interest = Interest::findOrFail($id);
        $interest->update(['interest_name' => $request->interest]);

        return redirect()->route('interest.index')->with('update_msg', 'रुचि अपडेट की गई!');
    }

    public function interestDestroy($id)
    {
        Interest::destroy($id);
        return redirect()->back()->with('delete_msg', 'रुचि हटाई गई!');
    }


    // religion master controller functions
    public function religionIndex()
    {
        $religions = Religion::all();
        return view('manager/religion_master', compact('religions'));
    }

    public function religionStore(Request $request)
    {
        $request->validate([
            'religion' => 'required|unique:religion_master,religion_name'
        ]);

        Religion::create(['religion_name' => $request->religion]);

        return redirect()->back()->with('insert_msg', 'धर्म जोड़ा गया!');
    }

    public function religionEdit($id)
    {
        $religion = Religion::findOrFail($id);
        return view('manager/edit_religion', compact('religion'));
    }

    public function religionUpdate(Request $request, $id)
    {
        $request->validate([
            'religion' => 'required|unique:religion_master,religion_name,' . $id . ',religion_id'
        ]);

        $religion = Religion::findOrFail($id);
        $religion->update(['religion_name' => $request->religion]);

        return redirect()->route('religion.index')->with('update_msg', 'धर्म अपडेट किया गया!');
    }

    public function religionDestroy($id)
    {
        Religion::destroy($id);
        return redirect()->back()->with('delete_msg', 'धर्म हटाया गया!');
    }


    // education master controller functions
    public function educationIndex()
    {
        $educations = Education::all();
        return view('manager/education_master', compact('educations'));
    }

    public function educationStore(Request $request)
    {
        $request->validate([
            'education' => 'required|unique:education_master,education_name'
        ]);

        Education::create(['education_name' => $request->education]);

        return redirect()->back()->with('insert_msg', 'शिक्षा जोड़ी गई!');
    }

    public function educationEdit($id)
    {
        $education = Education::findOrFail($id);
        return view('manager/edit_education', compact('education'));
    }

    public function educationUpdate(Request $request, $id)
    {
        $request->validate([
            'education' => 'required|unique:education_master,education_name,' . $id . ',id'
        ]);

        $education = Education::findOrFail($id);
        $education->update(['education_name' => $request->education]);

        return redirect()->route('education.index')->with('update_msg', 'शिक्षा अपडेट की गई!');
    }

    public function educationDestroy($id)
    {
        Education::destroy($id);
        return redirect()->back()->with('delete_msg', 'शिक्षा हटाई गई!');
    }


    // business master controller functions
    public function businessIndex()
    {
        $businesss = Business::all();
        return view('manager/business_master', compact('businesss'));
    }

    public function businessStore(Request $request)
    {
        $request->validate([
            'business' => 'required|unique:business_master,business_name'
        ]);

        Business::create(['business_name' => $request->business]);

        return redirect()->back()->with('insert_msg', 'व्यवसाय जोड़ा गया!');
    }

    public function businessEdit($id)
    {
        $business = Business::findOrFail($id);
        return view('manager/edit_business', compact('business'));
    }

    public function businessUpdate(Request $request, $id)
    {
        $request->validate([
            'business' => 'required|unique:business_master,business_name,' . $id . ',id'
        ]);

        $business = Business::findOrFail($id);
        $business->update(['business_name' => $request->business]);

        return redirect()->route('business.index')->with('update_msg', 'व्यवसाय अपडेट किया गया!');
    }

    public function businessDestroy($id)
    {
        Business::destroy($id);
        return redirect()->back()->with('delete_msg', 'व्यवसाय हटाया गया!');
    }


    // politics master controller functions
    public function politicsIndex()
    {
        $politics = Politics::all();
        return view('manager/politics_master', compact('politics'));
    }

    public function politicsStore(Request $request)
    {
        $request->validate([
            'politics' => 'required|unique:politics_master,name'
        ]);

        Politics::create(['name' => $request->politics]);

        return redirect()->back()->with('insert_msg', 'राजनीति जोड़ी गई!');
    }

    public function politicsEdit($id)
    {
        $politics = Politics::findOrFail($id);
        return view('manager/edit_politics', compact('politics'));
    }

    public function politicsUpdate(Request $request, $id)
    {
        $request->validate([
            'politics' => 'required|unique:politics_master,name,' . $id . ',id'
        ]);

        $politics = Politics::findOrFail($id);
        $politics->update(['name' => $request->politics]);

        return redirect()->route('politics.index')->with('update_msg', 'राजनीति अपडेट की गई!');
    }

    public function politicsDestroy($id)
    {
        Politics::destroy($id);
        return redirect()->back()->with('delete_msg', 'राजनीति हटाई गई!');
    }
}
