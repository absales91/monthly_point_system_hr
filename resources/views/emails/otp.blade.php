<!DOCTYPE html>
<html>
<body style="font-family: Arial; background:#f9fafb; padding:20px;">
  <div style="max-width:500px; margin:auto; background:#ffffff; padding:24px; border-radius:8px;">
    <h2 style="color:#111827;">Email Verification</h2>

    <p>Your OTP code is:</p>

    <div style="
        font-size:26px;
        font-weight:bold;
        letter-spacing:6px;
        text-align:center;
        margin:20px 0;
        color:#2563eb;
    ">
      {{ $otp }}
    </div>

    <p>This OTP is valid for <strong>5 minutes</strong>.</p>

    <p>If you didn’t request this, you can safely ignore this email.</p>

    <hr>

    <p style="font-size:12px; color:#6b7280;">
      © {{ date('Y') }} Techon Connect
    </p>
  </div>
</body>
</html>
