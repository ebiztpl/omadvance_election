@php
    $pageTitle = 'फॉलोअप विवरण';
    $breadcrumbs = [
        'मैनेजर' => '#',
        'फॉलोअप विवरण' => '#',
    ];
@endphp

@extends('layouts.app')

@section('title', 'All Followup Details')

@section('content')
    <div class="container">
        <div class="row page-titles mx-0">
            <div class="col-md-12 col-sm-12 col-xs-12">
                <form method="GET" action="{{ route('allfollowups.index') }}" class="row g-3 mb-4">
                    <div class="col-md-2">
                        <select class="form-control" name="status">
                            <option value="">सभी स्थिति</option>
                            <option value="1" {{ request('status') == '1' ? 'selected' : '' }}>अपूर्ण</option>
                            <option value="2" {{ request('status') == '2' ? 'selected' : '' }}>पूर्ण</option>
                        </select>
                    </div>

                    <div class="col-md-2"><input type="date" class="form-control" name="date_from"
                            value="{{ request('date_from') }}"></div>

                    <div class="col-md-2"><input type="date" class="form-control" name="date_to"
                            value="{{ request('date_to') }}"></div>

                    <div class="col-md-2"><input type="text" class="form-control" name="search"
                            placeholder="शिकायत नं./नाम/मोबाइल" value="{{ request('search') }}"></div>

                    <div class="col-md-2">
                        <select class="form-control" name="followup_by">
                            <option value="">सभी ऑपरेटर</option>
                            @foreach ($operators as $operator)
                                <option value="{{ $operator->admin_id }}"
                                    {{ request('followup_by') == $operator->admin_id ? 'selected' : '' }}>
                                    {{ $operator->admin_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary">फ़िल्टर</button>
                        <a href="{{ route('allfollowups.index') }}" class="btn btn-secondary">रीसेट</a>
                    </div>
                </form>
            </div>
        </div>


        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                             <span
                                style="margin-bottom: 8px; font-size: 18px; color: green; text-align: right; margin-left: 50px; float: right">कुल
                                फॉलोअप - <span id="complaint-count">{{ $complaints->count() }}</span></span>
                            <table style="width: 100%; table-layout: fixed;" id="example"
                                class="table display table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th style="width: 50px">क्र.</th>
                                        <th style="width: 250px">शिकायत विवरण</th>
                                        <th style="width: 150px">क्षेत्र</th>
                                        <th style="width: 500px">रिप्लाई/फॉलोअप विवरण</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $serial = 1;
                                    @endphp
                                    @forelse($complaints as $complaint)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>

                                            <td>
                                                <strong>शिकायत क्र.: </strong>{{ $complaint->complaint_number ?? '-' }}<br>
                                                <strong>शिकायत प्रकार: </strong>{{ $complaint->complaint_type ?? '' }}
                                                <br>
                                                <strong>नाम: </strong>{{ $complaint->name ?? '-' }}<br>
                                                <strong>मोबाइल: </strong>{{ $complaint->mobile_number ?? '-' }}<br>
                                                <strong>पुत्र श्री: </strong>{{ $complaint->father_name ?? '' }} <br>
                                                <strong>आवेदक: </strong>
                                                @if (optional($complaint)->type == 2)
                                                    {{ optional($complaint->admin)->admin_name ?? '-' }}
                                                @else
                                                    {{ optional($complaint->registrationDetails)->name ?? '-' }}
                                                @endif
                                                <br>
                                                <strong>विभाग: </strong> {{ $complaint->complaint_department ?? 'N/A' }}
                                                <br><br>
                                                <strong>शिकायत तिथि: </strong>
                                                {{ optional($complaint->posted_date) ? \Carbon\Carbon::parse($complaint->posted_date)->format('d-m-Y h:i A') : '-' }}<br><br>
                                                <strong>स्थिति: </strong>{!! optional($complaint)->statusTextPlain() ?? '-' !!} <br><br>
                                                <strong>विवरण: </strong><a
                                                    href="{{ route('complaints_show.details', $complaint->complaint_id) }}"
                                                    class="btn btn-sm btn-primary">क्लिक करें</a>

                                            </td>

                                            <td>
                                                {{ optional($complaint->division)->division_name ?? '-' }}<br>
                                                {{ optional($complaint->district)->district_name ?? '-' }}<br>
                                                {{ optional($complaint->vidhansabha)->vidhansabha ?? '-' }}<br>
                                                {{ optional($complaint->mandal)->mandal_name ?? '-' }}<br>
                                                {{ optional($complaint->gram)->nagar_name ?? '-' }}<br>
                                                {{ optional($complaint->polling)->polling_name ?? '-' }}
                                                ({{ optional($complaint->polling)->polling_no ?? '-' }})<br>
                                                {{ optional($complaint->area)->area_name ?? '-' }}
                                            </td>

                                            <td>
                                                <div style="display: flex; flex-wrap: nowrap; gap: 10px; overflow-x: auto;">
                                                    @forelse($complaint->replies as $reply)
                                                        <div
                                                            style="border:1px solid #ccc; border-radius:8px; padding:10px; background:#f9f9f9; flex: 0 0 auto; box-sizing: border-box;">
                                                            <strong>
                                                                <span
                                                                    style="background-color: #616361; padding: 4px 6px; border-radius: 3px; display: inline-block; margin-bottom: 8px; color: white">
                                                                    रिप्लाई: {{ $loop->iteration }}
                                                                </span>
                                                            </strong><br>
                                                            <strong>भेजने वाला:
                                                            </strong>{{ $reply->replyfrom->admin_name ?? 'N/A' }}
                                                            <br>
                                                            <strong>फॉरवर्ड:
                                                            </strong>{{ $reply->forwardedToManager->admin_name ?? 'N/A' }}<br>
                                                            <strong>जवाब:
                                                            </strong>{{ $reply->complaint_reply ?? '' }}<br><br>
                                                            <strong>तिथि:
                                                            </strong>{{ $reply->reply_date ?? '' }}
                                                            <br>


                                                            <div
                                                                style="display: flex; flex-wrap: nowrap; gap: 8px; margin-top:5px;">
                                                                @forelse($reply->allFollowups as $key => $f)
                                                                    <div
                                                                        style="border:1px solid #bbb; border-radius:5px; padding:8px; background:#fff; flex: 0 0 auto; box-sizing: border-box;">
                                                                        <strong>
                                                                            <span
                                                                                style="background-color: #343534; padding: 4px 6px; border-radius: 3px; display: inline-block; margin-bottom: 8px; color: white">
                                                                                {{ ['पहला', 'दूसरा', 'तीसरा', 'चौथा', 'पांचवा'][$key] ?? $key + 1 . 'वां' }}
                                                                                फॉलोअप:
                                                                            </span>
                                                                        </strong><br><br>
                                                                        <strong>फ़ॉलोअप तिथि:
                                                                        </strong>{{ \Carbon\Carbon::parse($f->followup_date)->format('d-m-Y h:i A') }}
                                                                        <br><br>

                                                                        <strong>फ़ॉलोअप दिया:
                                                                        </strong>{{ $f->createdByAdmin->admin_name ?? 'N/A' }}
                                                                        <br>

                                                                        <strong>संपर्क स्थिति:
                                                                        </strong>{{ $f->followup_contact_status ?? 'N/A' }}
                                                                        <br>
                                                                        <strong>संपर्क विवरण:
                                                                        </strong>{{ $f->followup_contact_description ?? 'N/A' }}
                                                                        <br><br>



                                                                        {!! $f->followup_status_text() !!}
                                                                    </div>
                                                                @empty
                                                                    <em>कोई फॉलोअप नहीं</em>
                                                                @endforelse
                                                            </div>
                                                        </div>
                                                    @empty
                                                        <em>कोई रिप्लाई उपलब्ध नहीं</em>
                                                    @endforelse
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center text-danger">कोई फॉलोअप डेटा उपलब्ध नहीं
                                                है</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
