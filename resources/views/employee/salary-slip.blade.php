<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Salary Slip</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #111;
        }
        .container {
            width: 100%;
            padding: 20px;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .header h2 {
            margin: 0;
        }
        .sub {
            font-size: 11px;
            color: #444;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        th, td {
            border: 1px solid #ccc;
            padding: 8px;
        }
        th {
            background: #f2f2f2;
            text-align: left;
        }
        .no-border td {
            border: none;
            padding: 5px;
        }
        .right {
            text-align: right;
        }
        .bold {
            font-weight: bold;
        }
        .net {
            background: #e6f7ff;
            font-size: 14px;
            font-weight: bold;
        }
        .footer {
            text-align: center;
            font-size: 10px;
            margin-top: 30px;
            color: #555;
        }
    </style>
</head>

<body>
<div class="container">

    {{-- HEADER --}}
    <div class="header">
        <h2>TECHON LED – A UNIT OF AB SALES</h2>
        <div class="sub">Salary Slip for {{ \Carbon\Carbon::create($year,$month)->format('F Y') }}</div>
    </div>

    {{-- EMPLOYEE DETAILS --}}
    <table class="no-border">
        <tr>
            <td><strong>Employee Name:</strong> {{ $user->name }}</td>
            <td><strong>Employee ID:</strong> {{ $user->id }}</td>
        </tr>
        <tr>
            <td><strong>Email:</strong> {{ $user->email }}</td>
            <td><strong>Role:</strong> {{ ucfirst($user->role) }}</td>
        </tr>
    </table>

    {{-- ATTENDANCE --}}
    <table>
        <tr>
            <th colspan="2">Attendance Summary</th>
        </tr>
        <tr>
            <td>Present Days</td>
            <td class="right">{{ $present }}</td>
        </tr>
        <tr>
            <td>Half Days</td>
            <td class="right">{{ $halfDay }}</td>
        </tr>
        <tr>
            <td>Payable Days</td>
            <td class="right">{{ $payableDays }}</td>
        </tr>
    </table>

    {{-- SALARY --}}
    <table>
        <tr>
            <th>Earnings</th>
            <th class="right">Amount (₹)</th>
        </tr>
        <tr>
            <td>Basic Salary</td>
            <td class="right">{{ number_format($user->basic_salary,2) }}</td>
        </tr>
        <tr>
            <td>Attendance Based Salary</td>
            <td class="right">{{ number_format($salary,2) }}</td>
        </tr>

        <tr>
            <th>Deductions</th>
            <th class="right">Amount (₹)</th>
        </tr>
        <tr>
            <td>Absent / Leave Deduction</td>
            <td class="right">0.00</td>
        </tr>

        <tr class="net">
            <td>Net Payable Salary</td>
            <td class="right">₹ {{ number_format($salary,2) }}</td>
        </tr>
    </table>

    {{-- FOOTER --}}
    <div class="footer">
        This is a system-generated salary slip. No signature required.
    </div>

</div>
</body>
</html>
