<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Division;
use App\Models\Department;
use App\Models\ComplaintReply;
use App\Models\Adhikari;
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
use Carbon\Carbon;
use App\Models\Reply;
use App\Models\JatiwiseVoter;
use Illuminate\Support\Facades\DB;

class ManagerController extends Controller
{
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
        $complaints = Complaint::where('type', 1)->get();

        foreach ($complaints as $complaint) {
            if (!in_array($complaint->complaint_status, [4, 5])) {
                $complaint->pending_days = Carbon::parse($complaint->posted_date)->diffInDays(now());
            } else {
                $complaint->pending_days = 0;
            }
        }

        return view('manager/commander_complaints', compact('complaints'));
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

        $query = Complaint::where('type', 2);
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
        }

        if ($request->ajax()) {
            $html = '';

            foreach ($complaints as $index => $complaint) {
                $html .= '<tr>';
                $html .= '<td>' . ($index + 1) . '</td>';
                $html .= '<td>' . ($complaint->name ?? 'N/A') . '<br>' . ($complaint->mobile_number ?? '') . '</td>';

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
                $html .= '<td>' . \Carbon\Carbon::parse($complaint->posted_date)->format('d-m-Y') . '</td>';

                // Pending Days or Status
                if (in_array($complaint->complaint_status, [4, 5])) {
                    $html .= '<td>0 दिन</td>';
                } else {
                    $html .= '<td>' . $complaint->pending_days . ' दिन</td>';
                }

                // Status Text
                $html .= '<td>' . strip_tags($complaint->statusTextPlain()) . '</td>';
                $html .= '<td>' . ($complaint->admin_name ?? '') . '</td>';

                // Attachment
                if (!empty($complaint->issue_attachment)) {
                    $html .= '<td><a href="' . asset('assets/upload/complaints/' . $complaint->issue_attachment) . '" target="_blank" class="btn btn-sm btn-success">' . $complaint->issue_attachment . '</a></td>';
                } else {
                    $html .= '<td><button class="btn btn-sm btn-secondary" disabled>No Attachment</button></td>';
                }

                // Action Button
                $html .= '<td><a href="' . route('complaints_show.details', $complaint->complaint_id) . '" class="btn btn-sm btn-primary" style="white-space: nowrap;">क्लिक करें</a></td>';

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
        $subjects = $request->department_id ? Subject::where('department_id', $request->department_id)->get() : collect();

        return view('manager.operator_complaints', compact(
            'complaints',
            'mandals',
            'grams',
            'pollings',
            'areas',
            'departments',
            'subjects'
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

        $nagars = Nagar::orderBy('nagar_name')->get();

        return view('manager/details_complaints', [
            'complaint' => $complaint,
            'nagars' => $nagars
        ]);
    }

    public function complaintsReply(Request $request, $id)
    {
        $request->validate([
            'cmp_reply' => 'required|string',
            'cmp_status' => 'required|in:1,2,3,4',
            'cb_photo.*' => 'nullable|image|mimes:jpeg,png,jpg,bmp,gif|max:2048',
            'ca_photo.*' => 'nullable|image|mimes:jpeg,png,jpg,bmp,gif|max:2048',
            'c_video' => 'nullable|url|max:255',
        ]);

        $complaint = Complaint::findOrFail($id);

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

        if ($request->ajax()) {
            return response()->json([
                'message' => 'शिकायत का उत्तर सफलतापूर्वक दर्ज किया गया और स्थिति अपडेट हो गई।'
            ]);
        }

        if ($complaint->type == 1) {
            return redirect()->route('commander.complaints.view', $id)
                ->with('success', 'कमांडर शिकायत के लिए जवाब दर्ज किया गया और शिकायत अपडेट हुई।');
        } else {
            return redirect()->route('operator.complaints.view', $id)
                ->with('success', 'कार्यालय शिकायत के लिए जवाब दर्ज किया गया और शिकायत अपडेट हुई।');
        }
    }



    public function updateComplaint(Request $request, $id)
    {
        $request->validate([
            'txtname' => 'required|string|max:255',
            'voter' => 'required|string|max:255',
            'division_id' => 'required|integer',
            'txtdistrict_name' => 'required',
            'txtvidhansabha' => 'required',
            'txtmandal' => 'required',
            'txtgram' => 'required',
            'txtpolling' => 'required',
            'txtarea' => 'required',
            'type' => 'required|string',
            'CharCounter' => 'required|string|max:100',
            'NameText' => 'required|string|max:2000',
            'department' => 'nullable',
            'post' => 'nullable',
            'from_date' => 'nullable|date',
            'program_date' => 'nullable|date',
            'to_date' => 'nullable',
        ]);

        $complaint = Complaint::findOrFail($id);

        $complaint_number = $complaint->complaint_number;


        $complaint->complaint_type = $request->type;
        $complaint->name = $request->txtname;
        $complaint->mobile_number = $request->mobile;
        $complaint->email = $request->mobile;
        $complaint->voter_id = $request->voter;
        $complaint->division_id = $request->division_id;
        $complaint->district_id = $request->txtdistrict_name;
        $complaint->vidhansabha_id = $request->txtvidhansabha;
        $complaint->mandal_id = $request->txtmandal;
        $complaint->gram_id = $request->txtgram;
        $complaint->polling_id = $request->txtpolling;
        $complaint->area_id = $request->txtarea;
        $complaint->complaint_department = $request->department ?? '';
        $complaint->complaint_designation = $request->post ?? '';
        $complaint->issue_title = $request->CharCounter;
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
            return response()->json(['message' => $message]);
        }

        if ($complaint->type == 1) {
            return redirect()->route('commander.complaints.view')
                ->with('success', 'कमांडर शिकायत सफलतापूर्वक अपडेट हुई और संदेश भेजा गया।');
        } else {
            return redirect()->route('operator.complaints.view')
                ->with('success', 'कार्यालय शिकायत सफलतापूर्वक अपडेट हुई');
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
            'mobile' => 'required|digits:10'
        ]);

        $exists = DB::table('create_adhikari_master')
            ->where('department_id', $request->department_id)
            ->where('designation_id', $request->designation_id)
            ->where('mobile', $request->mobile)
            ->exists();

        if ($exists) {
            return redirect()->back()->with('error', 'यह अधिकारी पहले से मौजूद है');
        }

        DB::table('create_adhikari_master')->insert([
            'department_id' => $request->department_id,
            'designation_id' => $request->designation_id,
            'name' => $request->name,
            'mobile' => $request->mobile,
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
            'mobile' => 'required|digits:10'
        ]);

        $adhikari = Adhikari::findOrFail($id);

        $adhikari->update([
            'department_id' => $request->department_id,
            'designation_id' => $request->designation_id,
            'name' => $request->name,
            'mobile' => $request->mobile
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
}
