<div class="nav-body-header bg-white pt-4 pb-3 px-2">
            <div class="nav-body-container container-fluid">
                <div class="row flex-row g-0">
                    <div class="col-auto">
                        <a href="#" class="btn-burger burger-open hide me-3"><img class="img-fluid" width="18"
                                src="{{URL::to('assets/img/burger-menu.svg')}}" alt=""></a>
                    </div>
                    <div class="col-md-3 col-6">

                    </div>
                    <div class="col-auto ms-auto">
                        <div class="navbar-item navbar-user dropdown">
<a href="#" class="navbar-link dropdown-toggle d-flex align-items-center" data-bs-toggle="dropdown" aria-expanded="true">
@if(isset(auth()->user()->profile_picture) && !empty(auth()->user()->profile_picture))
    <img src="{{URL::to('public/uploads/users/'.auth()->user()->profile_picture)}}" alt="">
    @else
    <img src="https://placekitten.com/320/320" alt="">
    @endif

<span>
<span class="d-none d-md-inline" style="font-size:14px;color: #E870A8;"><b>{{auth()->user()->name??''}}</b></span>
<b class="caret"></b>
</span>
</a>
<div class="dropdown-menu dropdown-menu-end me-1" style="position: absolute; inset: 0px 0px auto auto; margin: 0px;  border: none;
    border-radius: 0; width:150px; " data-popper-placement="bottom-end">
<a href="{{route('admin.profile')}}" class="dropdown-item"><i class="fa-solid fa-user-pen"></i>&nbsp;&nbsp;Edit Profile</a>

<a title="click here to change your password" alt="click here to change your password" href="#offcanvasRight-chgpwd" data-id="" data-bs-toggle="offcanvas" role="button" aria-controls="offcanvasRight-chgpwd" class="editPassword dropdown-item"><i class="fa-solid fa-key"></i>&nbsp;&nbsp;Change Password</a>

{{-- <a href="{{URL::to('admin/changepassword')}}" class="dropdown-item" ><i class="fa-solid fa-pen-to-square"></i>&nbsp;&nbsp;Change Password</a>
<a href="#" class="dropdown-item"><i class="fa-solid fa-gear"></i>&nbsp;&nbsp;Settings</a> --}}
<div class="dropdown-divider"></div>
<a href="{{route('admin.logout')}}" class="dropdown-item"><i class="fa-solid fa-right-from-bracket"></i>&nbsp;&nbsp;Log Out</a>
</div>
</div>



                    </div>
                </div>
            </div>
        </div>