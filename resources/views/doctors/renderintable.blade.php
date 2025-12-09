<div class="row" id="data-container">
    @if(isset($doctors_videos_data) && $doctors_videos_data->count()>0)
        @foreach ($doctors_videos_data as $doctorvideo) 
        <div class="col-md-3 mb-4">
           <div class="card">
            <!-- <img src="{{URL::to('public/uploads/testimonials/'.$doctorvideo->cover_image??'')}}" class="img-fluid"> -->
            <iframe width="100%" height="150" src="{{$doctorvideo->youtube_url??''}}" ></iframe>
            <div class="card-footer float-right">
                <div style="float:right;">
                    <a href="#offcanvasRight" data-id="{{$doctorvideo->id??''}}" data-bs-toggle="offcanvas" role="button" aria-controls="offcanvasRight" class="editPost"><i class="fa-solid fa-pen-to-square"></i></a>&nbsp;&nbsp;<a href="{{ route ('admin.doctor-videos.delete',["ID"=>Crypt::encryptString($doctorvideo->id)] ) }}" title="Delete" onclick="return confirm('Are you sure to delete this?')"><i class="fa-solid fa-trash-can"></i></a>
                </div>
                
            </div>
        </div> 
        </div>
        @endforeach
        @else
        <div class="col-md-4 mb-4">
           <p>No Results Found</p> 
        </div>
        @endif
    </div> 