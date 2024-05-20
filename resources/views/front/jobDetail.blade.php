@extends('front.layouts.app')
@section('main')

<section class="job-image-section">
    <div class="container-fluid p-0">
        <img src="{{ asset('job_images/' . $job->image) }}" alt="{{ $job->title }}" class="img-fluid mx-auto d-block" style="width: 50%;">
    </div>
</section>
<section class="section-4 bg-2">    
    <div class="container pt-5">
        <div class="row">
            <div class="col">
                <nav aria-label="breadcrumb" class="rounded-3 p-3">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('jobs') }}"><i class="fa fa-arrow-left" aria-hidden="true"></i> &nbsp;Back to Jobs</a></li>
                    </ol>
                </nav>
            </div>
        </div> 
    </div>
    <div class="container job_details_area">
        <div class="row pb-2">
            <div class="col-md-8">
                <div class="card shadow border-0">
                    <div class="job_details_header">
                        <div class="single_jobs white-bg d-flex justify-content-between">
                            <div class="jobs_left d-flex align-items-center">
                                <div class="jobs_conetent">
                                    <a href="#">
                                        <h4>{{ $job->title }}</h4>
                                    </a>
                                    <div class="links_locat d-flex align-items-center">
                                        <div class="location">
                                            <p><i class="fa fa-map-marker"></i> {{ $job->location }}</p>
                                        </div>
                                        <div class="location">
                                            <p><i class="fa fa-clock-o"></i> {{ $job->jobType->name }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="jobs_right">
                                <div class="apply_now">
                                    <a class="heart_mark" href="#"><i class="fa fa-heart-o" aria-hidden="true"></i></a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="descript_wrap white-bg">
                        <div class="single_wrap">
                            <h4>Job description</h4>
                            {!! nl2br($job->description) !!}
                        </div>
                        @if ($job->responsibility)
                            <div class="single_wrap">
                                <h4>Responsibility</h4>
                                {!! nl2br($job->responsibility) !!}
                            </div>
                        @endif
                        @if ($job->qualifications)
                            <div class="single_wrap">
                                <h4>Qualifications</h4>
                                {!! nl2br($job->qualifications) !!}
                            </div>
                        @endif
                        @if ($job->benefits)
                            <div class="single_wrap">
                                <h4>Benefits</h4>
                                {!! nl2br($job->benefits) !!}
                            </div>
                        @endif
                        <div class="border-bottom"></div>
                        <div class="pt-3 text-end">
                            @if(Auth::check())
                                <div>
                                   <a href="{{route(config('chatify.routes.prefix'))}}" onclick="saveJob({{ $job->id }});" class="btn btn-primary">Message</a> 
                                   <a href="#" onclick="saveJob({{ $job->id }});" class="btn btn-primary">Save</a>  

                                </div>  
                            @else
                                <a href="{{ route('account.login') }}" class="btn btn-primary">Login to save</a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card shadow border-0">
                    <div class="job_sumary">
                        <div class="summery_header pb-1 pt-4">
                            <h3>Job Summary</h3>
                        </div>
                        <div class="job_content pt-3">
                            <ul>
                                <li>Published on: <span>{{ \Carbon\Carbon::parse($job->created_at)->format('d M, Y') }}</span></li>
                                <li>Vacancy: <span>{{ $job->vacancy }}</span></li>
                                @if ($job->salary)
                                    <li>Salary: <span>{{ $job->salary }}</span></li>
                                @endif
                                <li>Location: <span>{{ $job->location }}</span></li>
                                <li>Job Nature: <span>{{ $job->jobType->name }}</span></li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="card shadow border-0 my-4">
                    <div class="job_sumary">
                        <div class="summery_header pb-1 pt-4">
                            <h3>Company Details</h3>
                        </div>
                        <div class="job_content pt-3">
                            <ul>
                                <li>Name: <span>{{ $job->company_name }}</span></li>
                                @if ($job->company_location)
                                    <li>Location: <span>{{ $job->company_location }}</span></li>
                                @endif
                                @if ($job->company_website)
                                    <li>Website: <span><a href="{{ $job->company_website }}">{{ $job->company_website }}</a></span></li>
                                @endif
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div id="message" class="alert" style="display: none;"></div>
    </div>
</section>

@endsection

@section('customJs')
<script>
    function saveJob(id) {
        $.ajax({
            url: "{{ route('saveJob') }}",
            type: "POST",
            data: {
                id: id,
                _token: "{{ csrf_token() }}" // Include CSRF token
            },
            dataType: "json",
            success: function(response) {
                var messageDiv = $('#message');
                messageDiv.removeClass('alert-success alert-danger');
                if (response.status) {
                    messageDiv.addClass('alert-success');
                } else {
                    messageDiv.addClass('alert-danger');
                }
                messageDiv.text(response.message).show();
            },
            error: function(xhr, status, error) {
                var messageDiv = $('#message');
                messageDiv.removeClass('alert-success').addClass('alert-danger');
                messageDiv.text('An error occurred. Please try again.').show();
            }
        });
    }
</script>
@endsection
@section('customCss')
<style>
    .job-image-section {
        max-height: 300px; /* Adjust the maximum height as needed */
        overflow: hidden; /* Hide any overflowing content */
    }

    .job-image-section img {
        width: 100%;
        height: auto;
        object-fit: cover; /* Maintain image aspect ratio */
    }
</style>
@endsection
