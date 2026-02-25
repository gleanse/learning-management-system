<div class="card report-card">
    <div class="card-header">
        <h5 class="mb-0">
            <i class="bi bi-file-check-fill"></i>
            Grade Submission Report
        </h5>
    </div>
    <div class="card-body">
        <?php if (!empty($data['submission_rates'])): ?>
            <!-- teacher submission rates -->
            <h6 class="trends-title mb-3">
                <i class="bi bi-person-check-fill"></i>
                Teacher Submission Rates
            </h6>
            <div class="table-responsive mb-5">
                <table class="table data-table">
                    <thead>
                        <tr>
                            <th>Teacher</th>
                            <th>Subjects Handled</th>
                            <th>Total Students</th>
                            <th>Grades Submitted</th>
                            <th>Submission Rate</th>
                            <th>Progress</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($data['submission_rates'] as $teacher): ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <i class="bi bi-person-circle" style="color: var(--primary);"></i>
                                        <span class="fw-semibold"><?= htmlspecialchars($teacher['teacher_name']) ?></span>
                                    </div>
                                </td>
                                <td><?= number_format($teacher['subjects_handled']) ?></td>
                                <td><?= number_format($teacher['total_students']) ?></td>
                                <td><?= number_format($teacher['grades_submitted']) ?></td>
                                <td>
                                    <span class="fw-bold <?= $teacher['submission_rate'] >= 90 ? 'text-success' : ($teacher['submission_rate'] >= 70 ? 'text-warning' : 'text-danger') ?>">
                                        <?= number_format($teacher['submission_rate'], 1) ?>%
                                    </span>
                                </td>
                                <td style="width: 150px;">
                                    <div class="progress-wrapper">
                                        <div class="progress-bar-container">
                                            <div class="progress-bar" style="width: <?= min($teacher['submission_rate'], 100) ?>%;"></div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

        <?php if (!empty($data['grading_progress'])): ?>
            <!-- grading period progress -->
            <h6 class="trends-title mb-3">
                <i class="bi bi-calendar-check-fill"></i>
                Grading Period Progress
            </h6>
            <div class="table-responsive">
                <table class="table data-table">
                    <thead>
                        <tr>
                            <th>Grading Period</th>
                            <th>Deadline</th>
                            <th>Status</th>
                            <th>Grades Encoded</th>
                            <th>Expected Grades</th>
                            <th>Completion</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($data['grading_progress'] as $period): ?>
                            <?php
                            $is_expired = strtotime($period['deadline_date']) < time() && !$period['is_locked'];
                            $status_class = $period['is_locked'] ? 'badge-success' : ($is_expired ? 'badge-danger' : 'badge-warning');
                            $status_text = $period['is_locked'] ? 'Locked' : ($is_expired ? 'Expired' : 'Open');
                            ?>
                            <tr>
                                <td>
                                    <span class="fw-semibold"><?= htmlspecialchars($period['grading_period']) ?></span>
                                </td>
                                <td>
                                    <?php if ($period['deadline_date']): ?>
                                        <?= date('M d, Y', strtotime($period['deadline_date'])) ?>
                                    <?php else: ?>
                                        <span class="text-muted">Not set</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge <?= $status_class ?>"><?= $status_text ?></span>
                                </td>
                                <td><?= number_format($period['grades_encoded']) ?></td>
                                <td><?= number_format($period['expected_grades']) ?></td>
                                <td>
                                    <div class="progress-wrapper">
                                        <div class="progress-bar-container">
                                            <div class="progress-bar" style="width: <?= min($period['completion_rate'], 100) ?>%;"></div>
                                        </div>
                                        <span class="progress-value">
                                            <?= number_format($period['completion_rate'], 1) ?>%
                                        </span>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

        <?php if (empty($data['submission_rates']) && empty($data['grading_progress'])): ?>
            <div class="empty-state py-5">
                <div class="empty-state-icon">
                    <i class="bi bi-inbox"></i>
                </div>
                <p class="empty-state-text">no grade submission data available</p>
            </div>
        <?php endif; ?>
    </div>
</div>