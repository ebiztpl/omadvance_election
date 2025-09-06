@php
    if (in_array($complaint->complaint_type, ['शुभ सुचना', 'अशुभ सुचना'])) {
        $pageTitle = 'सूचनाएँ देखे';
        $breadcrumbs = [
            'कार्यालय' => '#',
            'सूचनाएँ देखे' => '#',
        ];
    } else {
        $pageTitle = 'समस्याएँ देखें';
        $breadcrumbs = [
            'कार्यालय' => '#',
            'समस्याएँ देखें' => '#',
        ];
    }
@endphp

@php
    $nameLabel =
        $complaint->complaint_type === 'विकास'
            ? 'मांगकर्ता का नाम'
            : (in_array($complaint->complaint_type, ['शुभ सुचना', 'अशुभ सुचना'])
                ? 'सूचनाकर्ता नाम'
                : 'शिकायतकर्ता नाम');

    $mobileLabel =
        $complaint->complaint_type === 'विकास'
            ? 'मांगकर्ता मोबाइल'
            : (in_array($complaint->complaint_type, ['शुभ सुचना', 'अशुभ सुचना'])
                ? 'सूचनाकर्ता मोबाइल'
                : 'शिकायतकर्ता मोबाइल');
@endphp

@extends('layouts.app')
@section('title', 'Complaint Details')

