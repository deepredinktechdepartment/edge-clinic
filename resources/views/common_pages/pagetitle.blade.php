<div class="tt-posts">
   	<div class="d-flex justify-content-between tt-wrap bg-white mb-3">
	  	<div class="p-2 bd-highlight"><h5 class="mb-0 pb-0">{{$pageTitle??''}}</h5></div>
	  	<div class="p-2 bd-highlight">
	  		@if(isset($addlink) && !empty($addlink))
	  		<a href="#offcanvasRight" data-bs-toggle="offcanvas" role="button" aria-controls="offcanvasRight" class="addPost"><i class="fa-solid fa-circle-plus"></i></a>
	  		@else
			@endif
	  	</div>
	</div>
</div>