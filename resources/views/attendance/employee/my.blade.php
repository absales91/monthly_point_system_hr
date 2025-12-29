<h2>My Attendance</h2>

<form method="POST" action="{{ route('attendance.checkin') }}">
@csrf
<button>Check In</button>
</form>

<form method="POST" action="{{ route('attendance.checkout') }}">
@csrf
<button>Check Out</button>
</form>

<table border="1">
<tr>
<th>Date</th>
<th>Status</th>
<th>In</th>
<th>Out</th>
</tr>

@foreach($records as $r)
<tr>
<td>{{ $r->date }}</td>
<td>{{ $r->status }}</td>
<td>{{ $r->check_in }}</td>
<td>{{ $r->check_out }}</td>
</tr>
@endforeach
</table>
