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
        }

        .photo {
            width: 118px;
            height: 160px;
            border: 2px solid #000;
            margin-bottom: 10px;
        }

    </style>
</head>

<body>
    <div class="card">
        <img class="photo" src="{{ $photoPath }}" style="width:118px; height:160px; margin-top: 90px" alt="Photo">
        <div class="info" style="margin-left: -2%; font-size: 16px; margin-top: 8px;">{{ $member->name }}</div>
        <div class="info" style="margin-left: -40px; font-size: 16px; margin-top: 6px;">{{ $positionName }}</div>
        {{-- <div class="info">{{ $address}}</div> --}}
        <div class="info-second" style="margin-left: 50px; font-size: 14px; margin-top: 6px;">{{ $fromDate }} - {{ $toDate }}
        </div>
        <div class="info-second" style="margin-left: 70px; font-size: 14px; margin-top: 6px;">{{ $member->mobile1 }}</div>
    </div>
</body>

</html>
