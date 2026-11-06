<!DOCTYPE html>
<html>
<head>
    <title>Kode OTP Anda</title>
</head>
<body style="font-family: Arial, sans-serif; background-color: #f4f7f6; padding: 30px;">
    <div style="max-w-400px; margin: 0 auto; background: #ffffff; padding: 30px; border-radius: 10px; border-top: 5px solid #2563eb; text-align: center; box-shadow: 0 4px 6px rgba(0,0,0,0.05);">
        <h2 style="color: #1e293b; margin-bottom: 10px;">Reset Password SCP</h2>
        <p style="color: #64748b; font-size: 14px;">Halo, <strong>{{ $userName }}</strong>,</p>
        <p style="color: #64748b; font-size: 14px;">Kami menerima permintaan untuk mereset password akun Anda. Berikut adalah kode rahasia OTP Anda:</p>
        
        <div style="margin: 30px 0;">
            <span style="background: #f1f5f9; color: #0f172a; font-size: 28px; font-weight: bold; padding: 15px 25px; border-radius: 8px; letter-spacing: 5px;">{{ $otpCode }}</span>
        </div>
        
        <p style="color: #ef4444; font-size: 12px; font-weight: bold;">PENTING: Jangan berikan kode ini kepada siapapun. Kode ini hanya berlaku selama 15 menit.</p>
    </div>
</body>
</html>