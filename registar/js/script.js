// js/script.js
$(document).ready(function() {
    // Initialize DataTables
    $('.datatable').DataTable({
        pageLength: 10,
        language: {
            search: "Search:",
            lengthMenu: "Show _MENU_ entries",
            info: "Showing _START_ to _END_ of _TOTAL_ entries",
            paginate: {
                first: "First",
                last: "Last",
                next: "Next",
                previous: "Previous"
            }
        }
    });

    // Delete confirmation
    $('.delete-btn').click(function(e) {
        e.preventDefault();
        var url = $(this).data('url');
        var message = $(this).data('message') || 'Are you sure you want to delete this item?';
        
        if (confirm(message)) {
            window.location.href = url;
        }
    });

    // Auto-compute payment installments
    $('#term_id, #semester_fee').change(function() {
        var term_id = $('#term_id').val();
        var semester_fee = parseFloat($('#semester_fee').val()) || 0;
        
        if (term_id && semester_fee) {
            $.ajax({
                url: 'api/calculate_installments.php',
                method: 'POST',
                data: {
                    term_id: term_id,
                    semester_fee: semester_fee
                },
                success: function(response) {
                    $('#payment_per_installment').val(response);
                }
            });
        }
    });

    // Search student for payments
    $('#search_student').keyup(function() {
        var search = $(this).val();
        if (search.length > 2) {
            $.ajax({
                url: 'api/search_student.php',
                method: 'POST',
                data: {search: search},
                success: function(response) {
                    $('#student_results').html(response).show();
                }
            });
        }
    });

    // Select student from search results
    $(document).on('click', '.student-result', function() {
        var student_id = $(this).data('id');
        var student_name = $(this).data('name');
        
        $('#selected_student_id').val(student_id);
        $('#selected_student_name').val(student_name);
        $('#student_results').hide();
        
        // Load student's unpaid installments
        loadUnpaidInstallments(student_id);
    });

    // Calculate total when checkboxes are selected
    $(document).on('change', '.installment-checkbox', function() {
        var total = 0;
        $('.installment-checkbox:checked').each(function() {
            total += parseFloat($(this).data('amount'));
        });
        
        $('#total_amount').val('₱' + total.toFixed(2));
        $('#total_amount_raw').val(total);
        
        // Calculate change
        calculateChange();
    });

    // Calculate change
    $('#cash_received').keyup(function() {
        calculateChange();
    });

    function calculateChange() {
        var total = parseFloat($('#total_amount_raw').val()) || 0;
        var received = parseFloat($('#cash_received').val()) || 0;
        var change = received - total;
        
        if (change >= 0) {
            $('#change_amount').val('₱' + change.toFixed(2));
            $('#submit_payment').prop('disabled', false);
        } else {
            $('#change_amount').val('Insufficient amount');
            $('#submit_payment').prop('disabled', true);
        }
    }

    // Load unpaid installments
    function loadUnpaidInstallments(student_id) {
        $.ajax({
            url: 'api/get_unpaid_installments.php',
            method: 'POST',
            data: {student_id: student_id},
            success: function(response) {
                $('#installments_list').html(response);
            }
        });
    }

    // Print receipt
    $('#print_receipt').click(function() {
        var receipt_id = $(this).data('id');
        window.open('receipts/print_receipt.php?receipt=' + receipt_id, '_blank');
    });

    // Fee management - update fees
    $('.update-fee-btn').click(function() {
        var strand_id = $(this).data('strand-id');
        var year_level = $(this).data('year-level');
        var new_fee = $('#fee_' + strand_id + '_' + year_level).val();
        
        if (confirm('Are you sure you want to update this fee?')) {
            $.ajax({
                url: 'api/update_fees.php',
                method: 'POST',
                data: {
                    strand_id: strand_id,
                    year_level: year_level,
                    new_fee: new_fee
                },
                success: function(response) {
                    if (response.success) {
                        toastr.success('Fee updated successfully');
                    } else {
                        toastr.error('Error updating fee');
                    }
                }
            });
        }
    });

    // Cash drawer - open drawer
    $('#open_drawer').click(function() {
        var opening_balance = $('#opening_balance').val();
        
        $.ajax({
            url: 'api/open_drawer.php',
            method: 'POST',
            data: {opening_balance: opening_balance},
            success: function(response) {
                if (response.success) {
                    toastr.success('Drawer opened successfully');
                    location.reload();
                } else {
                    toastr.error('Error opening drawer');
                }
            }
        });
    });

    // Cash drawer - close drawer
    $('#close_drawer').click(function() {
        if (confirm('Are you sure you want to close the drawer?')) {
            $.ajax({
                url: 'api/close_drawer.php',
                method: 'POST',
                success: function(response) {
                    if (response.success) {
                        toastr.success('Drawer closed successfully');
                        location.reload();
                    } else {
                        toastr.error('Error closing drawer');
                    }
                }
            });
        }
    });

    // Export to Excel
    $('#export_excel').click(function() {
        var table = $('#monitoring_table').html();
        var blob = new Blob([table], {type: 'application/vnd.ms-excel'});
        var link = document.createElement('a');
        link.href = window.URL.createObjectURL(blob);
        link.download = 'payment_monitoring.xls';
        link.click();
    });
});