<?php

namespace App\Imports;

use App\Jobs\AddImportedUser;
use App\Jobs\RunCommands;
use App\Models\ImportStudent;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class StudentImport implements ToCollection
{
    /**
    * @param Collection $collection
    */
    public function collection(Collection $collection)
    {
        $data = [];
        foreach ($collection as $key => $row) {
            if ($key < 9) {
                continue;
            }
            AddImportedUser::dispatch([
                'hemis_id'    => $row[0],
                'name'        => $row[1],
                'surname'     => $row[2],
                'PersonGroup' => $row[3],
            ]);
        }
        RunCommands::dispatch('app:add-user-to-database');
    }
}
