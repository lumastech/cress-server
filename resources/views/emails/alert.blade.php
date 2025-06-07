<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Emergence message</title>
</head>
<body style="margin: 0; padding: 0; font-family: Arial, sans-serif; background-color: #f5f5f5;">
    <div class="container" style="max-width: 600px; margin: 0 auto; background-color: #ffffff;">
        <div class="header" style="background-color: #e74c3c; padding: 20px; text-align: center;">
            <h1 style="color: white; margin: 0; font-size: 24px;">Emergency Alert</h1>
        </div>
        <div class="content" style="padding: 20px;">
            <div class="field" style="margin-bottom: 15px;">
                <strong style="color: #333; display: inline-block; width: 80px;">Name:</strong> {{ $data['name'] }}
            </div>
            <div class="field" style="margin-bottom: 15px;">
                <strong style="color: #333; display: inline-block; width: 80px;">Email:</strong> {{ $data['email'] }}
            </div>
            <div class="field" style="margin-bottom: 15px;">
                <strong style="color: #333; display: inline-block; width: 80px;">Phone:</strong> {{ $data['phone'] }}
            </div>
            <div class="field" style="margin-bottom: 20px; padding: 15px; background-color: #f8f9fa; border-left: 4px solid #e74c3c;">
                <h2 style="color: #e74c3c; margin-top: 0;">URGENT: {{$data['alert']}}</h2>
                <p style="margin-bottom: 10px;"><strong>LOCATION:</strong> <a href="{{ $data['location'] }}" target="_blank" rel="noopener noreferrer" style="color: #3498db; text-decoration: none;">Open Google Map</a></p>
                <p style="margin-bottom: 0; color: #333;">{{ $data['message'] }}</p>
            </div>
            <div class="field" style="margin-top: 30px;">
                <hr style="border-top: 1px solid #eee; margin: 24px 0;">
                <p class="footer" style="color: #777; font-size: 14px; line-height: 1.5;">If you have received this message it means {{ $data['name'] }} added you as their emergency contact person in <em style="font-style: italic;">Community Health Helper App</em></p>
            </div>
        </div>
        <div class="footer" style="background-color: #f5f5f5; padding: 15px; text-align: center; font-size: 12px; color: #777;">
            &copy; 2025 CRESS. All rights reserved.
        </div>
    </div>
</body>
</html>
