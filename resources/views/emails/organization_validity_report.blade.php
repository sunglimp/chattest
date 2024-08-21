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
    <title>Account Validity Summary Report</title>
</head>

<body>
<br>
<h1 style="font-size: 28px">Account Validity Summary Report | {{$date}}</h1>
<div>
    <p>Dear Team,</p>
    <p>Please find below the details of the account which are getting expired.</p>

</div>

<br>
<table>
    <thead>
    <tr>
        <th>S.No.</th>
        <th>Organization Name</th>
        <th>Contact Name</th>
        <th>Contact</th>
        <th>Validity Date(UTC)</th>       
    </tr>
    </thead>
    @php $k=0; @endphp
    <tbody>
    @foreach($body as $k=>$v)
    <tr>
        <td>{{$k+1}}</td>
        <td>{{$v->company_name}}</td>
        <td>{{$v->contact_name}}</td>
        <td>{{$v->mobile_number}}</td>
        <td>{{$v->validity_date}}</td>
    </tr>
    @endforeach
    </tbody>
</table>
<br>
<br>
<h3>Regards,</h3>
<h3>Team Surbo Chat</h3>

</body>
</html>
