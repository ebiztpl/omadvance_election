<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Member Card</title>
    <style>
        .card {
            width: 300px;
            height: 400px;
            background-image: url('{{ $backgroundPath }}');
            background-size: fill;
            text-align: center;
            padding-top: 65px;
            font-family: 'DejaVu Sans', sans-serif;
        }

        .photo {
            width: 118px;
            height: 160px;
            border: 2px solid #000;
            margin-bottom: 10px;
        }

        .info {
            margin-top: 8px;
            font-size: 12px;
        }
    </style>
</head>

<body>
    <div class="card">
        <img class="photo" src="{{ $photoPath }}" style="width:118px; height:160px; margin-top: 90px" alt="Photo">
        <div class="info">{{ $member->name }}</div>
        <div class="info">{{ $member->membership }}</div>
        <div class="info">{{ $address}}</div>
        <div class="info">31-Dec-2027</div>
        <div class="info">{{ $member->mobile1 }}</div>
    </div>
</body>

</html>