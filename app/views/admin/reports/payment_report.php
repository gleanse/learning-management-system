<div class="card report-card">
    <div class="card-header">
        <h5 class="mb-0">
            <i class="bi bi-cash-coin"></i>
            Payment Report - <?= ucfirst($interval) ?>
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
                    <span class="snapshot-label">Transactions</span>
                    <span class="snapshot-value"><?= number_format($data['today']['transaction_count']) ?></span>
                </div>
                <div class="snapshot-item">
                    <span class="snapshot-label">Payments Processed</span>
                    <span class="snapshot-value"><?= number_format($data['today']['payment_count']) ?></span>
                </div>
                <div class="snapshot-item">
                    <span class="snapshot-label">Total Collected</span>
                    <span class="snapshot-value">₱<?= number_format($data['today']['total_collected'] ?? 0, 2) ?></span>
                </div>
                <div class="snapshot-item">
                    <span class="snapshot-label">Average Payment</span>
                    <span class="snapshot-value">₱<?= number_format($data['today']['average_payment'] ?? 0, 2) ?></span>
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
                    <span class="snapshot-label">Transactions</span>
                    <span class="snapshot-value"><?= number_format($data['this_week']['transaction_count']) ?></span>
                </div>
                <div class="snapshot-item">
                    <span class="snapshot-label">Total Collected</span>
                    <span class="snapshot-value">₱<?= number_format($data['this_week']['total_collected'] ?? 0, 2) ?></span>
                </div>
                <div class="snapshot-item">
                    <span class="snapshot-label">Fully Paid</span>
                    <span class="snapshot-value"><?= number_format($data['this_week']['fully_paid_count'] ?? 0) ?></span>
                </div>
                <div class="snapshot-item">
                    <span class="snapshot-label">Partial</span>
                    <span class="snapshot-value"><?= number_format($data['this_week']['partial_count'] ?? 0) ?></span>
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
                    <span class="snapshot-label">Total Transactions</span>
                    <span class="snapshot-value"><?= number_format($data['this_month']['total_transactions']) ?></span>
                </div>
                <div class="snapshot-item">
                    <span class="snapshot-label">Revenue</span>
                    <span class="snapshot-value">₱<?= number_format($data['this_month']['revenue'] ?? 0, 2) ?></span>
                </div>
                <div class="snapshot-item">
                    <span class="snapshot-label">Paying Students</span>
                    <span class="snapshot-value"><?= number_format($data['this_month']['paying_students'] ?? 0) ?></span>
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
                    <span class="snapshot-label">Total Transactions</span>
                    <span class="snapshot-value"><?= number_format($data['this_year']['total_transactions']) ?></span>
                </div>
                <div class="snapshot-item">
                    <span class="snapshot-label">Total Revenue</span>
                    <span class="snapshot-value">₱<?= number_format($data['this_year']['total_revenue'] ?? 0, 2) ?></span>
                </div>
                <div class="snapshot-item">
                    <span class="snapshot-label">Months with Payments</span>
                    <span class="snapshot-value"><?= $data['this_year']['months_with_payments'] ?></span>
                </div>
                <div class="snapshot-item">
                    <span class="snapshot-label">Avg Monthly Revenue</span>
                    <span class="snapshot-value">₱<?= number_format($data['this_year']['avg_monthly_revenue'] ?? 0, 2) ?></span>
                </div>
            </div>
        <?php endif; ?>

        <?php if (!empty($data['trends'])): ?>
            <div class="trends-section">
                <h6 class="trends-title">
                    <i class="bi bi-graph-up-arrow"></i>
                    Payment Trends
                </h6>
                <div class="table-responsive">
                    <table class="table trends-table">
                        <thead>
                            <tr>
                                <th>Period</th>
                                <th>Transactions</th>
                                <th>Total Collected</th>
                                <th>Payments Processed</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($data['trends'] as $trend): ?>
                                <tr>
                                    <td><?= htmlspecialchars($trend['period']) ?></td>
                                    <td><?= number_format($trend['transaction_count']) ?></td>
                                    <td>₱<?= number_format($trend['total_collected'] ?? 0, 2) ?></td>
                                    <td><?= number_format($trend['payments_processed'] ?? 0) ?></td>
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
                <p class="empty-state-text">no payment data available</p>
            </div>
        <?php endif; ?>
    </div>
</div>