<div class="card report-card">
    <div class="card-header">
        <h5 class="mb-0">
            <i class="bi bi-mortarboard-fill"></i>
            Student Performance Summary
        </h5>
    </div>
    <div class="card-body">
        <?php if (!empty($data['performance'])): ?>
            <div class="table-responsive">
                <table class="table data-table">
                    <thead>
                        <tr>
                            <th>Education Level</th>
                            <th>Year Level</th>
                            <th>Strand/Course</th>
                            <th>Students</th>
                            <th>Prelim</th>
                            <th>Midterm</th>
                            <th>Prefinal</th>
                            <th>Final</th>
                            <th>At Risk</th>
                            <th>Excellent</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($data['performance'] as $group): ?>
                            <tr>
                                <td>
                                    <span class="badge <?= $group['education_level'] == 'senior_high' ? 'badge-shs' : 'badge-college' ?>">
                                        <?= $group['education_level'] == 'senior_high' ? 'Senior High' : 'College' ?>
                                    </span>
                                </td>
                                <td><?= htmlspecialchars($group['year_level']) ?></td>
                                <td><?= htmlspecialchars($group['strand_course']) ?></td>
                                <td>
                                    <span class="fw-bold"><?= number_format($group['total_students']) ?></span>
                                </td>
                                <td>
                                    <?php if ($group['avg_prelim']): ?>
                                        <span class="fw-semibold"><?= number_format($group['avg_prelim'], 2) ?></span>
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($group['avg_midterm']): ?>
                                        <span class="fw-semibold"><?= number_format($group['avg_midterm'], 2) ?></span>
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($group['avg_prefinal']): ?>
                                        <span class="fw-semibold"><?= number_format($group['avg_prefinal'], 2) ?></span>
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($group['avg_final']): ?>
                                        <span class="fw-semibold"><?= number_format($group['avg_final'], 2) ?></span>
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($group['students_at_risk'] > 0): ?>
                                        <span class="badge bg-danger">
                                            <?= number_format($group['students_at_risk']) ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted">0</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($group['students_excellent'] > 0): ?>
                                        <span class="badge bg-success">
                                            <?= number_format($group['students_excellent']) ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted">0</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- performance summary -->
            <?php
            $total_students = array_sum(array_column($data['performance'], 'total_students'));
            $total_at_risk = array_sum(array_column($data['performance'], 'students_at_risk'));
            $total_excellent = array_sum(array_column($data['performance'], 'students_excellent'));
            $avg_final_grades = array_filter(array_column($data['performance'], 'avg_final'));
            $overall_average = !empty($avg_final_grades) ? array_sum($avg_final_grades) / count($avg_final_grades) : 0;
            ?>

            <div class="mt-4 p-3 bg-light rounded-3">
                <div class="row g-3">
                    <div class="col-md-3">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-people fs-4" style="color: var(--primary);"></i>
                            <div>
                                <small class="text-muted d-block">Total Students</small>
                                <span class="fw-bold fs-5"><?= number_format($total_students) ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-graph-up fs-4 text-success"></i>
                            <div>
                                <small class="text-muted d-block">Overall Average</small>
                                <span class="fw-bold fs-5"><?= number_format($overall_average, 2) ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-star-fill fs-4 text-warning"></i>
                            <div>
                                <small class="text-muted d-block">Excellent (≥90)</small>
                                <span class="fw-bold fs-5"><?= number_format($total_excellent) ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-exclamation-triangle-fill fs-4 text-danger"></i>
                            <div>
                                <small class="text-muted d-block">At Risk (&lt;75)</small>
                                <span class="fw-bold fs-5"><?= number_format($total_at_risk) ?></span>
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
                <p class="empty-state-text">no performance data available</p>
            </div>
        <?php endif; ?>
    </div>
</div>