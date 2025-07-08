<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Responsibility Cards</title>
    <style>

        .card {
            width: 300px;
            height: 400px;
            background-image: url("file://{{ $backgroundPath }}");
            background-size: cover;
            background-repeat: no-repeat;
            text-align: center;
            padding-top: 65px;
            margin: 0 auto;
            position: relative;
        }

        .photo {
            width: 118px;
            height: 160px;
            border: 2px solid #000;
            margin-bottom: 50px;
        }

        .info {
            margin-top: 5px;
            font-size: 12px;
        }

        .page-break {
            page-break-after: always;
        }
    </style>
</head>

<body>
    @foreach ($cards as $card)
    <div class="card">
        <img class="photo" src="{{ $card['photoPath'] }}" alt="Photo">
        <div class="info">नाम: {{ $card['name'] }}</div>
        <div class="info">दायित्व: {{ $card['position'] }}</div>
        <div class="info">{{ $card['workarea'] }}</div>
        <div class="info">मान्य तिथि: 31-Dec-2027</div>
        <div class="info">मो./संकल्प क्र.: {{ $card['mobile'] }}</div>
        <div class="info">पता: {{ $card['address'] }}</div>
    </div>
    <div class="page-break"></div>
    @endforeach
</body>

</html>