@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row page-titles mx-0">
            <div class="col-md-12 col-sm-12 col-xs-12">
                <h4>{{ $title }}</h4>
                <div class="card">
                    <div class="card-body">
                         <div class="table-responsive">
                            <span
                                style="margin-bottom: 8px; font-size: 18px; color: green; text-align: right; margin-left: 50px; float: right">कुल
                                शिकायतें - <span id="complaint-count">{{ $entries->count() }}</span></span>
                        <table id="example" class="display table-bordered">
                            <thead>
                                <tr>
                                        <th>क्र.</th>
                                        <th>नाम</th>
                                        <th>पिता/पति</th>
                                        <th>मकान क्र.</th>
                                        <th>उम्र</th>
                                        <th>लिंग</th>
                                        <th>मतदाता आईडी</th>
                                        <th>मतदान क्षेत्र</th>
                                        <th>जाति</th>
                                        <th>मतदान क्र.</th>
                                        <th>कुल सदस्य</th>
                                        <th>मुखिया मोबाइल</th>
                                        <th>मृत्यु/स्थानांतरित</th>
                                        <th>दिनांक</th>
                                        <th>क्रिया</th>
                                    </tr>
                            </thead>
                            <tbody>
                                  @php $i = 1; @endphp
                                @foreach ($entries as $voter)
                                   <tr>
                                            <td>{{ $i++ }}</td>
                                            <td>{{ $voter->name }}</td>
                                            <td>{{ $voter->father_name }}</td>
                                            <td>{{ $voter->step2->house ?? '' }}</td>
                                            <td>{{ $voter->age }}</td>
                                            <td>{{ $voter->gender }}</td>
                                            <td>{{ $voter->voter_id }}</td>
                                            <td>{{ $voter->step2->area->area_name ?? '-' }}</td>
                                            <td>{{ $voter->jati }}</td>
                                            <td>{{ $voter->step2->matdan_kendra_no ?? '' }}</td>
                                            <td>{{ $voter->step3->total_member ?? '' }}</td>
                                            <td>{{ $voter->step3->mukhiya_mobile ?? '' }}</td>
                                            <td>{{ $voter->{'death/left'} ?? '' }}</td>
                                            <td>{{ \Carbon\Carbon::parse($voter->date_time)->format('d-m-Y') }}</td>
                                            <td style="white-space: nowrap;">
                                                <a href="{{ route('voter.show', $voter->registration_id) }}"
                                                    class="btn btn-sm btn-success mr-1">View</a>
                                                     {{-- <a href="{{ route('voter.update', $voter->registration_id) }}"
                                                    class="btn btn-sm btn-info mr-1">Edit</a> --}}
                                                <form action="{{ route('register.destroy', $voter->registration_id) }}"
                                                    method="POST" style="display: inline-block;"
                                                    onsubmit="return confirm('क्या आप वाकई रिकॉर्ड हटाना चाहते हैं?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                                </form>
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
    </div>
@endsection
