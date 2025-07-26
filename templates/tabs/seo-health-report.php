<?php
$plugin = new LinkInspectorPlugin();
$stats = $plugin->get_stats();
$broken_links = $plugin->get_broken_links();
$orphan_pages = $plugin->get_orphan_pages();

// Calculate low linked pages (pages with less than 2 internal links)
$low_linked_count = $plugin->get_low_linked_pages_count();
?>

<div class="seo-health-report-container">
    <h2>WEEKLY SEO HEALTH REPORT</h2>
    <p class="report-description">Get a summary of your site's SEO health including broken links, orphan pages, and low-link posts.</p>
    
    <div class="health-stats-grid">
        <div class="health-stat-card broken-links-card">
            <div class="stat-icon">🔗</div>
            <div class="stat-content">
                <div class="stat-label">BROKEN LINKS</div>
                <div class="stat-value"><?php echo esc_html($stats['broken']); ?> found</div>
            </div>
        </div>
        
        <div class="health-stat-card orphan-pages-card">
            <div class="stat-icon">⚪</div>
            <div class="stat-content">
                <div class="stat-label">ORPHAN PAGES</div>
                <div class="stat-value"><?php echo esc_html($stats['orphan']); ?> orphaned</div>
            </div>
        </div>
        
        <div class="health-stat-card low-linked-card">
            <div class="stat-icon">📊</div>
            <div class="stat-content">
                <div class="stat-label">LOW LINKED PAGES</div>
                <div class="stat-value"><?php echo esc_html($low_linked_count); ?> posts below 2 links</div>
            </div>
        </div>
    </div>
    
    <div class="report-actions">
        <div class="schedule-section">
            <label class="toggle-container">
                <span>Schedule weekly report to email</span>
                <input type="checkbox" id="schedule-weekly-report">
                <span class="toggle-slider"></span>
            </label>
        </div>
        
        <div class="report-buttons">
            <button id="download-pdf-btn" class="report-btn primary">
                📄 Download PDF
            </button>
            <button id="generate-now-btn" class="report-btn secondary">
                ⚡ Generate Now
            </button>
        </div>
    </div>
    
    <div class="report-footer">
        <p class="last-generated">Last Generated: July 29, 2025 - Delivered to admin@yourdomain.com</p>
    </div>
</div>