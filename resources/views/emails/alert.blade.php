<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Emergence message</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f7f7f7;
            padding: 20px;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #fff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
        }
        .content {
            margin-bottom: 20px;
        }
        .field {
            margin-bottom: 10px;
        }
        .field strong {
            display: inline-block;
            width: 100px;
        }
        .footer {
            text-align: center;
            font-size: 12px;
            color: #777;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Emergence message</h1>
        </div>
        <div class="content">
            <div class="field">
                <strong>Name:</strong> {{ $user['name'] }}
            </div>
            <div class="field">
                <strong>Email:</strong> {{ $user['email'] }}
            </div>
            <div class="field">
                <strong>Phone:</strong> {{ $user['phone'] }}
            </div>
            <div class="field">
                <h2>Hi {{$user['sos']['name']}}</h2>
                <p>{{ $user['sos']['message'] }}</p>
            </div>
            <div class="field">
                <hr style="border-top:1px; margin:24px 0">
                <p class="footer">If you have received this message it means {{ $user['name'] }} added you as there Emergence contact person in <em> Community Health Helper App</em> </p>
            </div>
        </div>
        <div class="footer">
            &copy; 2025 CRESS. All rights reserved.
        </div>
    </div>
</body>
</html>
