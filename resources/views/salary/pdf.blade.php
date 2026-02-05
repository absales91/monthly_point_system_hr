<!DOCTYPE html>
<html>
<head>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            margin: 30px;
        }

        .container {
            border: 1px solid #ddd;
            padding: 15px;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            border-bottom: 1px solid #ddd;
            padding-bottom: 10px;
        }

        .company {
            font-size: 18px;
            font-weight: bold;
        }

        .title {
            text-align: center;
            font-size: 16px;
            font-weight: bold;
            margin: 10px 0;
        }

        .info-box {
            border: 1px solid #ddd;
            padding: 10px;
            margin-bottom: 12px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
        }

        th, td {
            border: 1px solid #ddd;
            padding: 8px;
        }

        th {
            background: #f5f5f5;
        }

        .right {
            text-align: right;
        }

        .net-pay {
            text-align: right;
            font-size: 16px;
            font-weight: bold;
            margin-top: 10px;
        }
    </style>
</head>

<body>

<div class="container">

    <!-- HEADER -->
    <div class="header">
        <div class="company">
            AB Sales
        </div>
        <div>
            Salary Slip
        </div>
    </div>

    <div class="title">
        Salary Slip for 
        {{ $start->format('F Y') }}
    </div>

    <!-- EMPLOYEE INFO BOX -->
    <div class="info-box">
        <strong>Name:</strong> {{ $employee->name }} <br>
        <strong>Department:</strong> {{ $employee->department ?? 'IT & Operation' }} <br>
        <strong>Period:</strong> 
        {{ $start->format('d F Y') }} — {{ $end->format('d F Y') }} <br>
        <strong>Gross Salary:</strong> ₹ {{ number_format($gross,2) }}
    </div>

    <!-- ATTENDANCE TABLE -->
    <table>
        <tr>
            <th>Present Days</th>
            <th>Absent Days</th>
            <th>Weekly Off</th>
            <th>Half Days</th>
        </tr>
        <tr>
            <td>{{ $present }}</td>
            <td>{{ $absent }}</td>
            <td>{{ $weeklyOff }}</td>
            <td>{{ $halfDays }}</td>
        </tr>
    </table>

    <!-- EARNINGS TABLE -->
    <table>
        <tr>
            <th>Earnings</th>
            <th class="right">Amount (₹)</th>
        </tr>
        <tr>
            <td>Salary for Present Days</td>
            <td class="right">{{ number_format($earned,2) }}</td>
        </tr>
        <tr>
            <td><b>Total Earnings</b></td>
            <td class="right"><b>{{ number_format($earned,2) }}</b></td>
        </tr>
    </table>

    <div class="net-pay">
        Net Payable: ₹ {{ number_format($net,2) }}
    </div>

</div>

</body>
</html>
