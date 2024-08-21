<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use App\Models\Summary;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithMapping;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\FromArray;

class ChatDownloadAgentWiseExport implements FromArray, WithTitle, WithHeadings, WithMapping
{
    private $organizationId;
    private $result;
    private $clientDisplaySetting;
    private $identifierPermission;

    /**
     *
     */
    public function __construct($organizationId, $result, $clientDisplaySetting, $identifierPermission)
    {
        $this->organizationId = $organizationId;
        $this->result = $result;
        $this->clientDisplaySetting = $clientDisplaySetting;
        $this->identifierPermission = $identifierPermission;
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function array(): array
    {
        return $this->result;
    }

    public function title(): string
    {
        return 'Summaries';
    }

    public function map($summaries): array
    {
        if ($summaries->source_type=='whatsapp' && $this->clientDisplaySetting && $summaries->Number==$summaries->Responder) {
            $client     = json_decode($summaries->raw_info, true);
            $clientName = isset($client) && isset($client[$summaries->source_type]) && isset($client[$summaries->source_type]['name']) ? $client[$summaries->source_type]['name'] : null;
            $client_display_name = client_display_name($this->clientDisplaySetting, $this->identifierPermission, $summaries->Number, $clientName);
        }
        return [
            $summaries->Date,
            $summaries->Time,
            $summaries->Number,
            $summaries->Result,
            ($summaries->waiting_time_for_visitor = convert_average_time($summaries->waiting_time_for_visitor) ?? ''),
            $summaries->Rating,
            $summaries->Tag,
            ($summaries->Responder = (isset($client_display_name) && $summaries->Number==$summaries->Responder) ? $client_display_name : $summaries->Responder),
            $summaries->Type,
            $summaries->Message_Body,
        ];
    }

    public function headings(): array
    {
        return [
            default_trans($this->organizationId . '/archive.ui_elements_messages.date', __('default/archive.ui_elements_messages.date')),
            default_trans($this->organizationId . '/archive.ui_elements_messages.time', __('default/archive.ui_elements_messages.time')),
            default_trans($this->organizationId . '/archive.ui_elements_messages.number', __('default/archive.ui_elements_messages.number')),
            default_trans($this->organizationId . '/archive.ui_elements_messages.result', __('default/archive.ui_elements_messages.result')),
            default_trans($this->organizationId . '/archive.ui_elements_messages.first_response_time', __('default/archive.ui_elements_messages.first_response_time')),
            default_trans($this->organizationId . '/archive.ui_elements_messages.rating', __('default/archive.ui_elements_messages.rating')),
            default_trans($this->organizationId . '/archive.ui_elements_messages.tag', __('default/archive.ui_elements_messages.tag')),
            default_trans($this->organizationId . '/archive.ui_elements_messages.responder', __('default/archive.ui_elements_messages.responder')),
            default_trans($this->organizationId . '/archive.ui_elements_messages.type', __('default/archive.ui_elements_messages.type')),
            default_trans($this->organizationId . '/archive.ui_elements_messages.message_body', __('default/archive.ui_elements_messages.message_body')),
        ];
    }
}