@section('content')
    <div class="container">

        {{-- Complaint Basic Info --}}
        {{-- Complaint Basic Info --}}
        <div class="card mb-3 shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center text-white"
                style="background-color: rgb(230, 225, 225) ">
                {{-- Complaint Number on the left --}}
                <h5 class="mb-0">{{ $complaint->complaint_number }} - ({{ $complaint->complaint_type }})</h5>

                {{-- Centered Counts --}}
                <div class="d-flex gap-2" style="font-weight: bold; font-size: 18px">
                    <span class="badge text-dark p-2 mr-2" style="background-color: #bbe3fc">
                        कुल जवाब: <strong>{{ $totalReplies }}</strong>
                    </span>
                    <span class="badge text-dark p-2" style="background-color: #b2ee64">
                        कुल फ़ॉलोअप: <strong>{{ $totalFollowups }}</strong>
                    </span>
                </div>

                {{-- Status on the right --}}
                <span><strong class="text-dark">स्थिति:</strong> {!! $complaint->statusText() !!}</span>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    @php
                        $fields = [
                            $nameLabel => $complaint->name,
                            $mobileLabel => $complaint->mobile_number,
                            'पिता का नाम' => $complaint->father_name,
                            'मतदाता पहचान' => $complaint->voter_id,
                            'संभाग का नाम' => $complaint->division->division_name ?? null,
                            'जिले का नाम' => $complaint->district->district_name ?? null,
                            'विधानसभा का नाम' => $complaint->vidhansabha->vidhansabha ?? null,
                            'नगर/मंडल' =>
                                ($complaint->gram->nagar_name ?? null) .
                                ($complaint->mandal->mandal_name ? ' - ' . $complaint->mandal->mandal_name : ''),
                            'मतदान केंद्र/ग्राम/वार्ड' =>
                                ($complaint->polling->polling_name ?? null) .
                                ($complaint->polling->polling_no ? ' (' . $complaint->polling->polling_no . ')' : '') .
                                ($complaint->area->area_name ? ' - ' . $complaint->area->area_name : ''),
                            'रेफरेंस नाम' => $complaint->reference_name,
                            'लिंग' => $complaint->registration->gender ?? null,
                            'धर्म' => $complaint->registration->religion ?? null,
                            'वर्ग/श्रेणी' => $complaint->registration->caste ?? null,
                            'जाति' => $complaint->registration->jati ?? null,
                            'शिक्षा' => $complaint->registration->education ?? null,
                            'व्यवसाय' => $complaint->registration->business ?? null,
                            'पद' => $complaint->registration->position ?? null,
                            'दिनांक' => $complaint->posted_date ?? null,
                        ];
                    @endphp

                    @foreach ($fields as $label => $value)
                        @if (!empty($value))
                            @php
                                $highlighted = [
                                    'विभाग',
                                    'नगर/मंडल',
                                    'मतदान केंद्र/ग्राम/वार्ड',
                                    'जाति',
                                    'मतदाता पहचान',
                                ];
                                $isHighlighted = in_array($label, $highlighted);
                            @endphp

                            <div class="col-md-2 mb-2">
                                <div class="border p-2 rounded {{ $isHighlighted ? 'fw-bold' : '' }}"
                                    style="{{ $isHighlighted ? 'background-color: #d8f5d0; color: #0c0c0c;' : '' }}">
                                    <strong>{{ $label }}:</strong>
                                    <div>{{ $value }}</div>
                                </div>
                            </div>
                        @endif
                    @endforeach

                    {{-- @foreach ($fields as $label => $value)
                        @if (!empty($value))
                            <div class="col-md-2 mb-2">
                                <div class="border p-2 rounded">
                                    <strong>{{ $label }}:</strong>
                                    <div>{{ $value }}</div>
                                </div>
                            </div>
                        @endif
                    @endforeach --}}

                    @if (!empty($complaint->address))
                        <div class="col-md-3 mb-2">
                            <div class="border p-2 rounded">
                                <strong>पूरा पता:</strong>
                                <div>{{ $complaint->address }}</div>
                            </div>
                        </div>
                    @endif

                    @if (!empty($complaint->issue_title))
                        <div class="col-md-3 mb-2">
                            <div class="border p-2 rounded">
                                <strong>विषय:</strong>
                                <div>{{ $complaint->issue_title }}</div>
                            </div>
                        </div>
                    @endif

                    @if (!empty($complaint->issue_description))
                        <div class="col-md-4 mb-2">
                            <div class="border p-2 rounded">
                                <strong>विवरण:</strong>
                                <div>{{ $complaint->issue_description }}</div>
                            </div>
                        </div>
                    @endif

                    @if (!empty($complaint->issue_attachment))
                        <div class="col-md-4 mb-2">
                            <div class="border p-2 rounded">
                                <strong>फ़ाइल अटैचमेंट:</strong>
                                <div class="mt-1">
                                    <a href="{{ asset('assets/upload/complaints/' . $complaint->issue_attachment) }}"
                                        class="btn btn-sm btn-light border" target="_blank">अटैचमेंट खोलें</a>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>


        {{-- Replies and Follow-ups --}}
        <div class="row g-3">
            @forelse ($complaint->replies as $reply)
                <div class="col-md-3">
                    <div class="card  shadow-sm">
                        <div class="card-header d-flex justify-content-end"
                            style="background: linear-gradient(90deg, #667eea, #764ba2); color: white;">
                            <span class="badge bg-warning text-dark">{!! $reply->statusTextPlain() !!}</span>
                        </div>

                        <div class="card-body">
                            @if (!empty($reply->reply_date))
                                <strong>तिथि:</strong>
                                {{ \Carbon\Carbon::parse($reply->reply_date)->format('d-m-Y h:i A') }}<br><br>
                            @endif

                            @if (!empty($reply->predefinedReply->reply))
                                <p><strong>निर्धारित उत्तर:</strong> {{ $reply->predefinedReply->reply }}</p>
                            @endif

                            @if (!empty($reply->complaint_reply))
                                <p><strong>जवाब:</strong> {{ $reply->complaint_reply }}</p>
                            @endif

                            @if (!empty($reply->replyfrom?->admin_name))
                                <p><strong>भेजने वाला:</strong> {{ $reply->replyfrom?->admin_name ?? '-' }}</p>
                            @endif

                            @if (!empty($reply->forwardedToManager?->admin_name))
                                <p><strong>फॉरवर्ड:</strong> {{ $reply->forwardedToManager?->admin_name ?? '-' }}
                                </p>
                            @endif

                            @if (!in_array($complaint->complaint_type, ['शुभ सुचना', 'अशुभ सुचना']))
                                @if (!empty($reply->review_date))
                                    <p><strong>रीव्यू तिथि:</strong> {{ $reply->review_date ?? '-' }}</p>
                                @endif
                                @if (!empty($reply->importance))
                                    <p><strong>महत्त्वपूर्ण:</strong> {{ $reply->importance ?? '-' }}</p>
                                @endif
                            @endif

                            {{-- Follow-ups --}}
                            <div class="mt-3 p-3 border rounded bg-light" style="max-height: 300px; overflow-y: auto;">
                                <h6 class="mb-2">फ़ॉलोअप विवरण</h6>
                                @if ($reply->followups->count() > 0)
                                    @foreach ($reply->followups as $followup)
                                        <div class="border p-2 mb-2 rounded bg-white shadow-sm">
                                            @if (!empty($followup->followup_date))
                                                <p><strong>फ़ॉलोअप तिथि:</strong>
                                                    {{ \Carbon\Carbon::parse($followup->followup_date)->format('d-m-Y h:i A') }}
                                                </p>
                                            @endif

                                            @if (!empty($followup->followup_contact_status))
                                                <p><strong>संपर्क स्थिति:</strong>
                                                    {{ $followup->followup_contact_status ?? '-' }}</p>
                                            @endif

                                            @if (!empty($followup->followup_contact_description))
                                                <p><strong>संपर्क विवरण:</strong>
                                                    {{ $followup->followup_contact_description ?? '-' }}</p>
                                            @endif

                                            @if (!empty($followup->createdByAdmin?->admin_name))
                                                <p><strong>फ़ॉलोअप दिया:</strong>
                                                    {{ $followup->createdByAdmin?->admin_name ?? '-' }}</p>
                                            @endif

                                            @if (!empty($followup->followup_status_text()))
                                                <p>{!! $followup->followup_status_text() ?? '-' !!}</p>
                                            @endif
                                        </div>
                                    @endforeach
                                @else
                                    <p class="text-muted">कोई फ़ॉलोअप उपलब्ध नहीं</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-md-12">
                    <div class="alert alert-warning text-center">कोई जवाब उपलब्ध नहीं है।</div>
                </div>
            @endforelse
        </div>
    </div>
@endsection
