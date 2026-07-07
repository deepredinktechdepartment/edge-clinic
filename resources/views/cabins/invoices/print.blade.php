@include('cabins.invoices.receipt', ['invoice' => $invoice])
<script>
window.onload = function () {
    window.print();
    window.onafterprint = function () {
        window.close();
    };
};
</script>
