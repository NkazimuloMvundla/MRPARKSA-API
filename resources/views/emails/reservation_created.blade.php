<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reservation Confirmation</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f9f9f9;
        }
        .container {
            width: 80%;
            margin: auto;
            background: #fff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
        }
        .details {
            margin: 20px 0;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            background: #f4f4f4;
        }
        .details p {
            margin: 0;
            padding: 5px 0;
        }
        .footer {
            text-align: center;
            padding: 10px 0;
            font-size: 0.9em;
            color: #555;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Reservation Confirmation</h1>
        <p>Thank you for your reservation.</p>
        <p>Your confirmation number is: {{ $reservation->confirmation_number }}</p>
        <div class="details">
            <p><strong>Start Time:</strong> {{ \Carbon\Carbon::parse($reservation->start_time)->format('Y-m-d H:i:s') }}</p>
            <p><strong>End Time:</strong> {{ \Carbon\Carbon::parse($reservation->end_time)->format('Y-m-d H:i:s') }}</p>
            <p><strong>Price:</strong> {{ $reservation->price }}</p>
            <p><strong>Vehicle License Number:</strong> {{ $reservation->vehicle_license_number }}</p>
            <p><strong>Vehicle Size:</strong> {{ $reservation->vehicle_size }}</p>
            <p><strong>Address:</strong> {{ $address }}</p>
        </div>
        <div class="footer">
            &copy; {{ date('Y') }} MrParkSA. All rights reserved.
        </div>
    </div>
</body>
</html>
