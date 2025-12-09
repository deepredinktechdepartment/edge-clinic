<!doctype html>
<html lang="en">

@include('common_pages.head')

<body class="t_ice-body">
    @include('common_pages.header')
    <main>
        @include('common_pages.nav')
        <div class="ice-body-container p-md-3 mt-2 p-3">
       <!-- @include('common_pages.pagetitle') -->   
        @yield('content')
        </div>
    </main>
    <footer>
        <!-- place footer here -->
    </footer>
    <!-- Bootstrap JavaScript Libraries -->

    
    @include('common_pages.footer_scripts')
    @stack('scripts')


    <script>
    @if(Session::has('message'))
    toastr.options = {
        "closeButton": true,
        "progressBar": true
    }
    toastr.success("{{ session('message') }}");
    @endif

    @if(Session::has('success'))
    toastr.options = {
        "closeButton": true,
        "progressBar": true
    }
    toastr.success("{{ session('success') }}");
    @endif

    @if(Session::has('error'))
    toastr.options = {
        "closeButton": true,
        "progressBar": true
    }
    toastr.error("{{ session('error') }}");
    @endif

    @if(Session::has('info'))
    toastr.options = {
        "closeButton": true,
        "progressBar": true
    }
    toastr.info("{{ session('info') }}");
    @endif

    @if(Session::has('warning'))
    toastr.options = {
        "closeButton": true,
        "progressBar": true
    }
    toastr.warning("{{ session('warning') }}");
    @endif
    </script>
    <script>
        $('#default-datatable').DataTable({

            "lengthMenu": [
                [50, 100, 150, -1],
                [50, 100, 150, "All"]
            ],
            "responsive": false
        });
    </script>
</body>

</html>
