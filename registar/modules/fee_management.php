<?php
// modules/fee_management.php

// Handle fee updates
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_fees'])) {
    $success_count = 0;
    $total_count = 0;
    
    foreach ($_POST['fees'] as $section_id => $year_fees) {
        foreach ($year_fees as $year_level => $semester_fee) {
            $total_count++;
            
            // Get old fee for history
            $old_fee_query = $conn->query("
                SELECT semester_fee FROM fee_structure 
                WHERE section_id = $section_id AND year_level = '$year_level'
            ");
            $old_fee = ($old_fee_query->num_rows > 0) ? $old_fee_query->fetch_assoc()['semester_fee'] : null;
            
            // Check if fee structure exists
            $check = $conn->query("
                SELECT id FROM fee_structure 
                WHERE section_id = $section_id AND year_level = '$year_level'
            ");
            
            if ($check->num_rows > 0) {
                // Update existing
                if ($conn->query("
                    UPDATE fee_structure 
                    SET semester_fee = $semester_fee,
                        updated_at = NOW()
                    WHERE section_id = $section_id AND year_level = '$year_level'
                ")) {
                    $success_count++;
                }
            } else {
                // Insert new
                if ($conn->query("
                    INSERT INTO fee_structure (section_id, year_level, semester_fee) 
                    VALUES ($section_id, '$year_level', $semester_fee)
                ")) {
                    $success_count++;
                }
            }
            
            // Log to history
            $conn->query("
                INSERT INTO fee_history (section_id, year_level, old_fee, new_fee, changed_by) 
                VALUES ($section_id, '$year_level', " . ($old_fee ? $old_fee : 'NULL') . ", $semester_fee, '" . $_SESSION['user_username'] . "')
            ");
        }
    }
    
    if ($success_count == $total_count) {
        echo "<script>
            toastr.success('All tuition fees updated successfully!', 'Success', {
                closeButton: true,
                progressBar: true,
                timeOut: 5000
            });
        </script>";
    } elseif ($success_count > 0) {
        echo "<script>
            toastr.warning('$success_count out of $total_count fees updated successfully. Some updates failed.', 'Partial Success', {
                closeButton: true,
                progressBar: true,
                timeOut: 5000
            });
        </script>";
    } else {
        echo "<script>
            toastr.error('Failed to update fees. Please try again.', 'Error', {
                closeButton: true,
                progressBar: true,
                timeOut: 5000
            });
        </script>";
    }
}

// Handle single fee update via AJAX
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_single'])) {
    $section_id = (int)$_POST['section_id'];
    $year_level = $conn->real_escape_string($_POST['year_level']);
    $semester_fee = (float)$_POST['semester_fee'];
    
    // Get old fee
    $old_fee_query = $conn->query("
        SELECT semester_fee FROM fee_structure 
        WHERE section_id = $section_id AND year_level = '$year_level'
    ");
    $old_fee = ($old_fee_query->num_rows > 0) ? $old_fee_query->fetch_assoc()['semester_fee'] : null;
    
    // Check if fee structure exists
    $check = $conn->query("
        SELECT id FROM fee_structure 
        WHERE section_id = $section_id AND year_level = '$year_level'
    ");
    
    if ($check->num_rows > 0) {
        // Update existing
        $result = $conn->query("
            UPDATE fee_structure 
            SET semester_fee = $semester_fee,
                updated_at = NOW()
            WHERE section_id = $section_id AND year_level = '$year_level'
        ");
    } else {
        // Insert new
        $result = $conn->query("
            INSERT INTO fee_structure (section_id, year_level, semester_fee) 
            VALUES ($section_id, '$year_level', $semester_fee)
        ");
    }
    
    if ($result) {
        // Log to history
        $conn->query("
            INSERT INTO fee_history (section_id, year_level, old_fee, new_fee, changed_by) 
            VALUES ($section_id, '$year_level', " . ($old_fee ? $old_fee : 'NULL') . ", $semester_fee, '" . $_SESSION['user_username'] . "')
        ");
        
        echo json_encode(['success' => true, 'message' => 'Fee updated successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error updating fee']);
    }
    exit;
}

// Handle bulk update via AJAX
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['bulk_update'])) {
    $success_count = 0;
    $total_count = 0;
    
    foreach ($_POST['fees'] as $section_id => $year_fees) {
        foreach ($year_fees as $year_level => $semester_fee) {
            $total_count++;
            
            // Get old fee
            $old_fee_query = $conn->query("
                SELECT semester_fee FROM fee_structure 
                WHERE section_id = $section_id AND year_level = '$year_level'
            ");
            $old_fee = ($old_fee_query->num_rows > 0) ? $old_fee_query->fetch_assoc()['semester_fee'] : null;
            
            // Check if fee structure exists
            $check = $conn->query("
                SELECT id FROM fee_structure 
                WHERE section_id = $section_id AND year_level = '$year_level'
            ");
            
            if ($check->num_rows > 0) {
                // Update existing
                if ($conn->query("
                    UPDATE fee_structure 
                    SET semester_fee = $semester_fee,
                        updated_at = NOW()
                    WHERE section_id = $section_id AND year_level = '$year_level'
                ")) {
                    $success_count++;
                }
            } else {
                // Insert new
                if ($conn->query("
                    INSERT INTO fee_structure (section_id, year_level, semester_fee) 
                    VALUES ($section_id, '$year_level', $semester_fee)
                ")) {
                    $success_count++;
                }
            }
            
            // Log to history
            $conn->query("
                INSERT INTO fee_history (section_id, year_level, old_fee, new_fee, changed_by) 
                VALUES ($section_id, '$year_level', " . ($old_fee ? $old_fee : 'NULL') . ", $semester_fee, '" . $_SESSION['user_username'] . "')
            ");
        }
    }
    
    echo json_encode([
        'success' => $success_count > 0,
        'message' => "$success_count out of $total_count fees updated successfully",
        'success_count' => $success_count,
        'total_count' => $total_count
    ]);
    exit;
}

// Get all sections/strands
$strands = $conn->query("SELECT * FROM sections ORDER BY section_name");

// Year levels
$year_levels = ['Grade 11', 'Grade 12', '1st Year', '2nd Year', '3rd Year', '4th Year'];

// Default fees
$default_fees = [
    'Grade 11' => 7500,
    'Grade 12' => 7500,
    '1st Year' => 7500,
    '2nd Year' => 7500,
    '3rd Year' => 10000,
    '4th Year' => 10000
];
?>

<!-- Loading Toast Template -->
<div class="position-fixed bottom-0 end-0 p-3" style="z-index: 11">
    <div id="liveToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="toast-header bg-primary text-white">
            <i class="fas fa-info-circle me-2"></i>
            <strong class="me-auto">Fee Management</strong>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast"></button>
        </div>
        <div class="toast-body">
            Processing fee updates...
        </div>
    </div>
</div>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2><i class="fas fa-calculator me-2"></i>Tuition Fee Management</h2>
        <p class="text-muted">Manage semester tuition fees for different strands and year levels</p>
    </div>
    <div>
        <button class="btn btn-success me-2" onclick="saveAllFees()" id="saveAllBtn">
            <i class="fas fa-save"></i> Save All Changes
        </button>
        <button class="btn btn-primary" onclick="resetToDefault()" id="resetDefaultBtn">
            <i class="fas fa-undo"></i> Reset to Default
        </button>
    </div>
</div>

<!-- Fee Management Tabs -->
<div class="card shadow-sm">
    <div class="card-header bg-white">
        <ul class="nav nav-tabs card-header-tabs" id="strandTabs" role="tablist">
            <?php 
            $first = true;
            while($strand = $strands->fetch_assoc()): 
            ?>
            <li class="nav-item" role="presentation">
                <button class="nav-link <?php echo $first ? 'active' : ''; ?>" 
                        id="tab-<?php echo $strand['section_id']; ?>" 
                        data-bs-toggle="tab" 
                        data-bs-target="#strand-<?php echo $strand['section_id']; ?>" 
                        type="button">
                    <?php echo $strand['section_name']; ?>
                </button>
            </li>
            <?php 
            $first = false;
            endwhile; 
            $strands->data_seek(0);
            ?>
        </ul>
    </div>
    <div class="card-body">
        <div class="tab-content" id="strandTabContent">
            <?php 
            $first = true;
            while($strand = $strands->fetch_assoc()): 
            ?>
            <div class="tab-pane fade <?php echo $first ? 'show active' : ''; ?>" 
                 id="strand-<?php echo $strand['section_id']; ?>">
                
                <form method="POST" id="feeForm-<?php echo $strand['section_id']; ?>" class="fee-form">
                    <input type="hidden" name="update_fees" value="1">
                    
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Year Level</th>
                                    <th>Semester Fee (₱)</th>
                                    <th>Per Quarter (₱)</th>
                                    <th>Annual Fee (₱)</th>
                                    <th>Last Updated</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($year_levels as $year_level): 
                                    // Get current fee
                                    $fee_query = $conn->query("
                                        SELECT * FROM fee_structure 
                                        WHERE section_id = {$strand['section_id']} 
                                        AND year_level = '$year_level'
                                        ORDER BY updated_at DESC LIMIT 1
                                    ");
                                    
                                    if ($fee_query->num_rows > 0) {
                                        $fee_data = $fee_query->fetch_assoc();
                                        $current_fee = $fee_data['semester_fee'];
                                        $last_updated = date('M d, Y H:i', strtotime($fee_data['updated_at']));
                                    } else {
                                        $current_fee = $default_fees[$year_level] ?? 7500;
                                        $last_updated = 'Not set';
                                    }
                                    
                                    $quarter_fee = $current_fee / 4;
                                    $annual_fee = $current_fee * 2;
                                ?>
                                <tr id="row-<?php echo $strand['section_id']; ?>-<?php echo str_replace(' ', '', $year_level); ?>">
                                    <td><strong><?php echo $year_level; ?></strong></td>
                                    <td>
                                        <div class="input-group">
                                            <span class="input-group-text">₱</span>
                                            <input type="number" 
                                                   name="fees[<?php echo $strand['section_id']; ?>][<?php echo $year_level; ?>]" 
                                                   class="form-control fee-input" 
                                                   value="<?php echo $current_fee; ?>" 
                                                   min="0" 
                                                   step="0.01"
                                                   data-original="<?php echo $current_fee; ?>"
                                                   onchange="updateCalculations(this, '<?php echo $strand['section_id']; ?>', '<?php echo $year_level; ?>')">
                                        </div>
                                    </td>
                                    <td class="quarter-fee-<?php echo $strand['section_id']; ?>-<?php echo str_replace(' ', '', $year_level); ?>">
                                        ₱<?php echo number_format($quarter_fee, 2); ?>
                                    </td>
                                    <td class="annual-fee-<?php echo $strand['section_id']; ?>-<?php echo str_replace(' ', '', $year_level); ?>">
                                        ₱<?php echo number_format($annual_fee, 2); ?>
                                    </td>
                                    <td><small class="text-muted"><?php echo $last_updated; ?></small></td>
                                    <td>
                                        <button type="button" 
                                                class="btn btn-sm btn-primary"
                                                onclick="updateSingleFee(<?php echo $strand['section_id']; ?>, '<?php echo $year_level; ?>')"
                                                id="save-<?php echo $strand['section_id']; ?>-<?php echo str_replace(' ', '', $year_level); ?>">
                                            <i class="fas fa-save"></i> Save
                                        </button>
                                        <button type="button" 
                                                class="btn btn-sm btn-secondary"
                                                onclick="resetRow(<?php echo $strand['section_id']; ?>, '<?php echo $year_level; ?>')"
                                                style="display: none;"
                                                id="reset-<?php echo $strand['section_id']; ?>-<?php echo str_replace(' ', '', $year_level); ?>">
                                            <i class="fas fa-undo"></i> Reset
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <td colspan="6" class="text-end">
                                        <button type="button" 
                                                class="btn btn-success"
                                                onclick="saveStrandFees(<?php echo $strand['section_id']; ?>)">
                                            <i class="fas fa-save"></i> Save All for <?php echo $strand['section_name']; ?>
                                        </button>
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </form>
                
                <!-- Fee Change History -->
                <div class="mt-4">
                    <h6 class="mb-3">
                        <i class="fas fa-history me-2"></i>
                        Recent Changes for <?php echo $strand['section_name']; ?>
                    </h6>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th>Date/Time</th>
                                    <th>Year Level</th>
                                    <th>Old Fee</th>
                                    <th>New Fee</th>
                                    <th>Changed By</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $history = $conn->query("
                                    SELECT * FROM fee_history 
                                    WHERE section_id = {$strand['section_id']}
                                    ORDER BY changed_at DESC 
                                    LIMIT 5
                                ");
                                
                                if ($history->num_rows > 0):
                                    while($change = $history->fetch_assoc()):
                                ?>
                                <tr>
                                    <td><?php echo date('M d, Y H:i', strtotime($change['changed_at'])); ?></td>
                                    <td><?php echo $change['year_level']; ?></td>
                                    <td><?php echo $change['old_fee'] ? '₱' . number_format($change['old_fee'], 2) : 'N/A'; ?></td>
                                    <td class="text-success fw-bold">₱<?php echo number_format($change['new_fee'], 2); ?></td>
                                    <td><?php echo $change['changed_by'] ?? 'System'; ?></td>
                                </tr>
                                <?php 
                                    endwhile;
                                else:
                                ?>
                                <tr>
                                    <td colspan="5" class="text-center text-muted">No history available</td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <?php 
            $first = false;
            endwhile; 
            ?>
        </div>
    </div>
</div>

<!-- Quick Fee Summary Card -->
<div class="row mt-4">
    <div class="col-md-12">
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <i class="fas fa-info-circle me-2"></i>Fee Structure Summary
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <i class="fas fa-lightbulb me-2"></i>
                    <strong>Default Fee Settings:</strong> 
                    1st-2nd Year: ₱7,500 per semester | 3rd-4th Year: ₱10,000 per semester
                </div>
                
                <div class="row">
                    <?php 
                    $strands->data_seek(0);
                    while($strand = $strands->fetch_assoc()): 
                    ?>
                    <div class="col-md-4 mb-3">
                        <div class="card h-100">
                            <div class="card-header bg-primary text-white">
                                <strong><?php echo $strand['section_name']; ?></strong>
                            </div>
                            <div class="card-body">
                                <small class="text-muted">Year Level Fees:</small>
                                <ul class="list-unstyled mt-2">
                                    <?php 
                                    $fees = $conn->query("
                                        SELECT * FROM fee_structure 
                                        WHERE section_id = {$strand['section_id']}
                                        ORDER BY 
                                            CASE year_level
                                                WHEN 'Grade 11' THEN 1
                                                WHEN 'Grade 12' THEN 2
                                                WHEN '1st Year' THEN 3
                                                WHEN '2nd Year' THEN 4
                                                WHEN '3rd Year' THEN 5
                                                WHEN '4th Year' THEN 6
                                            END
                                    ");
                                    
                                    while($fee = $fees->fetch_assoc()):
                                    ?>
                                    <li class="mb-1">
                                        <strong><?php echo $fee['year_level']; ?>:</strong> 
                                        ₱<?php echo number_format($fee['semester_fee'], 2); ?>/sem
                                    </li>
                                    <?php endwhile; ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Success Modal -->
<div class="modal fade" id="successModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">
                    <i class="fas fa-check-circle me-2"></i>Success!
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center py-4">
                <i class="fas fa-check-circle text-success fa-4x mb-3"></i>
                <h4 id="successMessage">Fees updated successfully!</h4>
                <p class="text-muted">Your changes have been saved.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-success" data-bs-dismiss="modal">OK</button>
            </div>
        </div>
    </div>
</div>

<script>
let hasChanges = false;
let changedRows = new Set();

function updateCalculations(input, strandId, yearLevel) {
    var fee = parseFloat(input.value) || 0;
    var quarterFee = (fee / 4).toFixed(2);
    var annualFee = (fee * 2).toFixed(2);
    
    // Update displays
    var quarterCell = document.querySelector('.quarter-fee-' + strandId + '-' + yearLevel.replace(/\s/g, ''));
    var annualCell = document.querySelector('.annual-fee-' + strandId + '-' + yearLevel.replace(/\s/g, ''));
    
    if (quarterCell) quarterCell.innerHTML = '₱' + quarterFee;
    if (annualCell) annualCell.innerHTML = '₱' + annualFee;
    
    // Check if value changed from original
    var original = parseFloat(input.getAttribute('data-original'));
    var rowId = 'row-' + strandId + '-' + yearLevel.replace(/\s/g, '');
    var saveBtn = document.getElementById('save-' + strandId + '-' + yearLevel.replace(/\s/g, ''));
    var resetBtn = document.getElementById('reset-' + strandId + '-' + yearLevel.replace(/\s/g, ''));
    
    if (fee !== original) {
        input.classList.add('border-warning', 'bg-warning', 'bg-opacity-10');
        if (resetBtn) resetBtn.style.display = 'inline-block';
        hasChanges = true;
        changedRows.add(rowId);
    } else {
        input.classList.remove('border-warning', 'bg-warning', 'bg-opacity-10');
        if (resetBtn) resetBtn.style.display = 'none';
        changedRows.delete(rowId);
        hasChanges = changedRows.size > 0;
    }
}

function resetRow(strandId, yearLevel) {
    var input = document.querySelector('input[name="fees[' + strandId + '][' + yearLevel + ']"]');
    var original = parseFloat(input.getAttribute('data-original'));
    input.value = original;
    updateCalculations(input, strandId, yearLevel);
    
    toastr.info('Value reset to original', 'Reset');
}

function updateSingleFee(strandId, yearLevel) {
    var fee = document.querySelector('input[name="fees[' + strandId + '][' + yearLevel + ']"]').value;
    var original = document.querySelector('input[name="fees[' + strandId + '][' + yearLevel + ']"]').getAttribute('data-original');
    
    if (parseFloat(fee) === parseFloat(original)) {
        toastr.info('No changes detected', 'Info');
        return;
    }
    
    if (confirm('Update fee for ' + yearLevel + ' to ₱' + parseFloat(fee).toFixed(2) + '?')) {
        // Show loading toast
        var loadingToast = toastr.info('Updating fee...', 'Please wait', {
            timeOut: 0,
            extendedTimeOut: 0,
            closeButton: false
        });
        
        // Create form data
        var formData = new FormData();
        formData.append('section_id', strandId);
        formData.append('year_level', yearLevel);
        formData.append('semester_fee', fee);
        formData.append('update_single', true);
        
        // Send AJAX request
        fetch('api/update_fees.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            // Clear loading toast
            toastr.clear();
            
            if (data.success) {
                toastr.success('Fee for ' + yearLevel + ' updated successfully!', 'Success', {
                    closeButton: true,
                    progressBar: true,
                    timeOut: 5000
                });
                
                // Update original value
                var input = document.querySelector('input[name="fees[' + strandId + '][' + yearLevel + ']"]');
                input.setAttribute('data-original', fee);
                input.classList.remove('border-warning', 'bg-warning', 'bg-opacity-10');
                
                // Hide reset button
                var resetBtn = document.getElementById('reset-' + strandId + '-' + yearLevel.replace(/\s/g, ''));
                if (resetBtn) resetBtn.style.display = 'none';
                
                // Show success modal
                showSuccessModal('Fee updated successfully!');
            } else {
                toastr.error('Error updating fee: ' + (data.message || 'Unknown error'), 'Error');
            }
        })
        .catch(error => {
            toastr.clear();
            toastr.error('Network error: ' + error, 'Error');
        });
    }
}
function saveStrandFees(strandId) {
    var form = document.getElementById('feeForm-' + strandId);
    var inputs = form.querySelectorAll('.fee-input');
    
    // Check if there are actual changes
    var hasActualChanges = false;
    var changedData = {};
    
    inputs.forEach(function(input) {
        var current = parseFloat(input.value);
        var original = parseFloat(input.getAttribute('data-original'));
        if (current !== original) hasActualChanges = true;
        
        // Get section and year from input name
        var match = input.name.match(/fees\[(\d+)\]\[(.*?)\]/);
        if (match) {
            var sectionId = match[1];
            var yearLevel = match[2];
            if (!changedData[sectionId]) changedData[sectionId] = {};
            changedData[sectionId][yearLevel] = current;
        }
    });
    
    if (!hasActualChanges) {
        toastr.info('No changes detected for this strand', 'Info');
        return;
    }
    
    if (confirm('Save all fee changes for this strand?')) {
        // Disable button
        var btn = event.target;
        var originalText = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Saving...';
        
        // Create form data manually
        var formData = new FormData();
        
        // Add all fees
        Object.keys(changedData).forEach(function(sectionId) {
            Object.keys(changedData[sectionId]).forEach(function(yearLevel) {
                formData.append(`fees[${sectionId}][${yearLevel}]`, changedData[sectionId][yearLevel]);
            });
        });
        
        // Add bulk update flag
        formData.append('bulk_update', 'true');
        
        // Send AJAX request
        fetch('api/update_fees.php', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            console.log('Response status:', response.status);
            return response.text().then(text => {
                console.log('Raw response:', text);
                try {
                    return JSON.parse(text);
                } catch (e) {
                    console.error('JSON parse error:', e);
                    throw new Error('Invalid JSON response: ' + text.substring(0, 100));
                }
            });
        })
        .then(data => {
            // Restore button
            btn.disabled = false;
            btn.innerHTML = originalText;
            
            if (data.success) {
                toastr.success(data.message || 'Fees updated successfully!', 'Success');
                
                // Update original values
                inputs.forEach(function(input) {
                    input.setAttribute('data-original', input.value);
                    input.classList.remove('border-warning', 'bg-warning', 'bg-opacity-10');
                    
                    var rowId = input.name.match(/fees\[(\d+)\]\[(.*?)\]/);
                    if (rowId) {
                        var resetBtn = document.getElementById('reset-' + rowId[1] + '-' + rowId[2].replace(/\s/g, ''));
                        if (resetBtn) resetBtn.style.display = 'none';
                    }
                });
                
                changedRows.clear();
                hasChanges = false;
                
                showSuccessModal('Strand fees updated successfully!');
                setTimeout(() => location.reload(), 1500);
            } else {
                toastr.error(data.message || 'Error updating fees', 'Error');
            }
        })
        .catch(error => {
            // Restore button
            btn.disabled = false;
            btn.innerHTML = originalText;
            
            toastr.error('Network error: ' + error.message, 'Error');
            console.error('Fetch error:', error);
        });
    }
}
function saveAllFees() {
    if (!hasChanges) {
        toastr.info('No changes detected', 'Info');
        return;
    }
    
    if (confirm('Save all fee changes for all strands? This will update ' + changedRows.size + ' changed items.')) {
        // Disable button
        var btn = document.getElementById('saveAllBtn');
        var originalText = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Saving...';
        
        // Collect all fees
        var formData = new FormData();
        var allFees = {};
        
        document.querySelectorAll('.fee-input').forEach(function(input) {
            var match = input.name.match(/fees\[(\d+)\]\[(.*?)\]/);
            if (match) {
                var sectionId = match[1];
                var yearLevel = match[2];
                var value = input.value;
                
                if (!allFees[sectionId]) allFees[sectionId] = {};
                allFees[sectionId][yearLevel] = value;
                
                formData.append(input.name, value);
            }
        });
        
        formData.append('bulk_update', 'true');
        
        // Debug log
        console.log('Submitting fees:', allFees);
        
        fetch('api/update_fees.php', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            console.log('Response status:', response.status);
            return response.text().then(text => {
                console.log('Raw response:', text);
                try {
                    return JSON.parse(text);
                } catch (e) {
                    console.error('JSON parse error:', e);
                    throw new Error('Invalid JSON response');
                }
            });
        })
        .then(data => {
            // Restore button
            btn.disabled = false;
            btn.innerHTML = originalText;
            
            if (data.success) {
                toastr.success(data.message, 'Success');
                
                // Update all original values
                document.querySelectorAll('.fee-input').forEach(function(input) {
                    input.setAttribute('data-original', input.value);
                    input.classList.remove('border-warning', 'bg-warning', 'bg-opacity-10');
                    
                    var rowId = input.name.match(/fees\[(\d+)\]\[(.*?)\]/);
                    if (rowId) {
                        var resetBtn = document.getElementById('reset-' + rowId[1] + '-' + rowId[2].replace(/\s/g, ''));
                        if (resetBtn) resetBtn.style.display = 'none';
                    }
                });
                
                changedRows.clear();
                hasChanges = false;
                
                showSuccessModal('All fees updated successfully!');
                setTimeout(() => location.reload(), 2000);
            } else {
                toastr.error(data.message || 'Error updating fees', 'Error');
            }
        })
        .catch(error => {
            // Restore button
            btn.disabled = false;
            btn.innerHTML = originalText;
            
            toastr.error('Network error: ' + error.message, 'Error');
            console.error('Fetch error:', error);
        });
    }
}

function resetToDefault() {
    if (confirm('Reset all fees to default values? This will overwrite any changes.')) {
        var defaultFees = <?php echo json_encode($default_fees); ?>;
        
        document.querySelectorAll('.fee-input').forEach(function(input) {
            var name = input.name;
            var match = name.match(/fees\[\d+\]\[(.*?)\]/);
            if (match && match[1] in defaultFees) {
                input.value = defaultFees[match[1]];
                
                // Trigger calculation
                var event = new Event('change', { bubbles: true });
                input.dispatchEvent(event);
            }
        });
        
        toastr.success('Reset to default values', 'Reset Complete');
    }
}

function showSuccessModal(message) {
    document.getElementById('successMessage').textContent = message || 'Fees updated successfully!';
    var modal = new bootstrap.Modal(document.getElementById('successModal'));
    modal.show();
}

// Warn user before leaving if there are unsaved changes
window.addEventListener('beforeunload', function(e) {
    if (hasChanges) {
        e.preventDefault();
        e.returnValue = 'You have unsaved changes. Are you sure you want to leave?';
        return e.returnValue;
    }
});

// Check for changes before closing tab
window.addEventListener('unload', function() {
    if (hasChanges) {
        // Optional: Log unsaved changes
        console.log('User left with unsaved changes');
    }
});
</script>