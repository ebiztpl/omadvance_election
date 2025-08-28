@php
    $pageTitle = 'डैशबोर्ड विवरण';
    $breadcrumbs = [
        'मैनेजर' => '#',
        'डैशबोर्ड विवरण' => '#',
    ];
@endphp

@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-12 col-sm-12 col-xs-12">

                <div class="mb-3">
                    <h5>
                        <strong> {{ $type ?? 'सभी' }} फॉलोअप</strong>
                        @if ($status === 'completed')
                            (पूरा किए गए)
                        @elseif($status === 'pending')
                            (लंबित)
                        @elseif($status === 'in_process')
                            (प्रक्रिया में)
                        @elseif($status === 'not_done')
                            (फॉलोअप बाकी)
                        @else
                            (सभी)
                        @endif


                        &nbsp;|&nbsp;
                        <strong>{{ $dateFilter ?? 'सभी' }}</strong>
                    </h5>
                </div>

                <div class="card">
                    <div class="card-body">
                        <span
                            style="margin-bottom: 8px; font-size: 18px; color: green; text-align: right; margin-left: 50px; float: right">कुल
                            फॉलोअप - <span id="complaint-count">{{ $complaints->count() }}</span></span>

                        <div class="table-responsive">
                            <table id="example" style="min-width: 845px" class="display table-bordered">
                                <thead>
                                    <tr>
                                        <th>क्र.</th>
                                        <th>शिकायत विवरण</th>
                                        <th style="width: 150px">नवीनतम जवाब विवरण</th>
                                        <th>फ़ॉलोअप</th>
                                        <th>विस्तार से</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $serial = 1;
                                    @endphp

                                    @foreach ($complaints as $complaint)
                                        <tr>
                                            <td>{{ $serial++ }}</td>

                                            <td>
                                                <strong>शिकायत क्र.: </strong>{{ $complaint->complaint_number ?? 'N/A' }}
                                                <br>
                                                <strong>शिकायत प्रकार: </strong>{{ $complaint->complaint_type ?? '' }}
                                                <br>
                                                <strong>नाम: </strong>{{ $complaint->name ?? 'N/A' }} <br>
                                                <strong>मोबाइल: </strong>{{ $complaint->mobile_number ?? '' }} <br>
                                                <strong>पुत्र श्री: </strong>{{ $complaint->father_name ?? '' }} <br>
                                                <strong>आवेदक: </strong>
                                                @if ($complaint->type == 2)
                                                    {{ $complaint->admin->admin_name ?? '-' }}
                                                @else
                                                    {{ $complaint->registrationDetails->name ?? '-' }}
                                                @endif
                                                <br>
                                                <strong>विभाग: </strong> {{ $complaint->complaint_department ?? 'N/A' }}
                                                <br><br>
                                                <strong>शिकायत तिथि: </strong>
                                                {{ \Carbon\Carbon::parse($complaint->posted_date)->format('d-m-Y h:i A') }}
                                                <br><br>
                                                <strong>स्थिति: </strong>{!! $complaint->statusTextPlain() !!} <br>
                                            </td>
                                            <td>
                                                <strong>भेजने वाला:
                                                </strong>{{ $complaint->latestReplyWithoutFollowup->replyfrom->admin_name ?? 'N/A' }}
                                                <br>
                                                <strong>फॉरवर्ड:
                                                </strong>{{ $complaint->latestReplyWithoutFollowup->forwardedToManager->admin_name ?? 'N/A' }}<br>
                                                <strong>जवाब:
                                                </strong>{{ $complaint->latestReplyWithoutFollowup->complaint_reply ?? '' }}<br><br>
                                                <strong>तिथि:
                                                </strong>{{ $complaint->latestReplyWithoutFollowup->reply_date ?? '' }}
                                                <br>
                                            </td>

                                            <td>
                                                @php
                                                    $latestFollowup = optional($complaint->latestNonDefaultReply)
                                                        ->latestFollowup;
                                                @endphp

                                                @if ($latestFollowup)
                                                    <strong>फ़ॉलोअप तिथि:
                                                    </strong>{{ \Carbon\Carbon::parse($latestFollowup->followup_date)->format('d-m-Y h:i A') }}
                                                    <br>
                                                    <strong>फ़ॉलोअप दिया:
                                                    </strong>{{ $latestFollowup->createdByAdmin->admin_name ?? 'N/A' }}
                                                    <br>
                                                    <strong>संपर्क स्थिति:
                                                    </strong>{{ $latestFollowup->followup_contact_status ?? 'N/A' }} <br>
                                                    <strong>संपर्क विवरण:
                                                    </strong>{{ $latestFollowup->followup_contact_description ?? 'N/A' }}
                                                    <br><br>
                                                    <strong>स्थिति: </strong>{!! $latestFollowup->followup_status_text() !!}
                                                    <br>
                                                @else
                                                    <span class="text-muted">कोई फ़ॉलोअप उपलब्ध नहीं</span>
                                                @endif
                                            </td>

                                            <td>
                                                <a href="{{ route('complaints_show.details', $complaint->complaint_id) }}"
                                                    class="btn btn-sm btn-primary" style="white-space: nowrap;">
                                                    क्लिक करें
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endsection
