<div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
    <div>
        <h2 class="page-title"><?= __t('common.dashboard') ?></h2>
        <p class="text-muted small mb-0"><?= __t('app.tagline') ?></p>
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-sm-6 col-xl-3">
        <div class="card kpi-card">
            <div class="kpi-icon bg-soft-primary"><i class="bi bi-buildings"></i></div>
            <div><div class="kpi-value"><?= number_format($kpi['orgs'], 0, ',', '.') ?></div><div class="kpi-label"><?= __t('dash.total_orgs') ?></div></div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card kpi-card">
            <div class="kpi-icon bg-soft-success"><i class="bi bi-check2-circle"></i></div>
            <div><div class="kpi-value"><?= $kpi['orgs_reported'] ?> / <span class="text-danger"><?= $kpi['orgs_not_reported'] ?></span></div>
            <div class="kpi-label"><?= __t('dash.orgs_reported') ?> / <?= __t('dash.orgs_not_reported') ?></div></div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card kpi-card">
            <div class="kpi-icon bg-soft-gold"><i class="bi bi-kanban"></i></div>
            <div><div class="kpi-value"><?= number_format($kpi['programs'], 0, ',', '.') ?></div><div class="kpi-label"><?= __t('dash.total_programs') ?> (<?= $kpi['programs_unfunded'] ?> <?= strtolower(__t('dash.programs_unfunded')) ?>)</div></div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card kpi-card">
            <div class="kpi-icon bg-soft-danger"><i class="bi bi-hourglass-split"></i></div>
            <div><div class="kpi-value"><?= $kpi['pending_reports'] ?></div><div class="kpi-label"><?= __t('dash.pending_verification') ?></div></div>
        </div>
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-sm-6 col-xl-3">
        <div class="card kpi-card">
            <div class="kpi-icon bg-soft-primary"><i class="bi bi-wallet2"></i></div>
            <div><div class="kpi-value"><?= format_rupiah($kpi['total_needed']) ?></div><div class="kpi-label"><?= __t('dash.total_needed') ?></div></div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card kpi-card">
            <div class="kpi-icon bg-soft-info"><i class="bi bi-hand-thumbs-up"></i></div>
            <div><div class="kpi-value"><?= format_rupiah($kpi['total_commitment']) ?></div><div class="kpi-label"><?= __t('dash.total_commitment') ?></div></div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card kpi-card">
            <div class="kpi-icon bg-soft-success"><i class="bi bi-cash-coin"></i></div>
            <div><div class="kpi-value"><?= format_rupiah($kpi['total_realization']) ?></div><div class="kpi-label"><?= __t('dash.total_realization') ?> (<?= $kpi['realization_pct'] ?>%)</div></div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card kpi-card">
            <div class="kpi-icon bg-soft-gold"><i class="bi bi-people"></i></div>
            <div><div class="kpi-value"><?= number_format($kpi['beneficiaries'], 0, ',', '.') ?></div><div class="kpi-label"><?= __t('dash.beneficiaries') ?></div></div>
        </div>
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-lg-7">
        <div class="card h-100">
            <div class="card-header"><?= __t('dash.chart_commitment_realization') ?></div>
            <div class="card-body"><canvas id="chartMonthly" height="240"></canvas></div>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="card h-100">
            <div class="card-header"><?= __t('dash.chart_program_status') ?></div>
            <div class="card-body"><canvas id="chartStatus" height="240"></canvas></div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-4">
        <div class="card h-100">
            <div class="card-header"><?= __t('dash.latest_reports') ?></div>
            <div class="list-group list-group-flush">
                <?php if ($latestReports === []): ?>
                    <div class="list-group-item text-muted small"><?= __t('common.none') ?></div>
                <?php else: foreach ($latestReports as $r): ?>
                    <a class="list-group-item list-group-item-action d-flex justify-content-between align-items-center" href="/laporan/<?= $r['id'] ?>">
                        <span class="small"><strong><?= e($r['org_name']) ?></strong><br><span class="text-muted"><?= e($r['number']) ?></span></span>
                        <?= status_badge($r['status']) ?>
                    </a>
                <?php endforeach; endif; ?>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card h-100">
            <div class="card-header"><?= __t('dash.urgent_programs') ?></div>
            <div class="list-group list-group-flush">
                <?php if ($urgentPrograms === []): ?>
                    <div class="list-group-item text-muted small"><?= __t('common.none') ?></div>
                <?php else: foreach ($urgentPrograms as $p): $gap = max(0, (float) $p['budget_needed'] - (float) $p['committed']); ?>
                    <a class="list-group-item list-group-item-action" href="/program/<?= $p['id'] ?>">
                        <div class="d-flex justify-content-between">
                            <span class="small fw-semibold"><?= e($p['name']) ?></span>
                            <span class="badge text-bg-<?= $p['priority_level'] === 'mendesak' ? 'danger' : 'warning' ?>"><?= __t('priority.' . $p['priority_level']) ?></span>
                        </div>
                        <small class="text-muted"><?= __t('program.funding_gap') ?>: <?= format_rupiah($gap) ?></small>
                    </a>
                <?php endforeach; endif; ?>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card h-100">
            <div class="card-header text-danger"><?= __t('org.not_reported') ?></div>
            <div class="list-group list-group-flush">
                <?php if ($notReported === []): ?>
                    <div class="list-group-item text-muted small"><?= __t('common.none') ?></div>
                <?php else: foreach ($notReported as $o): ?>
                    <a class="list-group-item list-group-item-action small" href="/organisasi/<?= $o['id'] ?>">
                        <strong><?= e($o['name']) ?></strong><br>
                        <span class="text-muted"><i class="bi bi-person me-1"></i><?= e($o['pic_name'] ?? '-') ?> · <i class="bi bi-telephone me-1"></i><?= e($o['phone'] ?? '-') ?></span>
                    </a>
                <?php endforeach; endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
$(function () {
    fetch('/dashboard/charts').then(r => r.json()).then(data => {
        const months = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
        new Chart(document.getElementById('chartMonthly'), {
            type: 'bar',
            data: {
                labels: data.monthly.map(m => months[m.month - 1]),
                datasets: [
                    { label: '<?= __t('dash.total_commitment') ?>', data: data.monthly.map(m => m.commitment), backgroundColor: '#16407e' },
                    { label: '<?= __t('dash.total_realization') ?>', data: data.monthly.map(m => m.realization), backgroundColor: '#2eaa63' }
                ]
            },
            options: {
                responsive: true,
                scales: { y: { ticks: { callback: v => 'Rp ' + Number(v).toLocaleString('id-ID') } } }
            }
        });
        new Chart(document.getElementById('chartStatus'), {
            type: 'doughnut',
            data: {
                labels: data.byStatus.map(s => s.label),
                datasets: [{
                    data: data.byStatus.map(s => s.total),
                    backgroundColor: ['#16407e','#2eaa63','#c9a227','#dc3545','#0dcaf0','#6c757d','#8e44ad','#e67e22']
                }]
            },
            options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
        });
    });
});
</script>
