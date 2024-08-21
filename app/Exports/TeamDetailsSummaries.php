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

class TeamDetailsSummaries implements FromCollection, WithTitle, WithHeadings, WithMapping
{
    private $requestParams;

    private $startDate;

    private $endDate;

    private $agents;

    private $organizationWiseDataFlag;

    private $organizationId;

    /**
     *
     */
    public function __construct($startDate, $endDate, $agents, $organizationId='', $organizationWiseDataFlag=false)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->agents = $agents;
        $this->organizationWiseDataFlag = $organizationWiseDataFlag;
        if ($organizationId!='') {
            $this->organizationId = $organizationId;
        } else {
            $loggedInUser = Auth::user();
            $this->organizationId = $loggedInUser->organization_id;
        }
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $data =  Summary::getExcelSummaryData($this->startDate, $this->endDate, $this->agents, $this->organizationId, $this->organizationWiseDataFlag);
        return $data;
    }

    public function title(): string
    {
        return 'Summaries';
    }

    public function map($summaries): array
    {
        $row_data = [
            date('d-m-y', strtotime($summaries->summary_date)),
            $summaries->numberOfChats,
            $summaries->averageSession,
            ($summaries->averageInteractions == 0.0)?'0.0':$summaries->averageInteractions,
            $summaries->avgFirstResponseTime,
            $summaries->avgResponseTime,
            $summaries->countChatResolved,
            ($summaries->avgFeedBack == 0.0)? '0.0':$summaries->avgFeedBack,
            $summaries->chatsTransferred,
            $summaries->missedChats,
            $summaries->averageOnlineDuration,
            $summaries->emailSent,
            $summaries->chatsTimeout,
            $summaries->countChatTerminatedByVisitor,
            $summaries->countQueuedChats,
            $summaries->countQueuedLeftChats,
        ];

        if ($this->organizationWiseDataFlag) {
            // Total chats calculation in case of all-admin , So replacing 1 index to total chat count with new one.
            $summaries->numberOfChats += ($summaries->outSessionMissedChats ?? 0 ) + ($summaries->countQueuedLeftChats ?? 0 ) + ($summaries->countOfflineQuery ?? 0 );
            $row_data[1] = $summaries->numberOfChats;
            $row_data[]  = $summaries->countQueuedLeftChats;
            $row_data[]  = $summaries->outSessionMissedChats;
            $row_data[]  = $summaries->countOfflineQuery;
        }

        return $row_data;
    }

    public function headings(): array
    {
        $organizationId = $this->organizationId;
        $headings = [
            default_trans($organizationId.'/dashboard.ui_elements_messages.date', __('default/dashboard.ui_elements_messages.date')),
            default_trans($organizationId.'/dashboard.ui_elements_messages.no_of_chats', __('default/dashboard.ui_elements_messages.no_of_chats')),
            default_trans($organizationId.'/dashboard.ui_elements_messages.avg_session_time', __('default/dashboard.ui_elements_messages.avg_session_time')),
            default_trans($organizationId.'/dashboard.ui_elements_messages.avg_interactions', __('default/dashboard.ui_elements_messages.avg_interactions')),
            default_trans($organizationId.'/dashboard.ui_elements_messages.first_response_time', __('default/dashboard.ui_elements_messages.first_response_time')),
            default_trans($organizationId.'/dashboard.ui_elements_messages.avg_response_time', __('default/dashboard.ui_elements_messages.avg_response_time')),
            default_trans($organizationId.'/dashboard.ui_elements_messages.chat_resolved', __('default/dashboard.ui_elements_messages.chat_resolved')),
            default_trans($organizationId.'/dashboard.ui_elements_messages.feedback', __('default/dashboard.ui_elements_messages.feedback')),
            default_trans($organizationId.'/dashboard.ui_elements_messages.no_of_chat_transferred', __('default/dashboard.ui_elements_messages.no_of_chat_transferred')),
            default_trans($organizationId.'/dashboard.ui_elements_messages.missed_chats', __('default/dashboard.ui_elements_messages.missed_chats')),
            default_trans($organizationId.'/dashboard.ui_elements_messages.online_duration', __('default/dashboard.ui_elements_messages.online_duration')),
            default_trans($organizationId.'/dashboard.ui_elements_messages.email_sent', __('default/dashboard.ui_elements_messages.email_sent')),
            default_trans($organizationId.'/dashboard.ui_elements_messages.chats_timeout', __('default/dashboard.ui_elements_messages.chats_timeout')),
            default_trans($organizationId.'/dashboard.ui_elements_messages.chat_closed_by_visitor', __('default/dashboard.ui_elements_messages.chat_closed_by_visitor')),
            default_trans($organizationId.'/dashboard.ui_elements_messages.queued_visitor', __('default/dashboard.ui_elements_messages.queued_visitor')),
            default_trans($organizationId.'/dashboard.ui_elements_messages.visitor_left_the_queue', __('default/dashboard.ui_elements_messages.visitor_left_the_queue')),
        ];

        if ($this->organizationWiseDataFlag) {
            $headings[] =  default_trans($organizationId.'/dashboard.ui_elements_messages.out_session_timeout', __('default/dashboard.ui_elements_messages.out_session_timeout'));
            $headings[] =  default_trans($organizationId.'/dashboard.ui_elements_messages.out_session_missed_chat', __('default/dashboard.ui_elements_messages.out_session_missed_chat'));
            $headings[] =  default_trans($organizationId.'/dashboard.ui_elements_messages.offline_queries_count', __('default/dashboard.ui_elements_messages.offline_queries_count'));
        }

        return $headings;

    }
}
