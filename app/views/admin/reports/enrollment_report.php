<div class="card report-card">
    <div class="card-header">
        <h5 class="mb-0">
            <i class="bi bi-person-plus-fill"></i>
            Enrollment Report - <?= $interval ?>
        </h5>
    </div>
    <div class="card-body">
        <?php if ($interval == 'daily' && !empty($data['today'])): ?>
            <!-- daily snapshot -->
            <div class="snapshot-grid mb-4">
                <div class="snapshot-item">
                    <span class="snapshot-label">Date</span>
                    <span class="snapshot-value"><?= htmlspecialchars($data['today']['date']) ?></span>
                </div>
                <div class="snapshot-item">
                    <span class="snapshot-label">Total Enrollments</span>
                    <span class="snapshot-value"><?= number_format($data['today']['total_enrollments']) ?></span>
                </div>
                <div class="snapshot-item">
                    <span class="snapshot-label">Senior High</span>
                    <span class="snapshot-value"><?= number_format($data['today']['senior_high_count'] ?? 0) ?></span>
                </div>
                <div class="snapshot-item">
                    <span class="snapshot-label">College</span>
                    <span class="snapshot-value"><?= number_format($data['today']['college_count'] ?? 0) ?></span>
                </div>
                <div class="snapshot-item">
                    <span class="snapshot-label">Strands/Courses</span>
                    <span class="snapshot-value"><?= number_format($data['today']['strands_count'] ?? 0) ?></span>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($interval == 'weekly' && !empty($data['this_week'])): ?>
            <div class="snapshot-grid mb-4">
                <div class="snapshot-item">
                    <span class="snapshot-label">Week</span>
                    <span class="snapshot-value">Week <?= $data['this_week']['week_number'] ?>, <?= $data['this_week']['year'] ?></span>
                </div>
                <div class="snapshot-item">
                    <span class="snapshot-label">Enrollments</span>
                    <span class="snapshot-value"><?= number_format($data['this_week']['total_enrollments']) ?></span>
                </div>
                <div class="snapshot-item">
                    <span class="snapshot-label">Strands</span>
                    <span class="snapshot-value"><?= number_format($data['this_week']['strands_count']) ?></span>
                </div>
                <div class="snapshot-item">
                    <span class="snapshot-label">Active</span>
                    <span class="snapshot-value"><?= number_format($data['this_week']['active_count']) ?></span>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($interval == 'monthly' && !empty($data['this_month'])): ?>
            <div class="snapshot-grid mb-4">
                <div class="snapshot-item">
                    <span class="snapshot-label">Month</span>
                    <span class="snapshot-value"><?= date('F', mktime(0, 0, 0, $data['this_month']['month'], 1)) ?> <?= $data['this_month']['year'] ?></span>
                </div>
                <div class="snapshot-item">
                    <span class="snapshot-label">Enrollments</span>
                    <span class="snapshot-value"><?= number_format($data['this_month']['total_enrollments']) ?></span>
                </div>
                <div class="snapshot-item">
                    <span class="snapshot-label">Sections Used</span>
                    <span class="snapshot-value"><?= number_format($data['this_month']['sections_used']) ?></span>
                </div>
                <div class="snapshot-item">
                    <span class="snapshot-label">Senior High</span>
                    <span class="snapshot-value"><?= number_format($data['this_month']['senior_high'] ?? 0) ?></span>
                </div>
                <div class="snapshot-item">
                    <span class="snapshot-label">College</span>
                    <span class="snapshot-value"><?= number_format($data['this_month']['college'] ?? 0) ?></span>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($interval == 'yearly' && !empty($data['this_year'])): ?>
            <div class="snapshot-grid mb-4">
                <div class="snapshot-item">
                    <span class="snapshot-label">Year</span>
                    <span class="snapshot-value"><?= $data['this_year']['year'] ?></span>
                </div>
                <div class="snapshot-item">
                    <span class="snapshot-label">Total Enrollments</span>
                    <span class="snapshot-value"><?= number_format($data['this_year']['total_enrollments']) ?></span>
                </div>
                <div class="snapshot-item">
                    <span class="snapshot-label">Months Active</span>
                    <span class="snapshot-value"><?= $data['this_year']['months_active'] ?></span>
                </div>
                <div class="snapshot-item">
                    <span class="snapshot-label">Strands Offered</span>
                    <span class="snapshot-value"><?= $data['this_year']['strands_offered'] ?></span>
                </div>
                <div class="snapshot-item">
                    <span class="snapshot-label">Currently Active</span>
                    <span class="snapshot-value"><?= number_format($data['this_year']['currently_active']) ?></span>
                </div>
            </div>
        <?php endif; ?>

        <?php if (!empty($data['trends'])): ?>
            <div class="trends-section">
                <h6 class="trends-title">
                    <i class="bi bi-graph-up-arrow"></i>
                    Enrollment Trends
                </h6>
                <div class="table-responsive">
                    <table class="table trends-table">
                        <thead>
                            <tr>
                                <th>Period</th>
                                <th>Enrollments</th>
                                <th>Senior High</th>
                                <th>College</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($data['trends'] as $trend): ?>
                                <tr>
                                    <td><?= htmlspecialchars($trend['period']) ?></td>
                                    <td><?= number_format($trend['enrollment_count']) ?></td>
                                    <td><?= number_format($trend['senior_high'] ?? 0) ?></td>
                                    <td><?= number_format($trend['college'] ?? 0) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>

        <?php if (empty($data)): ?>
            <div class="empty-state py-5">
                <div class="empty-state-icon">
                    <i class="bi bi-inbox"></i>
                </div>
                <p class="empty-state-text">No enrollment data available</p>
            </div>
        <?php endif; ?>
    </div>
</div>