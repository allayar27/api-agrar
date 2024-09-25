<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

class GroupmonthReportExport implements FromArray, WithHeadings, ShouldAutoSize
{
    protected $groupsData;

    public function __construct(array $groupsData)
    {
        $this->groupsData = $groupsData;
    }

    public function array(): array
    {
        return $this->groupsData;
    }

    public function headings(): array
    {
        return [
            'ID',
            'Имя группы',
            'Всего студентов',
            'Всего учебных дней',
            'Процент пришедших',
            'Процент опоздавших'
        ];
    }
    
}
