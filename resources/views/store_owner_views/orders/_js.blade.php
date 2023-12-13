<script>
    $(document).ready(function() {
        $('.datatable').DataTable({
            ajax: {
                url: "{{route('get_vendor_orders')}}",
                data: {
                    _token: "{{csrf_token()}}",
                    vendor_id: 2
                },
            }
        });
    });
</script>