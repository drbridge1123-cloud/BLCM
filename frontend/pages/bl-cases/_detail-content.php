<div x-data="caseDetailPage()" x-init="init()">

    <!-- Loading -->
    <template x-if="loading">
        <div class="flex items-center justify-center py-20">
            <div class="spinner"></div>
        </div>
    </template>

    <template x-if="!loading && caseData">
        <div>
            <?php include __DIR__ . '/_detail-header.php'; ?>

            <!-- C1 Main Card — all accordion sections inside one card -->
            <div class="c1-card">
                <?php include __DIR__ . '/_detail-providers.php'; ?>
                <?php include __DIR__ . '/_detail-health-ledger.php'; ?>
                <?php include __DIR__ . '/_detail-activity.php'; ?>
                <?php include __DIR__ . '/_detail-documents.php'; ?>

                <!-- Workflow Section Divider -->
                <div class="c1-workflow-divider">
                    <span class="c1-workflow-label">WORKFLOW</span>
                </div>

                <?php include __DIR__ . '/_detail-costs.php'; ?>
                <?php include __DIR__ . '/_detail-mbr.php'; ?>
                <?php include __DIR__ . '/_detail-negotiate.php'; ?>
                <?php include __DIR__ . '/_detail-disbursement.php'; ?>
            </div>
        </div>
    </template>

    <?php include __DIR__ . '/_detail-modals.php'; ?>
</div>

<script src="/CMCdemo/frontend/assets/js/pages/bl-cases/mbr-panel.js?v=<?= filemtime(__DIR__ . '/../../assets/js/pages/bl-cases/mbr-panel.js') ?>"></script>
<script src="/CMCdemo/frontend/assets/js/pages/bl-cases/negotiate-panel.js?v=<?= filemtime(__DIR__ . '/../../assets/js/pages/bl-cases/negotiate-panel.js') ?>"></script>
<script src="/CMCdemo/frontend/assets/js/pages/bl-cases/disbursement-panel.js?v=<?= filemtime(__DIR__ . '/../../assets/js/pages/bl-cases/disbursement-panel.js') ?>"></script>
<script src="/CMCdemo/frontend/assets/js/pages/bl-cases/health-ledger-panel.js?v=<?= filemtime(__DIR__ . '/../../assets/js/pages/bl-cases/health-ledger-panel.js') ?>"></script>
<script src="/CMCdemo/frontend/components/template-selector.js?v=<?= filemtime(__DIR__ . '/../../components/template-selector.js') ?>"></script>
<script src="/CMCdemo/frontend/components/document-uploader.js?v=<?= filemtime(__DIR__ . '/../../components/document-uploader.js') ?>"></script>
<script src="/CMCdemo/frontend/components/document-selector.js?v=<?= filemtime(__DIR__ . '/../../components/document-selector.js') ?>"></script>
