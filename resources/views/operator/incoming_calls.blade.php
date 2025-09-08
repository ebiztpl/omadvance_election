@php
    $pageTitle = 'प्राप्त कॉल';
    $breadcrumbs = [
        'कार्यालय' => '#',
        'प्राप्त कॉल' => '#',
    ];
@endphp

@extends('layouts.app')
@section('title', 'Incoming Calls')

@section('content')
    <div class="container">
        <div class="row page-titles mx-0">
            <div class="col-12">
                <form method="GET" id="complaintFilterForm">
                    <div class="row">
                        <div class="col-md-2">
                            <label for="filter" class="form-label">अन्य फ़िल्टर</label>
                            <input type="text" name="filter" id="filter" class="form-control"
                                placeholder="नाम, रेफरेंस, पुत्र श्री, मतदाता पहचान आदि से खोजें"
                                value="{{ request('filter') }}">
                        </div>

                        <div class="col-md-2">
                            <label for="mobile" class="form-label">मोबाइल नंबर</label>
                            <input type="text" name="mobile" id="mobile" class="form-control"
                                placeholder="मोबाइल नंबर दर्ज करें" value="{{ request('mobile') }}">
                        </div>

                        <div class="col-md-2">
                            <label>विभाग</label>
                            <select name="department_id" id="department_id" class="form-control">
                                <option value="">-- सभी --</option>
                                @foreach ($departments as $dept)
                                    <option value="{{ $dept->department_name }}">{{ $dept->department_name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label>मंडल</label>
                            <select name="mandal_id" id="mandal_id" class="form-control">
                                <option value="">-- सभी --</option>
                                @foreach ($mandals as $mandal)
                                    <option value="{{ $mandal->mandal_id }}">{{ $mandal->mandal_name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label>ग्राम/नगर</label>
                            <select name="gram_id" id="gram_id" class="form-control">
                                <option value="">-- सभी --</option>
                                @foreach ($grams as $g)
                                    <option value="{{ $g->nagar_id }}">{{ $g->nagar_name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label>मतदान केंद्र</label>
                            <select name="polling_id" id="polling_id" class="form-control">
                                <option value="">-- सभी --</option>
                                @foreach ($pollings as $p)
                                    <option value="{{ $p->gram_polling_id }}">{{ $p->polling_name }}
                                        ({{ $p->polling_no }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label>क्षेत्र</label>
                            <select name="area_id" id="area_id" class="form-control">
                                <option value="">-- सभी --</option>
                                @foreach ($areas as $a)
                                    <option value="{{ $a->area_id }}">{{ $a->area_name }}</option>
                                @endforeach
                            </select>
                        </div>


                        <div class="col-md-2 mt-2">
                            <br>
                            <button type="button" class="btn btn-primary" id="applyFilters"
                                style="font-size: 12px">फ़िल्टर</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>


        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body" id="complaints-container" style="display: none;">
                        @if (session('success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                {{ session('success') }}
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                        @endif

                        <span
                            style="margin-bottom: 8px; font-size: 18px; color: green; text-align: right; margin-left: 50px; float: right">कुल
                            शिकायत - <span id="complaint-count">0</span></span>

                        <div class="table-responsive">
                            <div id="message-container"></div>
                            <table id="example" style="min-width: 100%;" class="display table-bordered">
                                <thead>
                                    <tr>
                                        <th style="width: 5%;">क्र.</th>
                                        <th style="width: 20%;">शिकायत विवरण</th>
                                        <th style="width: 20%;">नवीनतम जवाब विवरण</th>
                                        <th style="width: 12%;">फ़ॉलोअप विवरण</th>
                                        <th style="width: 10%;">फ़ॉलोअप</th>
                                        <th style="width: 10%; text-align:center;">विस्तार से</th>
                                        <th style="width: 15%;">कारण</th>
                                    </tr>
                                </thead>
                                <tbody>
                                  
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <div class="modal fade" id="contactStatusModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-sm" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">संपर्क स्थिति अपडेट करें</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <form id="contactStatusForm" method="POST">
                    @csrf
                    <input type="hidden" name="complaint_reply_id" id="modal_complaint_reply_id">
                    <input type="hidden" name="complaint_id" id="modal_complaint_id">
                    <div class="modal-body">
                        <label>संपर्क स्थिति:</label>
                        <select class="form-control" name="contact_status">
                            <option value="">--चयन करें--</option>
                            <option value="फोन बंद था">फोन बंद था</option>
                            <option value="सूचना दे दी गई है">सूचना दे दी गई है</option>
                            <option value="फोन व्यस्त था">फोन व्यस्त था</option>
                            <option value="कोई उत्तर नहीं मिला">कोई उत्तर नहीं मिला</option>
                            <option value="बाद में संपर्क करने को कहा">बाद में संपर्क करने को कहा</option>
                            <option value="कॉल काट दी गई">कॉल काट दी गई</option>
                            <option value="संख्या आउट ऑफ कवरेज थी">संख्या आउट ऑफ कवरेज थी</option>
                            <option value="SMS भेजा गया">SMS/Whatsapp भेजा गया</option>
                            <option value="फोन नंबर उपलब्ध नहीं है">फोन नंबर उपलब्ध नहीं है</option>
                        </select>
                        <label class="form-label mt-2">संपर्क विवरण:</label>
                        <textarea name="contact_update" class="form-control" rows="6"></textarea>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-success btn-sm">अपडेट</button>
                    </div>
                </form>
            </div>
        </div>
    </div>


    @push('scripts')
        <script>
            $(document).ready(function() {
                $('#mandal_id').on('change', function() {
                    let mandalId = $(this).val();
                    $('#gram_id').html('<option value="">ग्राम चयन करें</option>');
                    $('#polling_id').html('<option value="">मतदान केंद्र</option>');
                    $('#area_id').html('<option value="">क्षेत्र</option>');

                    if (mandalId) {
                        $.get('/operator/get-nagar/' + mandalId, function(data) {
                            $('#gram_id').append(data);
                        });
                    }
                });

                $('#gram_id').on('change', function() {
                    let gramId = $(this).val();
                    $('#polling_id').html('<option value="">मतदान केंद्र</option>');
                    $('#area_id').html('<option value="">क्षेत्र</option>');

                    if (gramId) {
                        $.get('/operator/get-gram_pollings/' + gramId, function(data) {
                            let html = '<option value="">मतदान केंद्र</option>';
                            data.forEach(function(polling) {
                                html +=
                                    `<option value="${polling.gram_polling_id}">${polling.polling_name} (${polling.polling_no})</option>`;
                            });
                            $('#polling_id').html(html);
                        });
                    }
                });

                // Polling → Area
                $('#polling_id').on('change', function() {
                    let pollingId = $(this).val();
                    $('#area_id').html('<option value="">क्षेत्र</option>');

                    if (pollingId) {
                        $.get('/operator/get-areas/' + pollingId, function(data) {
                            let html = '<option value="">क्षेत्र</option>';
                            data.forEach(function(area) {
                                html +=
                                    `<option value="${area.area_id}">${area.area_name}</option>`;
                            });
                            $('#area_id').html(html);
                        });
                    }
                });

                $('#applyFilters').click(function(e) {
                    e.preventDefault();
                    $.ajax({
                        url: "{{ route('incoming_calls.index') }}",
                        type: "GET",
                        data: $('#complaintFilterForm').serialize(),
                        beforeSend: function() {
                            $('#loader-wrapper').show();
                        },
                        success: function(res) {
                            $('#example tbody').html(res.html);
                            $('#complaint-count').text(res.count);
                               $('#complaints-container').fadeIn();
                        },
                        complete: function() {
                            $('#loader-wrapper').hide();
                        },
                        error: function(err) {
                            console.error(err);
                        }
                    });
                });

            });

            if (performance.navigation.type === 1) {
                $('#complaintFilterForm')[0].reset();

                if (window.location.search) {
                    window.location.href = window.location.origin + window.location.pathname;
                }
            }

            $(document).on('click', '.openModalBtn', function() {
                let complaintId = $(this).data('complaint-id');
                let complaintReplyId = $(this).data('complaint-reply-id');

                $('#modal_complaint_id').val(complaintId);
                $('#modal_complaint_reply_id').val(complaintReplyId);

                $('#contactStatusForm').attr('action', '/operator/update-incoming-contact-status/' + complaintReplyId);

                $('#contactStatusModal').modal('show');
            });



            $(document).on('click', '.followup-radio', function() {
                var complaintId = $(this).closest('tr').find('.openModalBtn').data('complaint-id');
                var complaintReplyId = $(this).closest('tr').find('.openModalBtn').data('complaint-reply-id');

                if (complaintId && complaintReplyId) {
                    $('#modal_complaint_id').val(complaintId);
                    $('#modal_complaint_reply_id').val(complaintReplyId);
                    $('#contactStatusForm').attr('action', '/operator/update-incoming-contact-status/' +
                        complaintReplyId);
                    $('#contactStatusModal').modal('show');
                } else {
                    alert('कृपया पहले फ़ॉलोअप दें');
                    $(this).prop('checked', false);
                }
            });


            function storeIncomingReason(complaintId, complaintReplyId, reason) {
                $.ajax({
                    url: "{{ route('incoming.storeReason') }}",
                    type: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        complaint_id: complaintId,
                        complaint_reply_id: complaintReplyId,
                        reason: reason
                    },
                    success: function(response) {
                        let messageHtml = `
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                 प्राप्त कॉल का कारण सफलतापूर्वक दर्ज किया गया।
                            <button type="button" class="close" data-dismiss="alert">&times;</button>
                            </div>
                        `;
                        $('#message-container').html(messageHtml);

                        window.scrollTo({
                            top: 0,
                            behavior: 'smooth'
                        });
                        setTimeout(() => {
                            $('#message-container .alert').fadeOut('slow', function() {
                                $(this).remove();
                            });
                        }, 3000);
                    },
                    error: function(err) {
                        console.error(err);
                    }
                });
            }
        </script>
    @endpush
@endsection
