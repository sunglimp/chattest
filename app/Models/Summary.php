<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Organization;
use App\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;

class Summary extends Model
{
    public $timestamps = false;

    protected $guarded = [];

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function agent()
    {
        return $this->belongsTo(User::class);
    }

    public static function updateSummary(int $organizationId, int $agentId = null, string $summaryDate, array $update)
    {
        try {
            $update = array_map(function($val) {
                $val = is_null($val) ? 0.00 : $val;
                return $val;
            },$update);

            self::updateOrCreate([
                'organization_id' => $organizationId,
                'agent_id'        => $agentId,
                'summary_date'    => $summaryDate
                    ], $update);
            return true;
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    /**
     * Function to get Summary Data.
     *
     * @param integer $days
     * @throws \Exception
     */
    public static function getSummaryData($requestParams, $organizationId, $agents, $isDashboard = false, $organizationWiseDataFlag = false)
    {
        try {

            if (empty($requestParams)) {
                $startDate = date('Y-m-d', strtotime('-'.(config('constants.DASHBOARD_DEFAULT_DAYS')-1).' day'));
                $endDate = date('Y-m-d');
            } else {
                $date = $requestParams['date'];
                $dateArr = explode('- ', $date);
                $startDate = date('Y-m-d', strtotime($dateArr[0]));
                $endDate = date('Y-m-d', strtotime($dateArr[1]));
            }



            $uniqueChatsCount = ChatChannel::getUniqueChatsCount($startDate, $endDate, $agents);
            //unique chats count can't be calculated based on daily summary logic
            $orgSpecificData = self::getOrganizationSpecificData($organizationId, $startDate, $endDate, $organizationWiseDataFlag);

            $query  = self::select(
                DB::raw('SUM(count_chat) as numberOfChats'),
                DB::raw($uniqueChatsCount . ' as numberOfUniqueChats'),
                DB::raw('AVG(avg_session) as averageSession'),
                DB::raw('SUM(count_chat_resolved) as chatsResolved'),
                DB::raw('AVG(avg_chat) as averageChats'),
                DB::raw('AVG(avg_interaction) as averageInteractions'),
                DB::raw('SUM(count_chat_transferred) as chatsTransferred'),
                DB::raw('SUM(count_chat_terminated_by_visitor) as chatsClosedByVisitor'),
                DB::raw('"' .$orgSpecificData['outSessionTimeouts'] . '" as outSessionTimeouts'),
                DB::raw('SUM(count_insession_timeout) as chatsTimeout'),
                DB::raw('AVG(avg_online_duration) as averageOnlineDuration'),
                DB::raw('SUM(count_chat_missed) as missedChats'),
                DB::raw('"' .$orgSpecificData['outSessionMissedChats'] . '" as outSessionMissedChats'),
                DB::raw('SUM(count_email_sent) as emailSent'),
                DB::raw('AVG(avg_response_time) as avgResponseTime'),
                DB::raw('AVG(avg_first_response_time) as avgFirstResponseTime'),
                DB::raw('AVG(avg_feedback) as avgFeedBack'),
                DB::raw('"' .$orgSpecificData['avgFirstResponseTimeToVisitor'] . '" as avgFirstResponseTimeToVisitor'),
                DB::raw('"' .$orgSpecificData['countOfflineQuery'] . '" as countOfflineQuery')
            )->whereBetween('summary_date', ["$startDate", "$endDate"]);

            if (!empty($agents)) {
                if (is_array($agents)) {
                    $query->whereIn('agent_id', $agents);
                } else {
                    $query->where('agent_id', $agents);
                }
            }
            if ($isDashboard === true) {
                $data = $query->groupBy('summary_date')->get();
                return $data;
            } else {
                $data = $query->first();
                return self::format($data);
            }

        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    /**
     * Get offline counts
     *
     * @param int $organizationId
     * @param string $startDate
     * @param string $endDate
     * @return int
     */
    public static function getOfflineQueriesCounts($organizationId, $startDate, $endDate)
    {
        $query  = self::select(DB::raw('SUM(count_offline_query) as countOfflineQuery'))
            ->where('organization_id', $organizationId)
            ->whereBetween('summary_date', ["$startDate", "$endDate"]);
        $result = $query->groupBy('summary_date')->first();
        return $result->countOfflineQuery;
    }


    /**
     * FUnction to format data to be shown in dashboard.
     *
     */
    private static function format($data)
    {
        try {
                $data->averageSession = convert_average_time($data->averageSession);
                $data->averageChats = round($data->averageChats, 2);
                $data->averageInteractions = round($data->averageInteractions, 2);
                $data->averageOnlineDuration = convert_average_time($data->averageOnlineDuration);
                $data->avgResponseTime = convert_average_time($data->avgResponseTime);
                $data->avgFirstResponseTime = convert_average_time($data->avgFirstResponseTime);
                $data->avgFeedBack = round($data->avgFeedBack, 2);
                $data->avgFirstResponseTimeToVisitor = convert_average_time($data->avgFirstResponseTimeToVisitor);

            return $data;
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    /**
     *
     * @param unknown $chatData
     * @return NULL[]
     */
    private static function formatChatChartData($chatData, $key, $startDate, $endDate)
    {
        $labels = [];
        $data = [];
        $dates = get_dates_between_range($startDate, $endDate);

        $chatData = $chatData->toArray();

        $data = self::formatData($dates, $chatData, $key);

        $dates = get_dates_between_range($startDate, $endDate, 'd M');
        $result['categories'] = $dates;
        $result['data'] = $data;

        return $result;
    }

    /**
     * Function to get chat termination data.
     *
     * @param integer $days
     */
    public static function getChatTerminationData($startDate, $endDate, $organizationId, $agents)
    {
        try {
            $terminationByAgent = self::getSummaryChartData('count_chat_terminated_by_agent', $startDate, $endDate, $organizationId, $agents);
            $terminationByVisitor = self::getSummaryChartData('count_chat_terminated_by_visitor', $startDate, $endDate, $organizationId, $agents);
            return array(
                'terminationByAgent' => $terminationByAgent,
                'terminationByVisitor' => $terminationByVisitor
            );
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    /**
     * Function to get queued data.
     *
     * @param string $days
     * @param integer $organizationId
     * @throws \Exception
     */
    public static function getChatQueuedData($startDate, $endDate, $organizationId, $agents)
    {
        try {
            return array(
                'queuedVisitor' => self::getSummaryChartData('count_queued_visitor', $startDate, $endDate, $organizationId, array(), true),
                'queuedLeft' => self::getSummaryChartData('count_queued_left', $startDate, $endDate, $organizationId, array(), true),
                'enteredChat' => self::getSummaryChartData('count_entered_chat', $startDate, $endDate, $organizationId, $agents),
            );
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    /**
     *
     * @param unknown $key
     * @param unknown $days
     * @param unknown $organizationId
     * @param boolean $isQueue
     * @throws \Exception
     * @return NULL[]
     */
    public static function getSummaryChartData($key, $startDate, $endDate, $organizationId, $agents, $isQueue = false)
    {
        try {
            $startDate = date('Y-m-d', strtotime($startDate));
            $endDate = date('Y-m-d', strtotime($endDate));
            $query = self::select(DB::raw('CAST(SUM('.$key.') as UNSIGNED) as '.$key), 'summary_date')
                            ->where('organization_id', $organizationId);
            if ($isQueue === true) {
                $query->whereNull('agent_id');
            }

            if (!empty($agents)) {
                if (is_array($agents)) {
                    $query->whereIn('agent_id', $agents);
                } else {
                    $query->where('agent_id', $agents);
                }
            }
             $chatData = $query->groupBy('summary_date')
                                 ->havingRaw('summary_date BETWEEN "'.$startDate.'" AND "'.$endDate.'"')
                                ->get();

            return self::formatChatChartData($chatData, $key, $startDate, $endDate);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    /**
     * FUnction to gte availability data.
     *
     * @param integer $organizationId
     * @param integer $days
     * @throws \Exception
     * @return array availability data
     */
    public static function getAvailabilityData($requestParams, $organizationId, $agents)
    {
        try {

            $chatResult         = array();
            $onlineResult       = array();
            $chatFinalResult    = array();
            $onlineFinalResult  = array();
            $awaitingChatResult = array();
            $activeChatresult   = array();
            $toShowChatCount    = false;

            if (empty($requestParams)) {
                $startDate = date('Y-m-d', strtotime('-'.(config('constants.DASHBOARD_DEFAULT_DAYS')-1).' day', strtotime(date('Y-m-d'))));
                $endDate = date('Y-m-d');
            } else {
                $date      = $requestParams['date'];
                $dateArr   = explode('- ', $date);
                $startDate = date('Y-m-d', strtotime($dateArr[0]));
                $endDate   = date('Y-m-d', strtotime($dateArr[1]));
            }

            $chatData   = self::getChatData($organizationId, $startDate, $endDate, 'count_chat', $agents);
            $onlineData = self::getChatData($organizationId, $startDate, $endDate, 'online_duration', $agents);

            $dates = get_dates_between_range($startDate, $endDate);

            $chatCellData   = $chatData['cellData'];
            $onlineCellData = $onlineData['cellData'];

            $chatResult = self::formatAvailabilityData($chatCellData, 'count_chat');

            $dates = get_dates_between_range($startDate, $endDate);

            foreach ($chatResult as $key => $res) {
                $chatResult[$key] = self::formatData($dates, $res, 'count_chat');
            }

            $onlineResult = self::formatAvailabilityData($onlineCellData, 'online_duration');
            foreach ($onlineResult as $key => $res) {
                $onlineResult[$key] = self::formatData($dates, $res, 'online_duration');
            }

            $chatFinalResult   = self::createAvailabilityResult($chatData['headerData'], 'count_chat', $chatResult);
            $onlineFinalResult = self::createAvailabilityResult($onlineData['headerData'], 'online_duration', $onlineResult);


            // if current date is selected then calculate the active and awaiting chats count
            if (!empty($agents)) {
                if (!is_array($agents)) {
                    $agents = [$agents];
                }
            }
            $currentDate = Carbon::now()->toDateString();
            if ($startDate == $currentDate && $endDate == $currentDate && !Gate::allows('superadmin') && !Gate::allows('associate')) {
                $toShowChatCount    = true;
                $awaitingChatResult = ChatChannel::getChatCountAgent($agents, ChatChannel::CHANNEL_STATUS_UNPICKED);
                $activeChatresult   = ChatChannel::getChatCountAgent($agents, ChatChannel::CHANNEL_STATUS_PICKED);
            }

            return array(
                'chatData'         => $chatFinalResult,
                'onlineData'       => $onlineFinalResult,
                'toShowChatCount'  => $toShowChatCount,
                'awaitingChatData' => $awaitingChatResult,
                'activeChatData'   => $activeChatresult,
                'dates'            => get_dates_between_range($startDate, $endDate, 'd M')
            );
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    /**
     * Function to get chat data.
     *
     * @param integer $days
     * @param integer $organizationId
     * @throws \Exception
     *
     * @return \Illuminate\Support\Collection[]
     */
    private static function getChatData($organizationId, $startDate, $endDate, $field, $agents)
    {
        try {
            $query1 = self::getChatAvailabilityData($startDate, $endDate, $agents, $field);

            $cellData = $query1->get();


            $query = self::select('name', 'agent_id', DB::raw('sum('.$field.') as '.$field))
                        ->join('users', 'users.id', '=', 'agent_id')
                        ->whereBetween('summary_date', ["$startDate", "$endDate"])
                        ->groupBy('agent_id');

            if (!empty($agents)) {
                if (is_array($agents)) {
                    $query->whereIn('agent_id', $agents);
                } else {
                    $query->where('agent_id', $agents);
                }
            }

            $headerData = $query->get();

            $chatData = array(
                'cellData' => $cellData->toArray(),
                'headerData' => $headerData->toArray()
            );

            return $chatData;
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    /**
     * Function to get data shown to dashboard.
     * @param Request $request
     * @param integer $loggedInUserId
     * @throws \Exception
     * @return array
     */
    public static function getDashBoardData($request, $loggedInUserId)
    {
        try {
            $days = config('constants.DASHBOARD_DEFAULT_DAYS');

            if (isset($request->date) && !empty($request->date)) {
                $date = $request->date;
                $dateArr = explode('- ', $date);
                $startDate = $dateArr[0];
                $endDate = $dateArr[1];
                $startDate = Carbon::parse($startDate);
                $endDate = Carbon::parse($endDate);
                $days=  $startDate->diffInDays($endDate)+1;
            }
            $user = User::find($loggedInUserId);
            $permission = Permission::find(config('constants.PERMISSION.DOWNLOAD-REPORT'));

            $isExportAllowed = $user->can('check', $permission);

            $agentIds = $request->agentIds ?? $loggedInUserId;
            if ($user->role_id == config('constants.user.role.admin')) {

                $teamData =  User::where('organization_id', $user->organization_id)->whereNotIn('role_id', config('config.ADMIN_ROLE_IDS'))->orderBy('name', 'asc')->get()->toArray();
                $teamData = array_merge($user->toArray(), ['child' => $teamData]);

            } else {

                $teamData = get_direct_reportees($loggedInUserId);
            }

            if (empty($teamData['child']) || Gate::allows('associate')) {
                $isTeam = false;
            } else {
                $isTeam = true;
            }
            $onlineDuration = UserLogin::getUserOnlineDuration($loggedInUserId);

            $onlineDuration = explode(":", $onlineDuration);
            $onlineDuration['hours'] = $onlineDuration[0] ?? 00;
            $onlineDuration['min'] = $onlineDuration[1] ?? 00;
            $onlineDuration['seconds'] = $onlineDuration[2] ?? 00;

            return array(
                'days' => $days,
                'agentIds' => $request->agentIds ?? 0,
                'isTeam' => $isTeam,
                'teamData' => $teamData,
                'onlineDuration' => $onlineDuration,
                'isExportAllowed' => $isExportAllowed
            );
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    /**
     *
     * @param unknown $startDate
     * @param unknown $endDate
     * @param unknown $agents
     * @param unknown $field
     * @throws \Exception
     * @return \Illuminate\Database\Query\Builder
     */
    public static function getChatAvailabilityData($startDate, $endDate, $agents, $field, $isExcel = false)
    {
        try {
            if ($isExcel === true) {
                $query = self::select('agent_id', $field, 'summary_date', 'name');
            } else {
                $query = self::select('agent_id', 'summary_date', $field);
            }
            $query->whereBetween('summary_date', ["$startDate", "$endDate"]);



            if (!empty($agents)) {
                if (is_array($agents)) {
                    $query->whereIn('agent_id', $agents);
                } else {
                    $query->where('agent_id', $agents);
                }
            }

            if ($isExcel === true) {
                $query->join('users', 'users.id', '=', 'agent_id');
            }
            return $query;
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    /**
     * Function for export summery data from dashboard
     *
     * @param string $startDate
     * @param string $endDate
     * @param array $agents
     * @param int $organizationId
     * @param boolean $organizationWiseDataFlag
     * @return collections
     *
     * @throws \Exception
     */
    public static function getExcelSummaryData($startDate, $endDate, $agents, $organizationId, $organizationWiseDataFlag = false)
    {
        try {
            $query = self::getSummaryQuery($startDate, $endDate, $agents, $organizationId, $organizationWiseDataFlag);

            //This calculation is for  superadmin and admin its organization base only
            if ($organizationWiseDataFlag) {
                $query->addSelect(DB::raw('IFNULL(SUM(count_offline_query), 0) as countOfflineQuery'));
                $query->addSelect(DB::raw('IFNULL(SUM(IF(agent_id IS NULL, count_chat_missed, NULL)),0) as outSessionMissedChats'));
            }
            $data = $query->groupBy('summary_date')->get();

            $data->map(function (&$value) {
                self::format($value);
            });
            return $data;
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    /**
     *
     * @param type $startDate
     * @param type $endDate
     * @param type $agents
     * @param type $organizationId
     *
     * @return type
     */
    private static function getSummaryQuery($startDate, $endDate, $agents, $organizationId)
    {
        $query  = self::select(
            DB::raw('SUM(count_chat) as numberOfChats'),
            DB::raw('AVG(IF(agent_id is null && avg_session = 0.00, NULL, avg_session)) as averageSession'),
            DB::raw('SUM(count_chat_resolved) as chatsResolved'),
            DB::raw('AVG(IF(agent_id is null && avg_chat = 0.00, NULL, avg_chat)) as averageChats'),
            DB::raw('AVG(IF(agent_id is null && avg_interaction = 0.00, NULL, avg_interaction)) as averageInteractions'),
            DB::raw('SUM(count_chat_transferred) as chatsTransferred'),
            DB::raw('AVG(IF(agent_id is null && avg_online_duration = 0.00, NULL, avg_online_duration)) as averageOnlineDuration'),
            DB::raw('IFNULL(SUM(IF(agent_id IS NOT NULL, count_chat_missed, NULL)),0) as missedChats'),
            DB::raw('SUM(count_email_sent) as emailSent'),
            DB::raw('AVG(IF(agent_id is null && avg_response_time = 0.00, NULL, avg_response_time)) as avgResponseTime'),
            DB::raw('AVG(IF(agent_id is null && avg_first_response_time = 0.00, NULL, avg_first_response_time)) as avgFirstResponseTime'),
            'summary_date',
            DB::raw('SUM(count_chat_terminated_by_agent) as countChatTerminatedByAgent'),
            DB::raw('SUM(count_insession_timeout) as chatsTimeout'),
            DB::raw('SUM(count_chat_terminated_by_visitor) as countChatTerminatedByVisitor'),
            DB::raw('SUM(count_queued_visitor) as countQueuedChats'),
            DB::raw('SUM(count_queued_left) as countQueuedLeftChats'),
            DB::raw('SUM(count_chat_resolved) as countChatResolved'),
            DB::raw('AVG(IF(agent_id is null && avg_feedback = 0.00, NULL, avg_feedback)) as avgFeedBack')
        );

        if (!empty($agents)) {
            if (is_array($agents)) {
                $query->where(function ($query) use ($agents, $organizationId) {
                    $query->whereIn('agent_id', $agents)
                    ->orWhere(function ($query1) use ($organizationId) {
                        $query1->where('organization_id', $organizationId)
                        ->whereNull('agent_id');
                    });
                });
            } else {
                $query->where(function ($query) use ($agents, $organizationId) {
                    $query->where('agent_id', $agents)
                    ->orWhere(function ($query1) use ($organizationId) {
                        $query1->where('organization_id', $organizationId)
                        ->whereNull('agent_id');
                    });
                });
            }
        }
            $query->whereBetween('summary_date', ["$startDate", "$endDate"]);
           return $query;
    }

    /**
     * Format data if no data available for particular dates.
     *
     * @param array $dates
     * @param array $chatData
     * @param string $key
     * @return number[]|mixed[]
     */
    private static function formatData($dates, $chatData, $key)
    {
        $data = [];
        foreach ($dates as $val) {
            $notFound = true;
            foreach ($chatData as $chat) {
                $notFound = false;
                $chat = (array)($chat);
                if ($val == $chat['summary_date']) {
                    $data[] = $chat[$key];
                    $notFound = false;
                    break;
                } else {
                    $notFound = true;
                }
            }
            if ($notFound == true) {
                $data[] = 0;
            }
        }
        return $data;
    }

    /**
     * Format data to be shown for availability.
     *
     * @param array $chatCellData
     * @param string $key
     * @throws \Exception
     * @return array|NULL[]
     */
    private static function formatAvailabilityData($chatCellData, $key)
    {
        try {
            $agentIDs = array();
            $chatResult = [];
            foreach ($chatCellData as $chat) {
                $chat = (array)$chat;
                if (in_array($chat['agent_id'], $agentIDs)) {
                    $arr = array(
                        'summary_date' => $chat['summary_date'],
                         $key   => $chat[$key]
                    );
                    array_push($chatResult[$chat['agent_id']], $arr);

                } else {
                    array_push($agentIDs, $chat['agent_id']);
                    $chatResult[$chat['agent_id']][] = array(
                        'summary_date' => $chat['summary_date'],
                        $key   => $chat[$key]
                    );
                }
            }
            return $chatResult;
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    /**
     * Function to create result.
     *
     * @param array $availabilityData
     * @param string $key
     * @throws \Exception
     */
    private static function createAvailabilityResult($availabilityData, $key, $countData)
    {
        try {
            $result = [];
            foreach ($availabilityData as $duration) {
                $result[] = array (
                    'name' => $duration['name'],
                    'sum'  => $duration[$key],
                    'count' => $countData[$duration['agent_id']]
                );
            }
            return $result;
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    /**
     * Function to get daily report.
     *
     * @throws \Exception
     */
    public static function getDailyReport()
    {
        try {
            $dailySummaryData = DB::select('CALL usp_get_daily_summary()');
            return $dailySummaryData;
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    /**
     * Get organization specific data
     *
     * @param type $requestParams
     * @param int $organizationId
     * @param string $startDate
     * @param string $endDate
     * @param boolean $organizationWiseDataFlag
     * @return array
     */
    public static function getOrganizationSpecificData($organizationId, $startDate, $endDate, $organizationWiseDataFlag)
    {
        $data = [];
        $data['outSessionTimeouts'] = 0;
        $data['avgFirstResponseTimeToVisitor'] = 0;
        $data['countOfflineQuery'] = 0;
        $data['outSessionMissedChats'] = 0;
        //More can be added similarly
        if ($organizationWiseDataFlag) {
            $data = self::select(
                DB::raw('SUM(count_queued_left) as outSessionTimeouts'),
                DB::raw('IFNULL(Avg(avg_first_response_to_visitor), 0) as avgFirstResponseTimeToVisitor'),
                DB::raw('SUM(count_chat_missed) as outSessionMissedChats'),
                DB::raw('IFNULL(SUM(count_offline_query), 0) as countOfflineQuery')
            )->where('organization_id', $organizationId)
             ->whereNull('agent_id')
            ->whereBetween('summary_date', ["$startDate", "$endDate"])
            ->first();
        }
        return $data;
    }
}
