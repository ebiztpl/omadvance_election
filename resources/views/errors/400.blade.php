<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
     <title>400 Page Not Found</title>
    <style>
        body {
            background-color: #f8d7da;
            font-family: Arial, sans-serif;
            color: #721c24;
            text-align: center;
            padding: 50px;
        }
        .container {
            border: 1px solid #f5c6cb;
            background-color: #f8d7da;
            padding: 30px;
            border-radius: 10px;
            display: inline-block;
        }
        h1 {
            font-size: 60px;
            margin-bottom: 10px;
        }
        p {
            font-size: 18px;
            margin: 0;
        }
        a {
            display: inline-block;
            margin-top: 20px;
            color: #721c24;
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>400 - Page Not Found</h1>
    <p>The page you are looking for doesn't exist.</p>
        <a href="{{ url()->previous() }}">Go back</a>

         {{-- @if (config('app.debug'))
            <h3>{{ $exception->getMessage() }}</h3>
            <pre>{{ $exception->getTraceAsString() }}</pre> 
        @endif --}}
    </div>
</body>
</html>