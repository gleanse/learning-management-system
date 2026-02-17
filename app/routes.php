<?php

require_once __DIR__ . '/controllers/AuthController.php';
require_once __DIR__ . '/controllers/GradeController.php';
require_once __DIR__ . '/controllers/StudentController.php';
require_once __DIR__ . '/controllers/TeacherAssignmentController.php';
require_once __DIR__ . '/controllers/DashboardController.php';
require_once __DIR__ . '/controllers/SubjectController.php';
require_once __DIR__ . '/controllers/SectionController.php';
require_once __DIR__ . '/controllers/StudentSectionController.php';
require_once __DIR__ . '/controllers/ScheduleManagementController.php';

$page = $_GET['page'] ?? 'login';
$method = $_SERVER['REQUEST_METHOD'];

// HELPER functions
function isLoggedIn()
{
    return isset($_SESSION['user_id']);
}

function isTeacher()
{
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'teacher';
}

function isStudent()
{
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'student';
}

function isAdmin()
{
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

function isSuperAdmin()
{
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'superadmin';
}

function isRegistrar()
{
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'registrar';
}

// ADMIN dashboard
if ($page === 'admin_dashboard' && $method === 'GET') {
    if (!isLoggedIn() || (!isAdmin() && !isSuperAdmin())) {
        header('Location: index.php?page=login');
        exit();
    }

    $controller = new DashboardController();
    $controller->showDashboard();
    exit();
}

// SUBJECT MANAGEMENT ROUTES (admin only)
if ($page === 'subjects' && $method === 'GET') {
    if (!isLoggedIn() || (!isAdmin() && !isSuperAdmin())) {
        header('Location: index.php?page=login');
        exit();
    }

    $controller = new SubjectController();
    $controller->showSubjectList();
    exit();
}

if ($page === 'ajax_search_subjects' && $method === 'GET') {
    if (!isLoggedIn() || (!isAdmin() && !isSuperAdmin())) {
        header('Location: index.php?page=login');
        exit();
    }

    $controller = new SubjectController();
    $controller->ajaxSearchSubjects();
    exit();
}

if ($page === 'create_subject' && $method === 'GET') {
    if (!isLoggedIn() || (!isAdmin() && !isSuperAdmin())) {
        header('Location: index.php?page=login');
        exit();
    }

    $controller = new SubjectController();
    $controller->showCreateSubject();
    exit();
}

if ($page === 'create_subject_action' && $method === 'POST') {
    if (!isLoggedIn() || (!isAdmin() && !isSuperAdmin())) {
        header('Location: index.php?page=login');
        exit();
    }

    $controller = new SubjectController();
    $controller->createSubject();
    exit();
}

if ($page === 'edit_subject' && $method === 'GET') {
    if (!isLoggedIn() || (!isAdmin() && !isSuperAdmin())) {
        header('Location: index.php?page=login');
        exit();
    }

    $controller = new SubjectController();
    $controller->showEditSubject();
    exit();
}

if ($page === 'update_subject_action' && $method === 'POST') {
    if (!isLoggedIn() || (!isAdmin() && !isSuperAdmin())) {
        header('Location: index.php?page=login');
        exit();
    }

    $controller = new SubjectController();
    $controller->updateSubject();
    exit();
}

if ($page === 'delete_subject_action' && $method === 'POST') {
    if (!isLoggedIn() || (!isAdmin() && !isSuperAdmin())) {
        header('Location: index.php?page=login');
        exit();
    }

    $controller = new SubjectController();
    $controller->deleteSubject();
    exit();
}

// SECTION MANAGEMENT ROUTES (admin only)
if ($page === 'manage_sections' && $method === 'GET') {
    if (!isLoggedIn() || (!isAdmin() && !isSuperAdmin())) {
        header('Location: index.php?page=login');
        exit();
    }

    $controller = new SectionController();
    $controller->showManageSections();
    exit();
}

if ($page === 'ajax_search_sections' && $method === 'GET') {
    if (!isLoggedIn() || (!isAdmin() && !isSuperAdmin())) {
        header('Location: index.php?page=login');
        exit();
    }

    $controller = new SectionController();
    $controller->ajaxSearchSections();
    exit();
}

if ($page === 'ajax_section_students' && $method === 'GET') {
    if (!isLoggedIn() || (!isAdmin() && !isSuperAdmin())) {
        header('Location: index.php?page=login');
        exit();
    }

    $controller = new SectionController();
    $controller->ajaxSectionStudents();
    exit();
}

if ($page === 'create_section' && $method === 'GET') {
    if (!isLoggedIn() || (!isAdmin() && !isSuperAdmin())) {
        header('Location: index.php?page=login');
        exit();
    }

    $controller = new SectionController();
    $controller->showCreateSection();
    exit();
}

if ($page === 'create_section' && $method === 'POST') {
    if (!isLoggedIn() || (!isAdmin() && !isSuperAdmin())) {
        header('Location: index.php?page=login');
        exit();
    }

    $controller = new SectionController();
    $controller->processCreateSection();
    exit();
}

if ($page === 'edit_section' && $method === 'GET') {
    if (!isLoggedIn() || (!isAdmin() && !isSuperAdmin())) {
        header('Location: index.php?page=login');
        exit();
    }

    $controller = new SectionController();
    $controller->showEditSection();
    exit();
}

if ($page === 'update_section' && $method === 'POST') {
    if (!isLoggedIn() || (!isAdmin() && !isSuperAdmin())) {
        header('Location: index.php?page=login');
        exit();
    }

    $controller = new SectionController();
    $controller->processUpdateSection();
    exit();
}

if ($page === 'delete_section' && $method === 'POST') {
    if (!isLoggedIn() || (!isAdmin() && !isSuperAdmin())) {
        header('Location: index.php?page=login');
        exit();
    }

    $controller = new SectionController();
    $controller->processDeleteSection();
    exit();
}

if ($page === 'view_section' && $method === 'GET') {
    if (!isLoggedIn() || (!isAdmin() && !isSuperAdmin())) {
        header('Location: index.php?page=login');
        exit();
    }

    $controller = new SectionController();
    $controller->showViewSection();
    exit();
}

// STUDENT SECTION ASSIGNMENT ROUTES (admin only)
if ($page === 'student_sections' && $method === 'GET') {
    if (!isLoggedIn() || (!isAdmin() && !isSuperAdmin())) {
        header('Location: index.php?page=login');
        exit();
    }

    $controller = new StudentSectionController();
    $controller->showAssignmentPage();
    exit();
}

if ($page === 'student_section_data' && $method === 'GET') {
    if (!isLoggedIn() || (!isAdmin() && !isSuperAdmin())) {
        header('Location: index.php?page=login');
        exit();
    }

    $controller = new StudentSectionController();
    $controller->getSectionData();
    exit();
}

if ($page === 'search_eligible_students' && $method === 'GET') {
    if (!isLoggedIn() || (!isAdmin() && !isSuperAdmin())) {
        header('Location: index.php?page=login');
        exit();
    }

    $controller = new StudentSectionController();
    $controller->searchEligibleStudents();
    exit();
}

if ($page === 'search_current_students' && $method === 'GET') {
    if (!isLoggedIn() || (!isAdmin() && !isSuperAdmin())) {
        header('Location: index.php?page=login');
        exit();
    }

    $controller = new StudentSectionController();
    $controller->searchCurrentStudents();
    exit();
}

if ($page === 'assign_students' && $method === 'POST') {
    if (!isLoggedIn() || (!isAdmin() && !isSuperAdmin())) {
        header('Location: index.php?page=login');
        exit();
    }

    $controller = new StudentSectionController();
    $controller->processAssignment();
    exit();
}

if ($page === 'remove_student' && $method === 'POST') {
    if (!isLoggedIn() || (!isAdmin() && !isSuperAdmin())) {
        header('Location: index.php?page=login');
        exit();
    }

    $controller = new StudentSectionController();
    $controller->processRemoveStudent();
    exit();
}

if ($page === 'bulk_remove_students' && $method === 'POST') {
    if (!isLoggedIn() || (!isAdmin() && !isSuperAdmin())) {
        header('Location: index.php?page=login');
        exit();
    }

    $controller = new StudentSectionController();
    $controller->processBulkRemove();
    exit();
}

// SCHEDULE MANAGEMENT ROUTES (admin only)

// ajax paginated teacher list for schedule picker
if ($page === 'ajax_get_teachers' && $method === 'GET') {
    if (!isLoggedIn() || (!isAdmin() && !isSuperAdmin())) {
        header('Location: index.php?page=login');
        exit();
    }

    $controller = new ScheduleManagementController();
    $controller->ajaxGetTeachers();
    exit();
}

// teacher-first schedule management page
if ($page === 'teacher_schedules' && $method === 'GET') {
    if (!isLoggedIn() || (!isAdmin() && !isSuperAdmin())) {
        header('Location: index.php?page=login');
        exit();
    }

    $controller = new ScheduleManagementController();
    $controller->showTeacherSchedulePage();
    exit();
}

// ajax: get assignments + schedules for a teacher
if ($page === 'ajax_get_teacher_assignments' && $method === 'GET') {
    if (!isLoggedIn() || (!isAdmin() && !isSuperAdmin())) {
        header('Location: index.php?page=login');
        exit();
    }

    $controller = new ScheduleManagementController();
    $controller->ajaxGetTeacherAssignments();
    exit();
}

// ajax: get schedule entries for a single assignment row
if ($page === 'ajax_get_assignment_schedules' && $method === 'GET') {
    if (!isLoggedIn() || (!isAdmin() && !isSuperAdmin())) {
        header('Location: index.php?page=login');
        exit();
    }

    $controller = new ScheduleManagementController();
    $controller->ajaxGetAssignmentSchedules();
    exit();
}

// process create schedule (AJAX from teacher_schedules modal)
if ($page === 'create_schedule' && $method === 'POST') {
    if (!isLoggedIn() || (!isAdmin() && !isSuperAdmin())) {
        header('Location: index.php?page=login');
        exit();
    }

    $controller = new ScheduleManagementController();
    $controller->processCreateSchedule();
    exit();
}

// process update schedule (AJAX from teacher_schedules modal)
if ($page === 'update_schedule' && $method === 'POST') {
    if (!isLoggedIn() || (!isAdmin() && !isSuperAdmin())) {
        header('Location: index.php?page=login');
        exit();
    }

    $controller = new ScheduleManagementController();
    $controller->processUpdateSchedule();
    exit();
}

// process delete schedule (AJAX from teacher_schedules modal)
if ($page === 'delete_schedule' && $method === 'POST') {
    if (!isLoggedIn() || (!isAdmin() && !isSuperAdmin())) {
        header('Location: index.php?page=login');
        exit();
    }

    $controller = new ScheduleManagementController();
    $controller->processDeleteSchedule();
    exit();
}

// TEACHER ASSIGNMENT ROUTES (admin only)
if ($page === 'teacher_assignments' && $method === 'GET') {
    if (!isLoggedIn() || (!isAdmin() && !isSuperAdmin())) {
        header('Location: index.php?page=login');
        exit();
    }

    $controller = new TeacherAssignmentController();
    $controller->showAssignmentPage();
    exit();
}

if ($page === 'assign_teacher' && $method === 'POST') {
    if (!isLoggedIn() || (!isAdmin() && !isSuperAdmin())) {
        header('Location: index.php?page=login');
        exit();
    }

    $controller = new TeacherAssignmentController();
    $controller->processAssignment();
    exit();
}

if ($page === 'show_reassign' && $method === 'GET') {
    if (!isLoggedIn() || (!isAdmin() && !isSuperAdmin())) {
        header('Location: index.php?page=login');
        exit();
    }

    $controller = new TeacherAssignmentController();
    $controller->showReassignForm();
    exit();
}

if ($page === 'reassign_teacher' && $method === 'POST') {
    if (!isLoggedIn() || (!isAdmin() && !isSuperAdmin())) {
        header('Location: index.php?page=login');
        exit();
    }

    $controller = new TeacherAssignmentController();
    $controller->processReassignment();
    exit();
}

if ($page === 'remove_teacher_assignment' && $method === 'POST') {
    if (!isLoggedIn() || (!isAdmin() && !isSuperAdmin())) {
        header('Location: index.php?page=login');
        exit();
    }

    $controller = new TeacherAssignmentController();
    $controller->processRemoveAssignment();
    exit();
}

if ($page === 'restore_teacher_assignment' && $method === 'POST') {
    if (!isLoggedIn() || (!isAdmin() && !isSuperAdmin())) {
        header('Location: index.php?page=login');
        exit();
    }

    $controller = new TeacherAssignmentController();
    $controller->processRestoreAssignment();
    exit();
}

if ($page === 'ajax_search_assignment_subjects' && $method === 'GET') {
    if (!isLoggedIn() || (!isAdmin() && !isSuperAdmin())) {
        header('Location: index.php?page=login');
        exit();
    }

    $controller = new TeacherAssignmentController();
    $controller->ajaxSearchSubjectsForAssignment();
    exit();
}

if ($page === 'ajax_search_reassignment_subjects' && $method === 'GET') {
    if (!isLoggedIn() || (!isAdmin() && !isSuperAdmin())) {
        header('Location: index.php?page=login');
        exit();
    }

    $controller = new TeacherAssignmentController();
    $controller->ajaxSearchSubjectsForReassignment();
    exit();
}

if ($page === 'ajax_search_assignment_teachers' && $method === 'GET') {
    if (!isLoggedIn() || (!isAdmin() && !isSuperAdmin())) {
        header('Location: index.php?page=login');
        exit();
    }

    $controller = new TeacherAssignmentController();
    $controller->ajaxSearchTeachers();
    exit();
}

if ($page === 'ajax_search_assignment_sections' && $method === 'GET') {
    if (!isLoggedIn() || (!isAdmin() && !isSuperAdmin())) {
        header('Location: index.php?page=login');
        exit();
    }

    $controller = new TeacherAssignmentController();
    $controller->ajaxSearchSections();
    exit();
}

// TEACHER DASHBOARD ROUTES (teacher only)
if ($page === 'teacher_dashboard' && $method === 'GET') {
    if (!isLoggedIn() || !isTeacher()) {
        header('Location: index.php?page=login');
        exit();
    }

    $controller = new GradeController();
    $controller->showTeacherDashboard();
    exit();
}

// GRADING MANAGEMENT ROUTES (teacher only)
if ($page === 'grading' && $method === 'GET') {
    if (!isLoggedIn() || !isTeacher()) {
        header('Location: index.php?page=login');
        exit();
    }

    $controller = new GradeController();
    $controller->showYearLevels();
    exit();
}

if ($page === 'grading_subjects' && $method === 'GET') {
    if (!isLoggedIn() || !isTeacher()) {
        header('Location: index.php?page=login');
        exit();
    }

    $controller = new GradeController();
    $controller->showSubjects();
    exit();
}

if ($page === 'grading_sections' && $method === 'GET') {
    if (!isLoggedIn() || !isTeacher()) {
        header('Location: index.php?page=login');
        exit();
    }

    $controller = new GradeController();
    $controller->showSections();
    exit();
}

if ($page === 'grading_students' && $method === 'GET') {
    if (!isLoggedIn() || !isTeacher()) {
        header('Location: index.php?page=login');
        exit();
    }

    $controller = new GradeController();
    $controller->showStudentList();
    exit();
}

if ($page === 'save_grade' && $method === 'POST') {
    if (!isLoggedIn() || !isTeacher()) {
        header('Location: index.php?page=login');
        exit();
    }

    $controller = new GradeController();
    $controller->processSaveGrade();
    exit();
}

// STUDENT DASHBOARD ROUTES (student only)
if ($page === 'student_dashboard' && $method === 'GET') {
    if (!isLoggedIn() || !isStudent()) {
        header('Location: index.php?page=login');
        exit();
    }

    $controller = new StudentController();
    $controller->showStudentDashboard();
    exit();
}

if ($page === 'student_grades' && $method === 'GET') {
    if (!isLoggedIn() || !isStudent()) {
        header('Location: index.php?page=login');
        exit();
    }

    $controller = new StudentController();
    $controller->showYearLevels();
    exit();
}

if ($page === 'student_semesters' && $method === 'GET') {
    if (!isLoggedIn() || !isStudent()) {
        header('Location: index.php?page=login');
        exit();
    }

    $controller = new StudentController();
    $controller->showSemesters();
    exit();
}

if ($page === 'student_subjects' && $method === 'GET') {
    if (!isLoggedIn() || !isStudent()) {
        header('Location: index.php?page=login');
        exit();
    }

    $controller = new StudentController();
    $controller->showSubjects();
    exit();
}

if ($page === 'student_grades_view' && $method === 'GET') {
    if (!isLoggedIn() || !isStudent()) {
        header('Location: index.php?page=login');
        exit();
    }

    $controller = new StudentController();
    $controller->showGrades();
    exit();
}

// REGISTRAR ROUTES (registrar only)
if ($page === 'registrar_dashboard' && $method === 'GET') {
    if (!isLoggedIn() || !isRegistrar()) {
        header('Location: index.php?page=login');
        exit();
    }

    // TODO: view here for the registrar dashboard
    // include __DIR__ . '';
    exit();
}

// AUTH ROUTES
if ($page === 'login' && $method === 'GET') {
    if (isLoggedIn()) {
        if (isTeacher()) {
            header('Location: index.php?page=teacher_dashboard');
        } elseif (isStudent()) {
            header('Location: index.php?page=student_dashboard');
        } elseif (isAdmin()) {
            header('Location: index.php?page=admin_dashboard');
        } elseif (isSuperAdmin()) {
            header('Location: index.php?page=superadmin_dashboard');
        } elseif (isRegistrar()) {
            header('Location: index.php?page=registrar_dashboard');
        }
        exit();
    }

    $controller = new AuthController();
    $controller->showLoginForm();
    exit();
}

if ($page === 'login' && $method === 'POST') {
    $controller = new AuthController();
    $controller->processLogin();
    exit();
}

if ($page === 'dashboard' && $method === 'GET') {
    if (!isLoggedIn()) {
        header('Location: index.php?page=login');
        exit();
    }

    if (isTeacher()) {
        header('Location: index.php?page=teacher_dashboard');
    } elseif (isStudent()) {
        header('Location: index.php?page=student_dashboard');
    } elseif (isAdmin()) {
        header('Location: index.php?page=admin_dashboard');
    } elseif (isSuperAdmin()) {
        header('Location: index.php?page=superadmin_dashboard');
    } elseif (isRegistrar()) {
        header('Location: index.php?page=registrar_dashboard');
    } else {
        header('Location: index.php?page=login');
    }
    exit();
}

if ($page === 'logout' && $method === 'GET') {
    $controller = new AuthController();
    $controller->logout();
    exit();
}

if ($page === 'register' && $method === 'GET') {
    $controller = new AuthController();
    $controller->showRegisterForm();
    exit();
}

if ($page === 'register' && $method === 'POST') {
    $controller = new AuthController();
    $controller->processRegister();
    exit();
}

// 404 fallback
if (isLoggedIn()) {
    if (isTeacher()) {
        header('Location: index.php?page=teacher_dashboard');
    } elseif (isStudent()) {
        header('Location: index.php?page=student_dashboard');
    } elseif (isAdmin()) {
        header('Location: index.php?page=admin_dashboard');
    } elseif (isSuperAdmin()) {
        header('Location: index.php?page=superadmin_dashboard');
    } elseif (isRegistrar()) {
        header('Location: index.php?page=registrar_dashboard');
    } else {
        header('Location: index.php?page=login');
    }
} else {
    header('Location: index.php?page=login');
}
