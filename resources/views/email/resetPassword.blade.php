<!DOCTYPE html>
<html>

<head>
    <title>إعادة تعيين كلمة المرور</title>
    <style>
        .button {
            background: #4CAF50;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
        }
    </style>
</head>

<body>
    <h2>مرحبًا!</h2>
    <p>لقد تلقيت هذا البريد لأنك طلبت إعادة تعيين كلمة المرور لحسابك.</p>

    <a href="{{ $resetUrl }}" class="button">
        اضغط هنا لإعادة التعيين
    </a>

    <p>إذا لم تطلب هذا، يمكنك تجاهل البريد.</p>

    <p>شكرًا،<br>{{ config('app.name') }}</p>
</body>

</html>
