<div class="modal fade" id="patientModal" tabindex="-1">
    <div class="modal-dialog">
        <form id="patientForm">
            @csrf
            <input type="hidden" id="id">

            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Patient Form</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">

               
                    <label class="mt-2">Name</label>
                    <input type="text" class="form-control" id="name" name="name">

                    <label class="mt-2">Mobile</label>
                    <input type="text" class="form-control" id="mobile" name="mobile">

                    <label class="mt-2">Email</label>
                    <input type="email" class="form-control" id="email" name="email">

                    <label class="mt-2">Address</label>
                    <textarea class="form-control" id="address" name="address"></textarea>

                </div>

                <div class="modal-footer">
                    <button type="submit" class="btn btn-success">Save</button>
                </div>

            </div>
        </form>
    </div>
</div>
