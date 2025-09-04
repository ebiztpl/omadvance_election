<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class GenerateVoterListJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $filters;
    protected $userId;

    public function __construct($filters, $userId)
    {
        $this->filters = $filters;
        $this->userId = $userId;
    }

    public function handle()
    {
        $query = DB::table('registration_form')->where('type', 1);

        if (!empty($this->filters['voter_id'])) {
            $query->where('voter_id', $this->filters['voter_id']);
        }
        if (!empty($this->filters['area_id'])) {
            $areaId = $this->filters['area_id'];
            $query->whereIn('registration_id', function ($subquery) use ($areaId) {
                $subquery->select('registration_id')
                    ->from('step2')
                    ->where('area_id', $areaId);
            });
        }

        $fileName = "voterlist_" . time() . ".csv";
        $filePath = "exports/" . $fileName;

        $handle = fopen(storage_path("app/" . $filePath), 'w');

        // Header
        fputcsv($handle, [
            'SR No',
            'Name',
            'Father Name',
            'House',
            'Age',
            'Gender',
            'Voter ID',
            'Area Name',
            'Jati',
            'Matdan Kendra No',
            'Total Member',
            'Mukhiya Mobile',
            'Death/Left',
            'Date Time'
        ]);

        $sr = 1;
        $query->orderBy('registration_id')
            ->chunk(1000, function ($results) use (&$sr, $handle) {
                foreach ($results as $voter) {
                    $step2 = DB::table('step2')->where('registration_id', $voter->registration_id)->first();
                    $step3 = DB::table('step3')->where('registration_id', $voter->registration_id)->first();
                    $area_name = DB::table('area_master')->where('area_id', optional($step2)->area_id)->value('area_name');

                    $row = [
                        $sr++,
                        $voter->name ?? '',
                        $voter->father_name ?? '',
                        $step2->house ?? '',
                        $voter->age ?? '',
                        $voter->gender ?? '',
                        $voter->voter_id ?? '',
                        $area_name ?? '',
                        $voter->jati ?? '',
                        $step2->matdan_kendra_no ?? '',
                        $step3->total_member ?? '',
                        $step3->mukhiya_mobile ?? '',
                        $voter->death_left ?? '',
                        $voter->date_time ? \Carbon\Carbon::parse($voter->date_time)->format('d-m-Y') : '',
                    ];

                    fputcsv($handle, $row);
                }
            });

        fclose($handle);

        // File info save करें DB में (ताकि user बाद में download कर सके)
        DB::table('downloads')->insert([
            'user_id' => $this->userId,
            'file_name' => $fileName,
            'file_path' => $filePath,
            'status' => 'completed',
            'created_at' => now(),
        ]);
    }
}
