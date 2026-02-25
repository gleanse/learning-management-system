<div class="card report-card">
    <div class="card-header">
        <h5 class="mb-0">
            <i class="bi bi-grid-3x3-gap-fill"></i>
            Section Utilization Report
        </h5>
    </div>
    <div class="card-body">
        <?php if (!empty($data['utilization'])): ?>
            <div class="table-responsive">
                <table class="table data-table">
                    <thead>
                        <tr>
                            <th>Section</th>
                            <th>Education Level</th>
                            <th>Year Level</th>
                            <th>Strand/Course</th>
                            <th>Capacity</th>
                            <th>Enrolled</th>
                            <th>Utilization</th>
                            <th>Teachers</th>
                            <th>Subjects</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($data['utilization'] as $section): ?>
                            <tr>
                                <td>
                                    <span class="fw-semibold"><?= htmlspecialchars($section['section_name']) ?></span>
                                </td>
                                <td>
                                    <span class="badge <?= $section['education_level'] == 'senior_high' ? 'badge-shs' : 'badge-college' ?>">
                                        <?= $section['education_level'] == 'senior_high' ? 'Senior High' : 'College' ?>
                                    </span>
                                </td>
                                <td><?= htmlspecialchars($section['year_level']) ?></td>
                                <td><?= htmlspecialchars($section['strand_course']) ?></td>
                                <td><?= number_format($section['max_capacity']) ?></td>
                                <td><?= number_format($section['enrolled_students']) ?></td>
                                <td>
                                    <?php 
                                    $utilization = $section['utilization_rate'];
                                    $util_class = $utilization >= 90 ? 'utilization-high' : ($utilization >= 70 ? 'utilization-medium' : 'utilization-low');
                                    ?>
                                    <div class="progress-wrapper">
                                        <div class="progress-bar-container">
                                            <div class="progress-bar" style="width: <?= min($utilization, 100) ?>%;"></div>
                                        </div>
                                        <span class="progress-value <?= $util_class ?>">
                                            <?= number_format($utilization, 1) ?>%
                                        </span>
                                    </div>
                                </td>
                                <td><?= number_format($section['teachers_assigned']) ?></td>
                                <td><?= number_format($section['subjects_offered']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- utilization summary -->
            <div class="mt-4 p-3 bg-light rounded-3">
                <div class="row g-3">
                    <?php 
                    $total_capacity = array_sum(array_column($data['utilization'], 'max_capacity'));
                    $total_enrolled = array_sum(array_column($data['utilization'], 'enrolled_students'));
                    $avg_utilization = ($total_enrolled / max($total_capacity, 1)) * 100;
                    $sections_above_90 = count(array_filter($data['utilization'], fn($s) => $s['utilization_rate'] >= 90));
                    $sections_below_50 = count(array_filter($data['utilization'], fn($s) => $s['utilization_rate'] < 50));
                    ?>
                    <div class="col-md-4">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-grid-3x3 fs-4" style="color: var(--primary);"></i>
                            <div>
                                <small class="text-muted d-block">Overall Utilization</small>
                                <span class="fw-bold fs-5"><?= number_format($avg_utilization, 1) ?>%</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-check-circle-fill fs-4 text-success"></i>
                            <div>
                                <small class="text-muted d-block">Sections ≥ 90%</small>
                                <span class="fw-bold fs-5"><?= number_format($sections_above_90) ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-exclamation-triangle-fill fs-4 text-warning"></i>
                            <div>
                                <small class="text-muted d-block">Sections &lt; 50%</small>
                                <span class="fw-bold fs-5"><?= number_format($sections_below_50) ?></span>
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
                <p class="empty-state-text">no section utilization data available</p>
            </div>
        <?php endif; ?>
    </div>
</div>