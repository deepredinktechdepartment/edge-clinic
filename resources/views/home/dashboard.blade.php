@extends('template_v1')

@section('content')

	<div class="tt-posts">
   	<div class="d-flex justify-content-between tt-wrap bg-white mb-3">
	  	<div class="p-2 bd-highlight"><h5 class="mb-0 pb-0">{{$pageTitle??''}}</h5></div>
	  	<div class="p-2 bd-highlight">
	  		@if(isset($addlink) && !empty($addlink))
	  		<a href="#offcanvasRight" data-bs-toggle="offcanvas" role="button" aria-controls="offcanvasRight"><i class="fa-solid fa-circle-plus"></i></a>
	  		@else
			@endif
	  	</div>
	</div>
</div>
<div class="t-job-sheet container-fluid g-0">
	<div id="root">
  <div class=" pt-3">
    <div class="row align-items-stretch">

      <x-dashboard-card 
        title="Specializations" 
        :count="$departments_count??0" 
        route="{{ route('admin.specializations') }}" 
    />

    <x-dashboard-card 
        title="Doctors" 
        :count="$doctors_count??0" 
        route="{{ route('admin.doctors') }}" 
    />



  
    </div>
  </div>
</div>
</div>
@endsection