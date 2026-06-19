<!DOCTYPE html>
<html lang="en">
<head>
    <title>Delete Account – Attendance App</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif;
            background: #f4f6f8;
            margin: 0;
            padding: 20px;
        }

        .card {
            max-width: 520px;
            margin: auto;
            background: #ffffff;
            border-radius: 12px;
            padding: 24px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.06);
        }

        h2 {
            margin-top: 0;
            color: #1f2937;
            text-align: center;
        }

        h4 {
            color: #374151;
            margin-bottom: 8px;
        }

        p, li {
            color: #4b5563;
            font-size: 14px;
            line-height: 1.6;
        }

        ul {
            padding-left: 18px;
        }

        .alert-success {
            background: #ecfdf5;
            color: #065f46;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 16px;
            font-size: 14px;
        }

        label {
            font-size: 14px;
            font-weight: 600;
            color: #374151;
        }

        input[type="email"] {
            width: 100%;
            padding: 12px;
            margin-top: 6px;
            border-radius: 8px;
            border: 1px solid #d1d5db;
            font-size: 14px;
        }

        input[type="email"]:focus {
            outline: none;
            border-color: #2563eb;
        }

        button {
            width: 100%;
            background: #dc2626;
            color: #ffffff;
            padding: 12px;
            margin-top: 16px;
            border: none;
            border-radius: 8px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
        }

        button:hover {
            background: #b91c1c;
        }

        .footer {
            margin-top: 20px;
            text-align: center;
            font-size: 12px;
            color: #6b7280;
        }
    </style>
</head>

<body>

<div class="card">
    <h2>Delete Account & Data – Techon Connect App</h2>

    <p>
        You can request deletion of your account and associated data from the Attendance App.
    </p>

    <h4>How to Delete Your Account</h4>
    <ul>
        <li>Open the Techon Connect App → Profile → Settings → Delete Account</li>
        <li>OR submit the request using the form below</li>
    </ul>

    <h4>Data That Will Be Deleted</h4>
    <ul>
        <li>User profile information</li>
        <li>Login credentials</li>
        <li>Attendance records</li>
    </ul>

    <h4>Data Retention</h4>
    <p>
        Some records may be retained if required by law or company policy.
    </p>

    <h4>Processing Time</h4>
    <p>
        Account deletion requests are processed within <strong>7–30 days</strong>.
    </p>

    @if(session('success'))
        <div class="alert-success">
            {{ session('success') }}
        </div>
    @endif

    <form method="POST" action="{{ route('delete-account.store') }}">
        @csrf
        <label>Email (registered with app)</label>
        <input type="email" name="email" required placeholder="your@email.com">

        <button type="submit">
            Submit Delete Request
        </button>
    </form>

    <div class="footer">
        © {{ date('Y') }} Techon Connect App
    </div>
</div>

</body>
</html>
