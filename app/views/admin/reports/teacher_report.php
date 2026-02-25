<div class="card report-card">
    <div class="card-header">
        <h5 class="mb-0">
            <i class="bi bi-person-workspace"></i>
            Teacher Workload Report
        </h5>
    </div>
    <div class="card-body">
        <?php if (!empty($data['workload'])): ?>
            <div class="table-responsive">
                <table class="table data-table">
                    <thead>
                        <tr>
                            <th>Teacher Name</th>
                            <th>Subjects Taught</th>
                            <th>Sections Handled</th>
                            <th>Total Students</th>
                            <th>Workload Index</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($data['workload'] as $teacher): ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <i class="bi bi-person-circle" style="color: var(--primary);"></i>
                                        <span class="fw-semibold"><?= htmlspecialchars($teacher['teacher_name']) ?></span>
                                    </div>
                                </td>
                                <td><?= number_format($teacher['subjects_taught']) ?></td>
                                <td><?= number_format($teacher['sections_handled']) ?></td>
                                <td><?= number_format($teacher['total_students']) ?></td>
                                <td>
                                    <?php
                                    $workload_index = ($teacher['total_students'] / max($teacher['subjects_taught'], 1));
                                    $workload_class = $workload_index > 40 ? 'utilization-high' : ($workload_index > 25 ? 'utilization-medium' : 'utilization-low');
                                    ?>
                                    <span class="<?= $workload_class ?> fw-bold">
                                        <?= number_format($workload_index, 1) ?> students/subject
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- workload summary -->
            <div class="mt-4 p-3 bg-light rounded-3">
                <div class="row g-3">
                    <?php
                    $avg_subjects = array_sum(array_column($data['workload'], 'subjects_taught')) / count($data['workload']);
                    $avg_students = array_sum(array_column($data['workload'], 'total_students')) / count($data['workload']);
                    $total_teachers = count($data['workload']);
                    ?>
                    <div class="col-md-4">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-people fs-4" style="color: var(--primary);"></i>
                            <div>
                                <small class="text-muted d-block">Average Students/Teacher</small>
                                <span class="fw-bold fs-5"><?= number_format($avg_students, 1) ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-book fs-4" style="color: var(--primary);"></i>
                            <div>
                                <small class="text-muted d-block">Average Subjects/Teacher</small>
                                <span class="fw-bold fs-5"><?= number_format($avg_subjects, 1) ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-person-badge fs-4" style="color: var(--primary);"></i>
                            <div>
                                <small class="text-muted d-block">Total Teachers</small>
                                <span class="fw-bold fs-5"><?= number_format($total_teachers) ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="empty-state py-5">
                <div class="empty-state-icon">
                    <i class="bi bi-inbox"></i>
                </div>
                <p class="empty-state-text">no teacher workload data available</p>
            </div>
        <?php endif; ?>
    </div>
</div>