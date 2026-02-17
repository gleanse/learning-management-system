<?php
// modules/strands.php

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_strand'])) {
        $section_name = $conn->real_escape_string($_POST['section_name']);
        $year_level = $conn->real_escape_string($_POST['year_level']);
        $school_year = $conn->real_escape_string($_POST['school_year']);
        $education_level = $conn->real_escape_string($_POST['education_level']);
        $strand_course = $conn->real_escape_string($_POST['strand_course']);
        
        $conn->query("
            INSERT INTO sections (section_name, year_level, school_year, education_level, strand_course)
            VALUES ('$section_name', '$year_level', '$school_year', '$education_level', '$strand_course')
        ");
        
        echo "<script>toastr.success('Strand/Section added successfully!');</script>";
    }
    
    if (isset($_POST['edit_strand'])) {
        $section_id = (int)$_POST['section_id'];
        $section_name = $conn->real_escape_string($_POST['section_name']);
        $year_level = $conn->real_escape_string($_POST['year_level']);
        $school_year = $conn->real_escape_string($_POST['school_year']);
        $education_level = $conn->real_escape_string($_POST['education_level']);
        $strand_course = $conn->real_escape_string($_POST['strand_course']);
        
        $conn->query("
            UPDATE sections 
            SET section_name = '$section_name',
                year_level = '$year_level',
                school_year = '$school_year',
                education_level = '$education_level',
                strand_course = '$strand_course'
            WHERE section_id = $section_id
        ");
        
        echo "<script>toastr.success('Strand/Section updated successfully!');</script>";
    }
    
    if (isset($_POST['delete_strand'])) {
        $section_id = (int)$_POST['section_id'];
        
        // Check if section has students
        $check = $conn->query("SELECT COUNT(*) as count FROM students WHERE section_id = $section_id");
        $has_students = $check->fetch_assoc()['count'] > 0;
        
        if ($has_students) {
            echo "<script>toastr.error('Cannot delete section with enrolled students!');</script>";
        } else {
            $conn->query("DELETE FROM sections WHERE section_id = $section_id");
            echo "<script>toastr.success('Strand/Section deleted successfully!');</script>";
        }
    }
}

