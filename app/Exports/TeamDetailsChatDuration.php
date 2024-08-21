<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use App\Models\Summary;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Illuminate\Support\Facades\Auth;

class TeamDetailsChatDuration implements FromCollection, WithTitle, WithHeadings
{
    private $requestParams;
    
    private $startDate;
    
    private $endDate;
    
    private $agents;
    
    private $organizationId;
    
    /**
     *
     */
    public function __construct($startDate, $endDate, $agents, $organizationId)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->agents = $agents;
        $this->organizationId = $organizationId;
    }
    
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $result = array();
        $agentId = array();
        $agentResult = array();
        $query = Summary::getChatAvailabilityData($this->startDate, $this->endDate, $this->agents, 'online_duration', true);
     
        $data =  $query->get()->toArray();
        $summaryDates = array();
        foreach ($data as $value) {
            if (!in_array($value['summary_date'], $summaryDates)) {
                $result[] = $value['summary_date'];
                array_push($summaryDates, $value['summary_date']);
            }
        }
        foreach ($data as $val) {
            if (!in_array($val['agent_id'], $agentId)) {
                $agentResult[$val['agent_id']] = array(
                    $val['name']
                );
                array_push($agentId, $val['agent_id']);
        }
        }
                $i=0;
                foreach($summaryDates as $date){
                  $i++;
                  foreach($agentId as $id){
                   $agentResult[$id][$i] = '00:00';   
                  }
                 foreach($data as $val){  
                 if($val['summary_date']== $date ){
                     $agentResult[$val['agent_id']][$i] = convert_average_time($val['online_duration'], true);
                     
                 }
                 
         }
            }

        return collect($agentResult);
    }
    
    public function title(): string
    {
        return 'Chat Duration';
    }
    
    public function headings(): array
    {
        $organizationId = ($this->organizationId!='') ? $this->organizationId : Auth::user()->organization_id;
        $result[] =  default_trans($organizationId.'/dashboard.ui_elements_messages.agent_name', __('default/dashboard.ui_elements_messages.agent_name'));
        
        $query = Summary::getChatAvailabilityData($this->startDate, $this->endDate, $this->agents, 'online_duration', true);
        $data =  $query->get()->toArray();
       
        $summaryDates = array();
        foreach ($data as $value) {
            if (!in_array($value['summary_date'], $summaryDates)) {
                $result[] = $value['summary_date'];
                array_push($summaryDates, $value['summary_date']);
            }
        }
        return $result;
    }
}
