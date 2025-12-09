<div class="t-table table-responsive">
    <table
        class="table table-borderless table-hover table-centered align-middle table-nowrap mb-0">
        <thead>
            <tr>
                <td scope="col">S.No.</td>
                <td scope="col">Department</td>
                <td scope="col">Question?</td>
                {{-- <td scope="col">Answer</td> --}}
                <td scope="col">Status</td>
                <td scope="col">Action</td>
            </tr>
        </thead>
        <tbody>
            @foreach($department_faqs as $department)
            <tr>
                <td>{{$loop->iteration??''}}</td>
                <td>{{Str::title($department->dept_name??'')}}</td>
                <td>{{Str::title($department->faq_question??'')}}</td>
                {{-- <td>{!!   Str::limit($department->faq_answer??'', 50) !!}</td> --}}
                <td>
                        @if($department->is_active==1)
                        <span class="badge bg-success">Published</span>
                        @else
                        <span class="badge bg-danger">Draft</span>
                        @endif
                </td>
                <td><a href="#offcanvasRight" data-id="{{$department->id??''}}" data-bs-toggle="offcanvas" role="button" aria-controls="offcanvasRight" class="editFaqPost"><i class="fa-solid fa-pen-to-square"></i></a>&nbsp;&nbsp;<a href="{{ route ('admin.faq.delete',["ID"=>Crypt::encryptString($department->id)] ) }}" title="Delete" onclick="return confirm('Are you sure to delete this?')"><i class="fa-solid fa-trash-can"></i></a></td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>