// Get all sections
$sections = $conn->query("
    SELECT s.*, 
           (SELECT COUNT(*) FROM students WHERE section_id = s.section_id) as student_count
    FROM sections s 
    ORDER BY s.school_year DESC, s.year_level, s.section_name
");

// Get academic years for dropdown
$school_years = $conn->query("SELECT DISTINCT school_year FROM sections UNION SELECT school_year FROM academic_years ORDER BY school_year DESC");
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-book me-2"></i>Strands/Sections Management</h2>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addStrandModal">
        <i class="fas fa-plus"></i> Add New Strand/Section
    </button>
</div>

<!-- Sections List -->
<div class="card">
    <div class="card-header">
        <ul class="nav nav-tabs card-header-tabs">
            <li class="nav-item">
                <a class="nav-link active" href="#all" data-bs-toggle="tab">All Sections</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#seniorhigh" data-bs-toggle="tab">Senior High</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#college" data-bs-toggle="tab">College</a>
            </li>
        </ul>
    </div>
    <div class="card-body">
        <div class="tab-content">
            <div class="tab-pane active" id="all">
                <table class="table table-hover datatable">
                    <thead>
                        <tr>
                            <th>Section Name</th>
                            <th>Year Level</th>
                            <th>Education Level</th>
                            <th>Strand/Course</th>
                            <th>School Year</th>
                            <th>Students</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($section = $sections->fetch_assoc()): ?>
                        <tr>
                            <td><strong><?php echo $section['section_name']; ?></strong></td>
                            <td><?php echo $section['year_level']; ?></td>
                            <td><?php echo ucfirst(str_replace('_', ' ', $section['education_level'])); ?></td>
                            <td><?php echo $section['strand_course']; ?></td>
                            <td><?php echo $section['school_year']; ?></td>
                            <td>
                                <span class="badge bg-info"><?php echo $section['student_count']; ?> students</span>
                            </td>
                            <td><?php echo date('M d, Y', strtotime($section['created_at'])); ?></td>
                            <td>
                                <button class="btn btn-sm btn-warning" onclick="editStrand(<?php echo $section['section_id']; ?>)">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-danger" onclick="deleteStrand(<?php echo $section['section_id']; ?>)">
                                    <i class="fas fa-trash"></i>
                                </button>
                                <a href="?tab=fee_management&strand=<?php echo $section['section_id']; ?>" 
                                   class="btn btn-sm btn-info">
                                    <i class="fas fa-calculator"></i> Fees
                                </a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            
            <div class="tab-pane" id="seniorhigh">
                <!-- Filter for Senior High (Grade 11-12) -->
                <?php 
                $sh_sections = $conn->query("
                    SELECT * FROM sections 
                    WHERE year_level IN ('Grade 11', 'Grade 12')
                    ORDER BY school_year DESC, year_level, section_name
                ");
                ?>
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Section Name</th>
                            <th>Year Level</th>
                            <th>Strand/Course</th>
                            <th>School Year</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($section = $sh_sections->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $section['section_name']; ?></td>
                            <td><?php echo $section['year_level']; ?></td>
                            <td><?php echo $section['strand_course']; ?></td>
                            <td><?php echo $section['school_year']; ?></td>
                            <td>
                                <button class="btn btn-sm btn-warning">Edit</button>
                                <button class="btn btn-sm btn-danger">Delete</button>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            
            <div class="tab-pane" id="college">
                <!-- Filter for College (1st Year - 4th Year) -->
                <?php 
                $college_sections = $conn->query("
                    SELECT * FROM sections 
                    WHERE year_level IN ('1st Year', '2nd Year', '3rd Year', '4th Year')
                    ORDER BY school_year DESC, year_level, section_name
                ");
                ?>
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Section Name</th>
                            <th>Year Level</th>
                            <th>Strand/Course</th>
                            <th>School Year</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($section = $college_sections->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $section['section_name']; ?></td>
                            <td><?php echo $section['year_level']; ?></td>
                            <td><?php echo $section['strand_course']; ?></td>
                            <td><?php echo $section['school_year']; ?></td>
                            <td>
                                <button class="btn btn-sm btn-warning">Edit</button>
                                <button class="btn btn-sm btn-danger">Delete</button>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Add Strand Modal -->
<div class="modal fade" id="addStrandModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Strand/Section</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Section Name</label>
                        <input type="text" name="section_name" class="form-control" 
                               placeholder="e.g., BSIT-1A, Grade 11-STEM A" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Education Level</label>
                        <select name="education_level" class="form-control" id="add_education_level" required onchange="updateStrandCourse(this, 'add_strand_course')">
                            <option value="">Select Education Level</option>
                            <option value="senior_high">Senior High School</option>
                            <option value="college">College</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Strand/Course</label>
                        <input type="text" name="strand_course" id="add_strand_course" class="form-control" 
                               placeholder="e.g., STEM, HUMSS, BSIT" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Year Level</label>
                        <select name="year_level" class="form-control" required>
                            <option value="">Select Year Level</option>
                            <option value="Grade 11">Grade 11</option>
                            <option value="Grade 12">Grade 12</option>
                            <option value="1st Year">1st Year College</option>
                            <option value="2nd Year">2nd Year College</option>
                            <option value="3rd Year">3rd Year College</option>
                            <option value="4th Year">4th Year College</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">School Year</label>
                        <select name="school_year" class="form-control" required>
                            <?php 
                            $current_year = date('Y');
                            for($year = $current_year - 1; $year <= $current_year + 2; $year++):
                                $sy = $year . '-' . ($year + 1);
                            ?>
                            <option value="<?php echo $sy; ?>" <?php echo $sy == $current_year . '-' . ($current_year + 1) ? 'selected' : ''; ?>>
                                <?php echo $sy; ?>
                            </option>
                            <?php endfor; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="add_strand" class="btn btn-primary">Add Section</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Strand Modal -->
<div class="modal fade" id="editStrandModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="section_id" id="edit_section_id">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Strand/Section</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Section Name</label>
                        <input type="text" name="section_name" id="edit_section_name" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Education Level</label>
                        <select name="education_level" id="edit_education_level" class="form-control" required>
                            <option value="senior_high">Senior High School</option>
                            <option value="college">College</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Strand/Course</label>
                        <input type="text" name="strand_course" id="edit_strand_course" class="form-control" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Year Level</label>
                        <select name="year_level" id="edit_year_level" class="form-control" required>
                            <option value="Grade 11">Grade 11</option>
                            <option value="Grade 12">Grade 12</option>
                            <option value="1st Year">1st Year College</option>
                            <option value="2nd Year">2nd Year College</option>
                            <option value="3rd Year">3rd Year College</option>
                            <option value="4th Year">4th Year College</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">School Year</label>
                        <select name="school_year" id="edit_school_year" class="form-control" required>
                            <?php 
                            $current_year = date('Y');
                            for($year = $current_year - 1; $year <= $current_year + 2; $year++):
                                $sy = $year . '-' . ($year + 1);
                            ?>
                            <option value="<?php echo $sy; ?>"><?php echo $sy; ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="edit_strand" class="btn btn-primary">Update Section</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteStrandModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="section_id" id="delete_section_id">
                <div class="modal-header">
                    <h5 class="modal-title text-danger">Delete Strand/Section</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete this section?</p>
                    <p class="text-warning"><small>This action cannot be undone.</small></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="delete_strand" class="btn btn-danger">Delete</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function editStrand(id) {
    // In a real application, you would fetch data via AJAX
    // For now, we'll just show the modal with sample data
    $('#edit_section_id').val(id);
    $('#editStrandModal').modal('show');
}

function deleteStrand(id) {
    $('#delete_section_id').val(id);
    $('#deleteStrandModal').modal('show');
}
</script>