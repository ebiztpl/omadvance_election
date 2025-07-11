<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Division;
use App\Models\District;
use App\Models\VidhansabhaLokSabha;
use App\Models\Mandal;
use App\Models\Nagar;
use App\Models\Polling;
use App\Models\Area;
use App\Models\Level;
use App\Models\Jati;
use App\Models\Position;
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

        return redirect()->route('nagar.index')->with('success', 'कमाण्ड ऐरिया जोड़ा गया!');
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

        return redirect()->route('nagar.index')->with('update_msg', 'कमाण्ड ऐरिया अपडेट किया गया!');
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
        $vidhansabhas = VidhansabhaLoksabha::where('district_id', $polling->mandal->vidhansabha_id)->get();
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
        $vidhansabhas = VidhansabhaLoksabha::where('district_id', 11)->get();
        return view('manager/jatiwise_members', compact('areas', 'districts', 'jatis', 'vidhansabhas'));
    }

    public function getDropdown(Request $request)
    {
        switch ($request->type) {
            case 'vidhansabha':
                return VidhansabhaLoksabha::where('district_id', $request->id)->get();
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
}
