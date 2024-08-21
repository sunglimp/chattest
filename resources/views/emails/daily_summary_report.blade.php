<!DOCTYPE html>
<html>
<head>
    <style>
        table {
            font-family: arial, sans-serif;
            border-collapse: collapse;
            width: 100%;
        }
        th
        {
            font-weight: bold;
            background-color: #dddddd;
        }

        td, th {
            border: 1px solid #0a0302;
            text-align: center;
            padding: 8px;

        }


        body{
            background-color: white!important;
        }
    </style>
    <title>Summary Report</title>
</head>

<body>
<br>
<h1 style="font-size: 28px">{{ucfirst(config('constants.DAILY_SUMMARY_REPORT_SUBJECT'))}} | {{$date}}</h1>
<div>
    <p>Dear Team,</p>
    <p>Please find below the Daily Session report for {{$date}}.</p>

</div>

<br>
<table>
    <tr>
        <th rowspan=2>S.No.</th>
        <th rowspan=2>Client Name</th>
        <th rowspan=2>Alloted Seats</th>
        <th rowspan=2>Users Created</th>
        <th rowspan=2>Users Logged In</th>
        <th colspan=3>Sessions</th>
    </tr>
    <tr>
        <th>Today</th>
        <th>Monthly</th>
        <th>Lifetime</th>
    </tr>


      @foreach($reportdata as $k=>$v)
    <tr>
        <td>{{$k+1}}</td>
        <td>{{$v->company_name}}</td>
        <td>{{$v->seat_alloted}}</td>
        <td>{{$v->user_id_count}}</td>
        <td>{{$v->agents_online}}</td>
        <td>{{$v->today_chat}}</td>
        <td>{{$v->month_chat}}</td>
        <td>{{$v->lifetime_chat}}</td>
    </tr>
          @endforeach

</table>
<br>
<br>
<h3>Regards,</h3>
<h3>Team Surbo Chat</h3>

</body>
</html>
