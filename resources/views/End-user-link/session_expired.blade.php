<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Session Expired</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8fafc;
            color: #333;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .container {
            text-align: center;
            padding: 2rem;
            width: 90%;
            max-width: 400px;
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }

        .logo {
            width: 100px;
            margin-bottom: 1.5rem;
        }

        h1 {
            font-size: 1.5rem;
            color: #dc2626;
            margin-bottom: 1rem;
        }

        p {
            font-size: 1rem;
            color: #555;
            margin: 0.5rem 0;
        }

        #sessionTimer {
            font-size: 1.2rem;
            font-weight: bold;
            margin-top: 1rem;
            color: #1e40af;
        }
    </style>
</head>
<body>
    <div class="container">
        <img src="{{ asset('assets/AVXAV Logos/logo_black.png') }}" alt="Logo" class="logo">
        <h1>Sorry, the session ended.</h1>
        <p>Please contact customer support to generate a new link.</p>
        <p>Thanks.</p>

    </div>

</body>
</html>
