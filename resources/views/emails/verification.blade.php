<!-- resources/views/emails/verification.blade.php -->

<!DOCTYPE html>
<html>
<head>
    <title>Email Verification</title>
</head>
<body>
    <h1>Email Verification</h1>
    <p>Your verification code is: <strong>{{ $verificationCode }}</strong></p>
    <p>Please enter this code to verify your email address.</p>
</body>
</html>