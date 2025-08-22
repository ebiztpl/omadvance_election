<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Department;
use App\Models\Adhikari;
use App\Models\Subject;
use App\Models\Designation;
use App\Models\District;
use App\Models\ComplaintReply;
use App\Models\RegistrationForm;
use App\Models\Step2;
use App\Models\Step3;
use App\Models\Step4;
use App\Models\Mandal;
use App\Models\Nagar;
use App\Models\Area;
use App\Models\Polling;
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
        $jatis = RegistrationForm::select('jati')->distinct()->get();

        return view('admin/dashboard', compact('districts', 'jatis'));
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
                        <a href="' . route('register.show', $row->registration_id) . '" class="btn btn-sm btn-success mr-2">View</a>
                        <a href="' . route('register.card', $row->registration_id) . '" class="btn btn-sm btn-primary mr-2">Card</a>
                        <a href="' . route('register.destroy', $row->registration_id) . '" class="btn btn-sm btn-danger">Delete</a>
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
            'interestOptions'
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

        $positionName = $assignPosition->position->position_name;
        $fromDate = Carbon::parse($assignPosition->from_date)->format('d-M-Y');
        $toDate = Carbon::parse($assignPosition->to_date)->format('d-M-Y');

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
        DB::table('registration_form')->where('registration_id', $id)->delete();

        return redirect()->back()->with('delete_msg', 'रिकॉर्ड हटाया गया!');
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
            ->where('A.mobile1', '!=', '');

        $registrations = $query->get();

        return view('admin/dashboard2', compact('registrations'));
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

        $query->whereNotNull('A.mobile1')->where('A.mobile1', '!=', '');

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
                $html .= '<td>' . ($index + 1) . '</td>';
                $html .= '<td>
                <strong>शिकायत क्र.: </strong>' . ($complaint->complaint_number ?? 'N/A') . '<br>
                <strong>नाम: </strong>' . ($complaint->name ?? 'N/A') . '<br>
                <strong>मोबाइल: </strong>' . ($complaint->mobile_number ?? '') . '<br>
                <strong>पुत्र श्री: </strong>' . ($complaint->father_name ?? '') . '<br>
                <strong>रेफरेंस: </strong>' . ($complaint->reference_name ?? '') . '<br><br>
                <strong>स्थिति: </strong>' . $complaint->statusTextPlain() . '
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
                $html .= '<td>' . ($complaint->admin_name ?? '') . '</td>';

                $html .= '<td>' . $complaint->forwarded_to_name . '<br>' . $complaint->forwarded_reply_date . '</td>';


                // Action Button
                $html .= '<td style="white-space: nowrap;">
                    <a href="' . route('complaints.show', $complaint->complaint_id) . '" class="btn btn-sm btn-primary" >क्लिक करें</a>
                    <button class="btn btn-sm btn-danger delete-complaint" data-id="' . $complaint->complaint_id . '">हटाएं</button>
                </td>';

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

        return view('admin/commander_complaints', compact(
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

    public function complaintDestroy($id)
    {
        $complaint = Complaint::find($id);

        if (!$complaint) {
            return response()->json(['error' => 'रिकॉर्ड नहीं मिला।'], 404);
        }

        $type = $complaint->complaint_type;

        $complaint->replies()->delete();

        $complaint->delete();


        if ($type === 'शुभ सुचना') {
            return response()->json(['success' => 'शुभ सूचना सफलतापूर्वक हटा दी गई।']);
        } elseif ($type === 'अशुभ सुचना') {
            return response()->json(['success' => 'अशुभ सूचना सफलतापूर्वक हटा दी गई।']);
        }

        return response()->json(['success' => 'शिकायत सफलतापूर्वक हटा दी गई।']);
    }

    public function OperatorComplaints(Request $request)
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
                <strong>स्थिति: </strong>' . $complaint->statusTextPlain() . '
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

                $html .= '<td>' . ($complaint->admin_name ?? '') . '</td>';

                $html .= '<td>' . ($complaint->forwarded_to_name ?? '-') . '<br>' . ($complaint->forwarded_reply_date ?? '-') . '</td>';


                // Action Button
                $html .= '<td style="white-space: nowrap;">
                    <a href="' . route('complaints.show', $complaint->complaint_id) . '" class="btn btn-sm btn-primary" >क्लिक करें</a>
                    <button class="btn btn-sm btn-danger delete-complaint" data-id="' . $complaint->complaint_id . '">हटाएं</button>
                </td>';

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

        return view('admin.operator_complaints', compact(
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





    public function CommanderSuchnas(Request $request)
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


        $complaints = $query->orderBy('posted_date', 'desc')->get();

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
                <strong>स्थिति: </strong>' . $complaint->statusTextPlain() . '
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
                $html .= '<td style="white-space: nowrap;">
                    <a href="' . route('complaints.show', $complaint->complaint_id) . '" class="btn btn-sm btn-primary" >क्लिक करें</a>
                    <button class="btn btn-sm btn-danger delete-complaint" data-id="' . $complaint->complaint_id . '">हटाएं</button>
                </td>';

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

        return view('admin/commander_suchna', compact(
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

    public function OperatorSuchnas(Request $request)
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

                $html .= '<td style="white-space: nowrap;">
                    <a href="' . route('complaints.show', $complaint->complaint_id) . '" class="btn btn-sm btn-primary" >क्लिक करें</a>
                    <button class="btn btn-sm btn-danger delete-complaint" data-id="' . $complaint->complaint_id . '">हटाएं</button>
                </td>';

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

        return view('admin.operator_suchna', compact(
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

        if (in_array((int)$request->cmp_status, [4, 5, 18, 17, 16, 15, 14, 13])) {
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

        DB::table('registration_form')->where('registration_id', $request->member_id)
            ->update([
                'position_id' => $request->position_id,
                'type' => 3
            ]);

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
    public function viewResponsibilities()
    {
        $assignments = AssignPosition::with(['member', 'position', 'addressInfo', 'district'])->get();
        $districts = District::all();

        return view('admin/view_responsibility', compact('assignments', 'districts'));
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

        DB::table('registration_form')->where('registration_id', $request->member_id)
            ->update(['position_id' => $request->position_id]);

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
                        'position_id'       => 0,
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
                            'matdan_kendra_name' => $polling->polling_name,
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
    public function downloadvoterFullData(Request $request)
    {
        $query = DB::table('registration_form')->where('type', 1);

        // Optional filters 
        if ($request->filled('voter_id')) {
            $query->where('voter_id', $request->input('voter_id'));
        }
        if ($request->filled('area_id')) {
            $areaId = $request->input('area_id');
            $query->whereIn('registration_id', function ($subquery) use ($areaId) {
                $subquery->select('registration_id')
                    ->from('step2')
                    ->where('area_id', $areaId);
            });
        }

        $fileName = "voterlist.csv";
        $headers = [
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];

        $callback = function () use ($query) {
            $file = fopen('php://output', 'w');

            // Write header only once
            $headerWritten = false;
            $sr = 1;

            $query->orderBy('registration_id')
                ->chunk(1000, function ($results) use (&$headerWritten, &$sr, $file) {
                    foreach ($results as $voter) {
                        $step2 = DB::table('step2')->where('registration_id', $voter->registration_id)->first();
                        $step3 = DB::table('step3')->where('registration_id', $voter->registration_id)->first();
                        $area_name = DB::table('area_master')->where('area_id', optional($step2)->area_id)->value('area_name');

                        $row = [
                            'SR No' => $sr++,
                            'Name' => $voter->name ?? '',
                            'Father Name' => $voter->father_name ?? '',
                            'House' => $step2->house ?? '',
                            'Age' => $voter->age ?? '',
                            'Gender' => $voter->gender ?? '',
                            'Voter ID' => $voter->voter_id ?? '',
                            'Area Name' => $area_name ?? '',
                            'Jati' => $voter->jati ?? '',
                            'Matdan Kendra No' => $step2->matdan_kendra_no ?? '',
                            'Total Member' => $step3->total_member ?? '',
                            'Mukhiya Mobile' => $step3->mukhiya_mobile ?? '',
                            'Death/Left' => $voter->death_left ?? '',
                            'Date Time' => $voter->date_time ? \Carbon\Carbon::parse($voter->date_time)->format('d-m-Y') : '',
                        ];

                        if (!$headerWritten) {
                            fputcsv($file, array_keys($row));
                            $headerWritten = true;
                        }

                        fputcsv($file, $row);
                    }
                });

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
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
            'polling' => $polling->polling_name ?? '',
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
        return view('admin/membership_form', compact('divisions'));
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
            // 1. Save to registration_form
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
            $registration->mobile1_whatsapp = $request->has('mobile_1_whatsapp') ? 1 : 0;
            $registration->mobile2_whatsapp = $request->has('mobile_2_whatsapp') ? 1 : 0;
            $registration->religion = $request->religion;
            $registration->caste = $request->caste;
            $registration->jati = $request->jati;
            $registration->education = $request->education;
            $registration->business = $request->business;
            $registration->position = $request->position;
            $registration->father_name = $request->father_name;
            $registration->email = $request->email;
            $registration->pincode = $request->pincode;
            $registration->samagra_id = $request->samagra_id;
            $registration->date_time = now();

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
            $step2->matdan_kendra_no = $request->matdan_kendra_no;
            $step2->matdan_kendra_name = $request->matdan_kendra_name;
            $step2->area_id = $request->area_name;
            $step2->loksabha = $request->loksabha;
            $step2->voter_number = $request->voter_number;
            $step2->post_date = now();

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

            // 3. Step 3
            $step3 = new Step3();
            $step3->registration_id = $registration_id;
            $step3->total_member = $request->total_member;
            $step3->total_voter = $request->total_voter;
            $step3->member_job = $request->member_job;
            $step3->member_name_1 = $request->member_name_1;
            $step3->member_mobile_1 = $request->member_mobile_1;
            $step3->member_name_2 = $request->member_name_2;
            $step3->member_mobile_2 = $request->member_mobile_2;
            $step3->friend_name_1 = $request->friend_name_1;
            $step3->friend_mobile_1 = $request->friend_mobile_1;
            $step3->friend_name_2 = $request->friend_name_2;
            $step3->friend_mobile_2 = $request->friend_mobile_2;
            $step3->intrest = is_array($request->interest) ? implode(',', $request->interest) : '';
            $step3->vehicle1 = $request->vehicle1;
            $step3->vehicle2 = $request->vehicle2;
            $step3->vehicle3 = $request->vehicle3;
            $step3->permanent_address = $request->permanent_address;
            $step3->temp_address = $request->temp_address;
            $step3->post_date = now();
            $step3->save();

            // 4. Step 4
            $step4 = new Step4();
            $step4->registration_id = $registration_id;
            $step4->party_name = $request->party_name;
            $step4->present_post = $request->present_post;
            $step4->reason_join = $request->reason_join;
            $step4->post_date = now();
            $step4->save();

            DB::commit();
            return redirect()->route('register.card', ['id' => $registration_id]);
        } catch (\Exception $e) {
            DB::rollback();
            return back()->withErrors(['error' => $e->getMessage()]);
        }

        // return redirect()->route('membership.create')->with('success', 'सदस्यता सफलतापूर्वक सबमिट की गई!');
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
}
