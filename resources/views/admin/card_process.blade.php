@php
$pageTitle = 'सदस्यता फाॅर्म डेटा';
$breadcrumbs = [
'एडमिन' => '#',
'सदस्यता फाॅर्म डेटा' => '#'
];
@endphp

@php
$backgroundUrl = asset('assets/images/bg.jpg');
@endphp


@extends('layouts.app')
@section('title', 'Card')

@section('content')
<div class="container cropper mt-4">
    <div class="row">
        <div class="col-md-9" style="background-image: url('{{ $backgroundUrl }}')">

            <div class="img-container mx-auto mb-3" style="overflow: hidden;">
                <img id="image" src="{{ asset('assets/upload/' . $filename) }}" class="d-none" alt="Picture" style="display: block; max-width: 100%;">
            </div>
        </div>
        <div class="col-md-3">
            <div class="card-preview" style="width:250px; background-color:#e4e4e4; border:3px solid green; border-radius:10px; padding:10px;">
                <table class="w-100 text-center">
                    <tr>
                        <td>
                            <img src="{{ asset('assets/img/logo.png') }}" alt="Logo">
                            <span class="d-block fw-bold fs-4" style="color: #000">पहचान पत्र</span>
                        </td>
                    </tr>
                </table>

                <div class="img-container mx-auto mb-3" style="width:220px; height:220px; border: solid 5px #000; overflow: hidden;">
                    <img id="card-image-preview" src="{{ asset('assets/upload/' . $filename) }}" style="width:100%; height: auto;" alt="Card Photo">
                </div>
                <div class="text-center " style="color: #000">
                    <p><b>नाम:</b> {{ $member->name }}</p>
                    <p class="mt-n2"><b>पिता का नाम:</b> {{ $member->father_name }}</p>
                    <p class="mt-n2"><b>मो.</b>{{ $member->mobile1 }}</p>
                    <p class="mt-n2"><b>पता:</b> {{ $address }}, {{ $district }}</p>
                </div>
                <span class="d-block text-center small" style="color: #000">www.bjsmp.in</span>
            </div>
        </div>
    </div>

    <div class="row mt-3">
        <div class="col-md-9 docs-buttons">
            <!-- Cropper controls -->
            <div class="btn-group">
                <button class="btn btn-primary" data-method="rotate" data-option="-45"><i class="fa fa-rotate-left"></i></button>
                <button class="btn btn-primary" data-method="rotate" data-option="45"><i class="fa fa-rotate-right"></i></button>
            </div>
            <div class="btn-group">
                <button class="btn btn-primary" data-method="scaleX" data-option="-1"><i class="fa fa-arrows-h"></i></button>
                <button class="btn btn-primary" data-method="scaleY" data-option="-1"><i class="fa fa-arrows-v"></i></button>
            </div>
            <div class="btn-group">
                <button class="btn btn-primary" data-method="crop"><i class="fa fa-check"></i></button>
                <button class="btn btn-primary" data-method="clear"><i class="fa fa-remove"></i></button>
            </div>
            <button id="crop_button" class="btn btn-success float-end">Crop & Save</button>

            <!-- Modal for preview -->
            <div class="modal fade docs-cropped" id="getCroppedCanvasModal" aria-hidden="true" aria-labelledby="getCroppedCanvasTitle" role="dialog" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                            <h4 class="modal-title" id="getCroppedCanvasTitle">Cropped</h4>
                        </div>
                        <div class="modal-body"></div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                            <a class="btn btn-primary" id="download" href="javascript:void(0);" download="cropped.png">Download</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 text-center">
            <input type="hidden" id="member_id" value="{{ $member->registration_id }}">
            <a class="btn btn-danger mt-2" href="{{ route('admin.card.print', ['id' => $member->registration_id]) }}" target="_blank">
                <i class="fa fa-print"></i> Print Card
            </a>
        </div>
    </div>
</div>
@endsection


@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const image = document.getElementById('image');
        const cardImage = document.getElementById('card-image-preview');

        const cropper = new Cropper(image, {
            aspectRatio: 1,
            viewMode: 1,
            autoCropArea: 1,
            background: true,
            crop(event) {

                const canvas = cropper.getCroppedCanvas({
                    width: 220,
                    height: 150,
                });
                if (canvas) {
                    cardImage.src = canvas.toDataURL();
                }
            }
        });

        document.getElementById('crop_button').addEventListener('click', function() {
            const canvas = cropper.getCroppedCanvas({
                width: 220,
                height: 220
            });
            if (!canvas) return;

            // Set download link and show preview modal
            document.getElementById('download').href = canvas.toDataURL();
            new bootstrap.Modal(document.getElementById('getCroppedCanvasModal')).show();
        });
    });
</script>

@endpush