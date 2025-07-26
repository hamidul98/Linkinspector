<?php
if (!defined('ABSPATH')) {
    exit;
}

$plugin = new LinkInspectorPlugin();
$stats = $plugin->get_stats();
$active_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'internal';
?>

<div class="wrap link-inspector-dashboard">
    <div class="dashboard-header">
        <h1>Link Inspector Dashboard</h1>
        <button id="sync-now-btn" class="sync-button">
            <span class="sync-icon">🔄</span>
            Sync Now
        </button>
    </div>

    <div class="stats-cards">
        <div class="stat-card internal-card">
            <div class="stat-label">Internal Links</div>
            <div class="stat-number"><?php echo esc_html($stats['internal']); ?></div>
        </div>
        <div class="stat-card external-card">
            <div class="stat-label">External Links</div>
            <div class="stat-number"><?php echo esc_html($stats['external']); ?></div>
        </div>
        <div class="stat-card broken-card">
            <div class="stat-label">Broken Links</div>
            <div class="stat-number"><?php echo esc_html($stats['broken']); ?></div>
        </div>
        <div class="stat-card orphan-card">
            <div class="stat-label">Orphan Pages</div>
            <div class="stat-number"><?php echo esc_html($stats['orphan']); ?></div>
        </div>
    </div>

    <nav class="nav-tab-wrapper">
        <a href="?page=link-inspector&tab=internal" class="nav-tab <?php echo $active_tab === 'internal' ? 'nav-tab-active' : ''; ?>">Internal Links</a>
        <a href="?page=link-inspector&tab=external" class="nav-tab <?php echo $active_tab === 'external' ? 'nav-tab-active' : ''; ?>">External Links</a>
        <a href="?page=link-inspector&tab=broken" class="nav-tab <?php echo $active_tab === 'broken' ? 'nav-tab-active' : ''; ?>">Broken Links</a>
        <a href="?page=link-inspector&tab=orphan" class="nav-tab <?php echo $active_tab === 'orphan' ? 'nav-tab-active' : ''; ?>">Orphan Pages</a>
        <a href="?page=link-inspector&tab=anchor" class="nav-tab <?php echo $active_tab === 'anchor' ? 'nav-tab-active' : ''; ?>">Anchor Text Analysis</a>
    </nav>

    <div class="secondary-nav">
        <a href="?page=link-inspector&tab=seo-health" class="secondary-nav-btn <?php echo $active_tab === 'seo-health' ? 'active' : ''; ?>">Weekly SEO Health Report</a>
        <a href="?page=link-inspector&tab=content-inventory" class="secondary-nav-btn <?php echo $active_tab === 'content-inventory' ? 'active' : ''; ?>">CONTENT INVENTORY</a>
        <a href="?page=link-inspector&tab=redirect-manager" class="secondary-nav-btn <?php echo $active_tab === 'redirect-manager' ? 'active' : ''; ?>">REDIRECT MANAGER</a>
    </div>

    <div class="tab-content">
        <?php if ($active_tab === 'internal'): ?>
            <?php include 'tabs/internal-links.php'; ?>
        <?php elseif ($active_tab === 'external'): ?>
            <?php include 'tabs/external-links.php'; ?>
        <?php elseif ($active_tab === 'broken'): ?>
            <?php include 'tabs/broken-links.php'; ?>
        <?php elseif ($active_tab === 'orphan'): ?>
            <?php include 'tabs/orphan-pages.php'; ?>
        <?php elseif ($active_tab === 'anchor'): ?>
            <?php include 'tabs/anchor-analysis.php'; ?>
        <?php elseif ($active_tab === 'seo-health'): ?>
            <?php include 'tabs/seo-health-report.php'; ?>
        <?php elseif ($active_tab === 'content-inventory'): ?>
            <?php include 'tabs/content-inventory.php'; ?>
        <?php elseif ($active_tab === 'redirect-manager'): ?>
            <?php include 'tabs/redirect-manager.php'; ?>
        <?php endif; ?>
    </div>
</div>

<div id="loading-overlay" class="loading-overlay" style="display: none;">
    <div class="loading-content">
        <div class="spinner"></div>
        <p>Syncing links... Please wait.</p>
    </div>
</div>