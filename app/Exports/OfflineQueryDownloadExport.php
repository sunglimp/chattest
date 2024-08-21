<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use App\Models\OfflineRequesterDetail;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Shared\Date;


class OfflineQueryDownloadExport implements FromCollection, WithTitle, WithHeadings
{
    private $organizationId;
    private $userId;
    private $showOnlyForGroup;
    private $identifier;
    private $startDate;
    private $endDate;
    private $status;
    private $userTimezone;

    /**
     *
     */
    public function __construct($organizationId, $userId, $showOnlyForGroup, $identifier, $startDate, $endDate, $status, $userTimezone)
    {
        $this->organizationId = $organizationId;
        $this->userId = $userId;
        $this->showOnlyForGroup = $showOnlyForGroup;
        $this->identifier = $identifier;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->status = $status;
        $this->userTimezone = $userTimezone;
    }

    public function collection()
    {
        $data = OfflineRequesterDetail::getDownloadSql($this->organizationId, $this->userId, config('app.timezone'), $this->identifier, $this->startDate, $this->endDate, $this->status, $this->userTimezone, $this->showOnlyForGroup);
        return $data;
    }

    public function title(): string
    {
        return 'Offline Query';
    }


    public function headings(): array
    {
        return [
            default_trans($this->organizationId . '/offline_queries.ui_elements_messages.group', __('default/offline_queries.ui_elements_messages.group')),
            default_trans($this->organizationId . '/offline_queries.ui_elements_messages.source_type', __('default/offline_queries.ui_elements_messages.source_type')),
            default_trans($this->organizationId . '/offline_queries.ui_elements_messages.identifier', __('default/offline_queries.ui_elements_messages.identifier')),
            default_trans($this->organizationId . '/offline_queries.ui_elements_messages.client_query', __('default/offline_queries.ui_elements_messages.client_query')),         
            default_trans($this->organizationId . '/archive.ui_elements_messages.date', __('default/archive.ui_elements_messages.date')),
            default_trans($this->organizationId . '/archive.ui_elements_messages.time', __('default/archive.ui_elements_messages.time')),
            default_trans($this->organizationId . '/offline_queries.ui_elements_messages.status', __('default/offline_queries.ui_elements_messages.status')),
        ];
    }
}
