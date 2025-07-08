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
use App\Models\Level;
use App\Models\Position;
use App\Models\Complaint;
use App\Models\Reply;
use App\Models\AssignPosition;
use Carbon\Carbon;
use Intervention\Image\Facades\Image;
use App\Models\Division;
use Mpdf\Mpdf;
use App\Models\VidhansabhaLoksabha;
use Illuminate\Support\Facades\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminController extends Controller
{
    public function index()
    {
        $districts = District::all();
        $jatis = RegistrationForm::select('jati')->distinct()->get();

        return view('admin/dashboard', compact('districts', 'jatis'));
    }

    public function filter(Request $request)
    {
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
        if ($request->whatsapp !== null) {
            $query->where('reg.mobile1_whatsapp', $request->whatsapp);
        }

        $registrations = $query->get();
        $count = $registrations->count();

        $html = '<table class="display table-bordered" style="min-width: 845px" id="example">';
        $html .= '<thead>
                <tr>
                <th>Sr.No.</th>
                    <th>Member ID</th>
                    <th>Name</th>
                    <th>Mobile</th>
                    <th>Gender</th>
                    <th>Entry Date</th>
                    <th>Action</th>
                </tr>
              </thead><tbody>';


        $x = 0;
        foreach ($registrations as $row) {
            $x += 1;
            $date = date('d-m-Y', strtotime($row->date_time));
            $html .= '<tr>';
            $html .= '<td>' . $x . '</td>';
            $html .= '<td>' . $row->member_id . '</td>';
            $html .= '<td>' . $row->name . '</td>';
            $html .= '<td>' . $row->mobile1 . '</td>';
            $html .= '<td>' . $row->gender . '</td>';
            $html .= '<td>' . $date . '</td>';
            $html .= '<td style="white-space: nowrap;">
              <a href="' . route('register.show', $row->registration_id) . '" class="btn btn-sm btn-success">View</a>
              <a href="' . route('register.card', $row->registration_id) . '" class="btn btn-sm btn-primary">Card</a>
              <a href="' . route('register.destroy', $row->registration_id) . '" class="btn btn-sm btn-danger">Delete</a>
            </td>';
            $html .= '</tr>';
        }

        $html .= '</tbody></table>';

        return response()->json([
            'html' => $html,
            'count' => $count,
        ]);
    }


    public function download(Request $request)
    {
        $condition = $request->input('download_data_whr');

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


    public function show($id)
    {
        $registration = RegistrationForm::with('reference')->findOrFail($id);
        $step2 = Step2::where('registration_id', $id)->first();
        $step3 = Step3::where('registration_id', $id)->first();
        $step4 = Step4::where('registration_id', $id)->first();

        $vidhansabhaModel = VidhansabhaLoksabha::find($step2->vidhansabha);
        $vidhansabha = $vidhansabhaModel ? $vidhansabhaModel->vidhansabha : null;

        $mandalModel = Mandal::find($step2->mandal);
        $mandal = $mandalModel ? $mandalModel->mandal_name : null;

        $nagarModel = Nagar::find($step2->nagar);
        $nagar = $nagarModel ? $nagarModel->nagar_name : null;

        $pollingModel = Polling::find($step2->matdan_kendra_name);
        $polling = $pollingModel ? $pollingModel->polling_name : null;

        $areaModel = Area::find($step2->area_id);
        $area = $areaModel ? $areaModel->area_name : null;

        $divisions = DB::table('division_master')->get();

        $district_id = $division->district_id ?? null;

        $districts = DB::table('district_master')->get();

        $interests = explode(' ', $step3->intrest);
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


        $photoPath = public_path('assets/upload/' . $member->photo);
        $backgroundPath = public_path('assets/images/member_back.jpg');


        $html = view('admin/card_print', [
            'member' => $member,
            'step3' => $step3,
            'address' => $step3->permanent_address ?? '',
            'photoPath' => $photoPath,
            'backgroundPath' => $backgroundPath,
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

        $mpdf->SetWatermarkText('etutorialspoint');
        $mpdf->showWatermarkText = false;

        $mpdf->WriteHTML($html);
        return response($mpdf->Output('', 'S'), 200)
            ->header('Content-Type', 'application/pdf');
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
        return view('admin/dashboard2');
    }

    public function dashboard2_filter(Request $request)
    {
        $condition = $request->input('where');

        $query = DB::table('registration_form as A')
            ->join('registration_form as B', 'A.registration_id', '=', 'B.reference_id')
            ->join('step2 as st', 'st.registration_id', '=', 'A.registration_id')
            ->join('step3 as st3', 'st3.registration_id', '=', 'A.registration_id')
            ->join('step4 as st4', 'st4.registration_id', '=', 'A.registration_id')
            ->select('A.*', 'B.name',  'B.member_id', 'B.mobile1 as mbl', 'B.date_time as pdate');

        if (!empty($condition)) {
            $query->whereRaw($condition);
        }

        $registrations = $query->get();
        $count = $registrations->count();

        $html = '<table class="display table table-bordered" style="min-width: 845px" id="example">';
        $html = '<thead>
                <tr>
                <th>Sr.No.</th>
                    <th>Member ID</th>
                    <th>Name</th>
                    <th>Mobile1</th>
                    <th>Mobile2</th>
                    <th>Gender</th>
                    <th>Entry Date</th>
                    <th>Action</th>
                </tr>
              </thead><tbody>';


        $x = 0;
        foreach ($registrations as $row) {
            $x += 1;
            $date = date('d-m-Y', strtotime($row->pdate));
            $html .= '<tr>';
            $html .= '<td>' . $x . '</td>';
            $html .= '<td>' . $row->member_id . '</td>';
            $html .= '<td>' . $row->name . '</td>';
            $html .= '<td>' . $row->mbl . '</td>';
            $html .= '<td>' . $row->mobile2 . '</td>';
            $html .= '<td>' . $row->gender . '</td>';
            $html .= '<td>' . $date . '</td>';
            $html .= '<td style="white-space: nowrap;">
              <a href="' . route('register.show', $row->registration_id) . '" class="btn btn-sm btn-success">View</a>
              <a href="' . route('register.card', $row->registration_id) . '" class="btn btn-sm btn-primary">Card</a>
              <a href="' . route('register.destroy', $row->registration_id) . '" class="btn btn-sm btn-danger">Delete</a>
            </td>';
            $html .= '</tr>';
        }

        $html .= '</tbody></table>';

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
    public function complaint_index()
    {
        $complaints = Complaint::all();
        return view('admin/view_complaint', compact('complaints'));
    }

    public function complaint_show($id)
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
            'area'
        )->findOrFail($id);

        return view('admin/details_complaint', [
            'complaint' => $complaint,
        ]);
    }

    public function postReply(Request $request, $id)
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

        return redirect()->route('complaints.index', $id)
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
            'registration_form.*',
            'step2.district as district_id',
            'step3.permanent_address'
        )->get();

        foreach ($results as $r) {
            $district = District::find($r->district_id);
            $r->district = $district ? $district->district_name : null;
        }

        $html = '<table class="display table table-bordered" style="min-width: 845px" id="example">';
        $html .= '<thead><tr>
        <th>Sr.No.</th>
        <th>Member ID</th>
        <th>Name</th>
        <th>Address</th>
        <th>District</th>
        <th>Photo</th>
        <th>Post Date</th>
        <th>Action</th>
        </tr></thead><tbody>';

        foreach ($results as $index => $b) {
            $date = $b->date_time ? \Carbon\Carbon::parse($b->date_time)->format('d-m-Y') : '';
            $html .= "<tr>
            <td>" . ($index + 1) . "</td>
            <td>{$b->member_id}</td>
            <td>{$b->name}<br>{$b->mobile1}<br>{$b->gender}</td>
            <td>{$b->permanent_address}</td>
            <td>{$b->district}</td>
            <td><img src='" . asset('assets/upload/' . $b->photo) . "' height='100' alt='Photo' /></td>
            <td>{$date}</td>
            <td>
            <div class='d-flex'>
                <a href='" . route('register.show', $b->registration_id) . "' class='btn btn-success btn-sm mr-1'>View</a>
               <a href='" . route('register.card', ['id' => $b->registration_id]) . "' class='btn btn-warning btn-sm mr-1'>Card</a>
                <a href='#' class='btn btn-primary btn-sm chk' data-id='{$b->registration_id}' data-toggle='modal' data-target='#assignModal'>Assign</a>
            </div>
            </td>
        </tr>";
        }

        $html .= '</tbody></table>';

        return response()->json([
            'html' => $html,
            'count' => count($results)
        ]);
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
            case 'नगर केंद्र/ग्राम केंद्र':
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
            ->update(['position_id' => $request->position_id]);

        return redirect()->back()->with('success', 'दायित्व सफलतापूर्वक जोड़ा गया।');
    }


    public function getVidhansabhasByDistrict(Request $request)
    {
        $districtId = $request->district_id;

        $vidhansabhas = VidhansabhaLokSabha::where('district_id', $districtId)->get(['vidhansabha_id', 'vidhansabha']);

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
        return VidhansabhaLokSabha::where('district_id', $district_id)->get();
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

        return view('admin/view_responsibility_member', compact('assignments'));
    }

    public function assign_destroy($id)
    {
        $assignment = AssignPosition::findOrFail($id);
        $assignment->delete();

        return redirect()->route('view_responsibility.index')->with('delete_msg', 'दायित्व सफलतापूर्वक हटाया गया!');
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
                return optional(VidhansabhaLoksabha::find($refrence_id))->vidhansabha;

            case 'मंडल':
                return optional(Mandal::find($refrence_id))->mandal_name;

            case 'नगर केंद्र/ग्राम केंद्र':
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
}

