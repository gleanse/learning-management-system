<?php
// modules/students.php

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_student'])) {
        $student_number = generateStudentNumber($conn);
        $first_name = $conn->real_escape_string($_POST['first_name']);
        $middle_name = $conn->real_escape_string($_POST['middle_name']);
        $last_name = $conn->real_escape_string($_POST['last_name']);
        $lrn = $conn->real_escape_string($_POST['lrn']);
        $section_id = (int)$_POST['section_id'];
        $year_level = $conn->real_escape_string($_POST['year_level']);
        $education_level = $conn->real_escape_string($_POST['education_level']);
        $contact = $conn->real_escape_string($_POST['contact']);
        $guardian = $conn->real_escape_string($_POST['guardian']);
        
        // First, insert into users table (assuming basic user account)
        $username = strtolower($first_name . '.' . $last_name);
        // Check if username exists
        $check = $conn->query("SELECT id FROM users WHERE username = '$username'");
        if ($check->num_rows > 0) {
            $username = strtolower($first_name . '.' . $last_name . rand(1, 999));
        }
        
        $password = password_hash('default123', PASSWORD_DEFAULT);
        
        $conn->query("
            INSERT INTO users (username, password, role, first_name, middle_name, last_name) 
            VALUES ('$username', '$password', 'student', '$first_name', '$middle_name', '$last_name')
        ");
        
        $user_id = $conn->insert_id;
        
        // Then insert into students table
        $conn->query("
            INSERT INTO students (user_id, student_number, lrn, section_id, year_level, education_level, contact, guardian, enrollment_status) 
            VALUES ($user_id, '$student_number', '$lrn', $section_id, '$year_level', '$education_level', '$contact', '$guardian', 'inactive')
        ");
        
        echo "<script>toastr.success('Student added successfully!');</script>";
    }
    
    if (isset($_POST['edit_student'])) {
        $student_id = (int)$_POST['student_id'];
        $user_id = (int)$_POST['user_id']; // Get user_id from form
        $first_name = $conn->real_escape_string($_POST['first_name']);
        $middle_name = $conn->real_escape_string($_POST['middle_name']);
        $last_name = $conn->real_escape_string($_POST['last_name']);
        $lrn = $conn->real_escape_string($_POST['lrn']);
        $section_id = (int)$_POST['section_id'];
        $year_level = $conn->real_escape_string($_POST['year_level']);
        $education_level = $conn->real_escape_string($_POST['education_level']);
        $contact = $conn->real_escape_string($_POST['contact']);
        $guardian = $conn->real_escape_string($_POST['guardian']);
        $status = $conn->real_escape_string($_POST['enrollment_status']);
        
        // Update users table
        $conn->query("
            UPDATE users 
            SET first_name = '$first_name', 
                middle_name = '$middle_name',
                last_name = '$last_name'
            WHERE id = $user_id
        ");
        
        // Update students table
        $conn->query("
            UPDATE students 
            SET lrn = '$lrn', 
                section_id = $section_id, 
                year_level = '$year_level',
                education_level = '$education_level',
                contact = '$contact',
                guardian = '$guardian',
                enrollment_status = '$status'
            WHERE student_id = $student_id
        ");
        
        echo "<script>toastr.success('Student updated successfully!');</script>";
    }
    
    if (isset($_POST['delete_student'])) {
        $student_id = (int)$_POST['student_id'];
        $user_id = (int)$_POST['user_id'];
        
        // Delete from students first (foreign key constraint)
        $conn->query("DELETE FROM students WHERE student_id = $student_id");
        // Then delete from users
        $conn->query("DELETE FROM users WHERE id = $user_id");
        
        echo "<script>toastr.success('Student deleted successfully!');</script>";
    }
}

// Get all sections for dropdown
$sections = $conn->query("SELECT * FROM sections ORDER BY section_name");

// Update the filter tabs to display 'All Students', 'Enrolled', and 'Unenrolled'


// Update query to handle the new filters
$filter_status = isset($_GET['filter_status']) ? $_GET['filter_status'] : null;

$students_query = "
    SELECT s.student_id, s.student_number, s.year_level, s.section_id,
           u.first_name, u.middle_name, u.last_name, sec.section_name, s.enrollment_status, s.education_level, s.user_id
    FROM students s
    JOIN users u ON s.user_id = u.id
    JOIN sections sec ON s.section_id = sec.section_id
";

if ($filter_status === 'enrolled') {
    $students_query .= " WHERE s.enrollment_status = 'active'";
} elseif ($filter_status === 'unenrolled') {
    $students_query .= " WHERE s.enrollment_status = 'inactive'";
}

$students_query .= " ORDER BY u.last_name, u.first_name";
$students = $conn->query($students_query);

if (!$students) {
    die("<div class='alert alert-danger'>Error fetching students: " . $conn->error . "</div>");
}

// Update filter dropdown in the UI
?>


<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-users me-2"></i>Student Management</h2>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addStudentModal">
        <i class="fas fa-plus"></i> Add New Student
    </button>
</div>

<!-- Students Table -->
<div class="card">

<div class="card-header">
    <ul class="nav nav-tabs">
        <li class="nav-item">
            <a class="nav-link <?php echo !isset($_GET['filter_status']) ? 'active' : ''; ?>" href="?tab=students">All Students</a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo (isset($_GET['filter_status']) && $_GET['filter_status'] === 'enrolled') ? 'active' : ''; ?>" href="?tab=students&filter_status=enrolled">Enrolled</a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo (isset($_GET['filter_status']) && $_GET['filter_status'] === 'unenrolled') ? 'active' : ''; ?>" href="?tab=students&filter_status=unenrolled">Unenrolled</a>
        </li>
    </ul>
</div>

    <div class="card-body">
        <div class="tab-content">
            <div class="tab-pane active" id="all">
                <table class="table table-hover datatable">
                    <thead>
                        <tr>
                            <th>Student #</th>
                            <th>LRN</th>
                            <th>Name</th>
                            <th>Section</th>
                            <th>Year Level</th>
                            <th>Education Level</th>
                            <th>Contact</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($student = $students->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $student['student_number']; ?></td>
                            <td><?php echo $student['lrn'] ?? 'N/A'; ?></td>
                            <td><?php echo $student['first_name'] . ' ' . ($student['middle_name'] ? $student['middle_name'] . ' ' : '') . $student['last_name']; ?></td>
                            <td><?php echo $student['section_name']; ?></td>
                            <td><?php echo $student['year_level']; ?></td>
                            <td><?php echo ucfirst(str_replace('_', ' ', $student['education_level'])); ?></td>
                            <td><?php echo $student['contact'] ?? 'N/A'; ?></td>
                            <td>
                                <span class="badge bg-<?php echo $student['enrollment_status'] == 'active' ? 'success' : 'secondary'; ?>">
                                    <?php echo $student['enrollment_status'] == 'active' ? 'Enrolled' : 'Unenrolled'; ?>
                                </span>
                            </td>
                            <td>
                                <?php if (isset($student['student_id'])): ?>
                                    <button class="btn btn-sm btn-info" onclick="viewStudent(<?php echo $student['student_id']; ?>)">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn btn-sm btn-warning" onclick="editStudent(<?php echo $student['student_id']; ?>)">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                <?php else: ?>
                                    <span class="text-danger">Error: Missing student ID</span>
                                <?php endif; ?>

                                <?php if (isset($student['user_id'])): ?>
                                    <button class="btn btn-sm btn-danger" onclick="deleteStudent(<?php echo $student['student_id']; ?>, <?php echo $student['user_id']; ?>)">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                <?php else: ?>
                                    <span class="text-danger">Error: Missing user ID</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            <!-- Add similar tables for active/inactive tabs -->
        </div>
    </div>
</div>

<!-- Add Student Modal -->
<div class="modal fade" id="addStudentModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Student</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label>First Name</label>
                            <input type="text" name="first_name" class="form-control" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label>Middle Name <small class="text-muted">(Optional)</small></label>
                            <input type="text" name="middle_name" class="form-control">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label>Last Name</label>
                            <input type="text" name="last_name" class="form-control" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label>LRN (Optional)</label>
                            <input type="text" name="lrn" class="form-control" maxlength="12">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Education Level</label>
                            <select name="education_level" class="form-select" required>
                                <option value="senior_high">Senior High School</option>
                                <option value="college">College</option>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label>Section/Strand</label>
                            <select name="section_id" class="form-select" required>
                                <?php 
                                $sections->data_seek(0);
                                while($section = $sections->fetch_assoc()): 
                                ?>
                                <option value="<?php echo $section['section_id']; ?>">
                                    <?php echo $section['section_name']; ?> (<?php echo $section['year_level']; ?>)
                                </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Year Level</label>
                            <select name="year_level" class="form-select" required>
                                <option value="Grade 11">Grade 11</option>
                                <option value="Grade 12">Grade 12</option>
                                <option value="1st Year">1st Year</option>
                                <option value="2nd Year">2nd Year</option>
                                <option value="3rd Year">3rd Year</option>
                                <option value="4th Year">4th Year</option>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label>Contact Number</label>
                            <input type="text" name="contact" class="form-control">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Guardian Name</label>
                            <input type="text" name="guardian" class="form-control">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="add_student" class="btn btn-primary">Add Student</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
let allSections = [];

function viewStudent(id) {
    $.ajax({
        url: 'api/get_student.php',
        method: 'GET',
        data: {id: id},
        dataType: 'json',
        success: function(data) {
            let student = data.student;
            let middleName = student.middle_name ? student.middle_name + ' ' : '';
            let details = `
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Student Number:</strong> ${student.student_number}</p>
                        <p><strong>Name:</strong> ${student.first_name} ${middleName}${student.last_name}</p>
                        <p><strong>LRN:</strong> ${student.lrn || 'N/A'}</p>
                        <p><strong>Year Level:</strong> ${student.year_level}</p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Section:</strong> ${student.section_name}</p>
                        <p><strong>Education Level:</strong> ${student.education_level}</p>
                        <p><strong>Contact:</strong> ${student.contact || 'N/A'}</p>
                        <p><strong>Guardian:</strong> ${student.guardian || 'N/A'}</p>
                        <p><strong>Status:</strong> ${student.enrollment_status}</p>
                    </div>
                </div>
            `;
            
            showModal('View Student', details, [
                {text: 'Close', class: 'btn-secondary', dismiss: true}
            ]);
        },
        error: function() {
            toastr.error('Error loading student information');
        }
    });
}

function editStudent(id) {
    $.ajax({
        url: 'api/get_student.php',
        method: 'GET',
        data: {id: id},
        dataType: 'json',
        success: function(data) {
            let student = data.student;
            let sections = data.sections;
            
            let sectionsHtml = '';
            sections.forEach(function(section) {
                let selected = section.section_id == student.section_id ? 'selected' : '';
                sectionsHtml += `<option value="${section.section_id}" ${selected}>${section.section_name} (${section.year_level})</option>`;
            });
            
            let form = `
                <form id="editStudentForm" method="POST">
                    <input type="hidden" name="student_id" value="${student.student_id}">
                    <input type="hidden" name="user_id" value="${student.user_id}">
                    <input type="hidden" name="edit_student" value="1">
                    
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label>First Name</label>
                            <input type="text" name="first_name" class="form-control" value="${student.first_name}" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label>Middle Name <small class="text-muted">(Optional)</small></label>
                            <input type="text" name="middle_name" class="form-control" value="${student.middle_name || ''}">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label>Last Name</label>
                            <input type="text" name="last_name" class="form-control" value="${student.last_name}" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label>LRN (Optional)</label>
                            <input type="text" name="lrn" class="form-control" maxlength="12" value="${student.lrn || ''}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Education Level</label>
                            <select name="education_level" class="form-select" required>
                                <option value="senior_high" ${student.education_level === 'senior_high' ? 'selected' : ''}>Senior High School</option>
                                <option value="college" ${student.education_level === 'college' ? 'selected' : ''}>College</option>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label>Section/Strand</label>
                            <select name="section_id" class="form-select" required>
                                ${sectionsHtml}
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Year Level</label>
                            <select name="year_level" class="form-select" required>
                                <option value="Grade 11" ${student.year_level === 'Grade 11' ? 'selected' : ''}>Grade 11</option>
                                <option value="Grade 12" ${student.year_level === 'Grade 12' ? 'selected' : ''}>Grade 12</option>
                                <option value="1st Year" ${student.year_level === '1st Year' ? 'selected' : ''}>1st Year</option>
                                <option value="2nd Year" ${student.year_level === '2nd Year' ? 'selected' : ''}>2nd Year</option>
                                <option value="3rd Year" ${student.year_level === '3rd Year' ? 'selected' : ''}>3rd Year</option>
                                <option value="4th Year" ${student.year_level === '4th Year' ? 'selected' : ''}>4th Year</option>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label>Contact Number</label>
                            <input type="text" name="contact" class="form-control" value="${student.contact || ''}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Guardian Name</label>
                            <input type="text" name="guardian" class="form-control" value="${student.guardian || ''}">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label>Enrollment Status</label>
                        <select name="enrollment_status" class="form-select" required>
                            <option value="active" ${student.enrollment_status === 'active' ? 'selected' : ''}>Active</option>
                            <option value="inactive" ${student.enrollment_status === 'inactive' ? 'selected' : ''}>Inactive</option>
                        </select>
                    </div>
                </form>
            `;
            
            showModal('Edit Student', form, [
                {text: 'Cancel', class: 'btn-secondary', dismiss: true},
                {text: 'Save Changes', class: 'btn-primary', onclick: 'submitEditForm()'}
            ]);
        },
        error: function() {
            toastr.error('Error loading student information');
        }
    });
}

function submitEditForm() {
    $.ajax({
        url: '',
        method: 'POST',
        data: $('#editStudentForm').serialize(),
        success: function(response) {
            toastr.success('Student updated successfully!');
            setTimeout(function() {
                location.reload();
            }, 1000);
        },
        error: function() {
            toastr.error('Error updating student');
        }
    });
}

function deleteStudent(id, userId) {
    if (confirm('Are you sure you want to delete this student?')) {
        $.ajax({
            url: '',
            method: 'POST',
            data: {
                delete_student: 1,
                student_id: id,
                user_id: userId
            },
            success: function(response) {
                toastr.success('Student deleted successfully!');
                setTimeout(function() {
                    location.reload();
                }, 1000);
            },
            error: function() {
                toastr.error('Error deleting student');
            }
        });
    }
}

function showModal(title, content, buttons) {
    // Remove old modal if exists
    $('#dynamicModal').remove();
    
    let modal = `
        <div class="modal fade" id="dynamicModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">${title}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        ${content}
                    </div>
                    <div class="modal-footer">
    `;
    
    buttons.forEach(function(btn) {
        if (btn.dismiss) {
            modal += `<button type="button" class="btn ${btn.class}" data-bs-dismiss="modal">${btn.text}</button>`;
        } else if (btn.onclick) {
            modal += `<button type="button" class="btn ${btn.class}" onclick="${btn.onclick}">${btn.text}</button>`;
        }
    });
    
    modal += `
                    </div>
                </div>
            </div>
        </div>
    `;
    
    $('body').append(modal);
    
    let bsModal = new bootstrap.Modal(document.getElementById('dynamicModal'));
    bsModal.show();
}
</script>