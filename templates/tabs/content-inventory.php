<?php
$plugin = new LinkInspectorPlugin();
$content_inventory = $plugin->get_content_inventory();
$total_pages = count($content_inventory);
?>

<div class="content-inventory-container">
    <h2>Content Inventory Overview</h2>
    <p class="inventory-description">Track and manage your site's entire content inventory — view key SEO data for each post/page at a glance.</p>
    
    <div class="inventory-controls">
        <div class="search-section">
            <input type="text" id="content-search" placeholder="Search by Title or URL" class="search-input">
        </div>
        
        <div class="filter-section">
            <select id="content-filter" class="filter-select">
                <option value="all">Filter by All</option>
                <option value="post">Posts Only</option>
                <option value="page">Pages Only</option>
                <option value="orphan">Orphan Pages</option>
                <option value="low-links">Low Internal Links</option>
            </select>
        </div>
        
        <div class="export-section">
            <button id="export-inventory-csv" class="export-btn">
                📊 Export CSV
            </button>
        </div>
    </div>
    
    <div class="inventory-table-container">
        <table class="inventory-table">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>URL</th>
                    <th>Published On</th>
                    <th>Last Updated</th>
                    <th>Word Count</th>
                    <th>Internal Links</th>
                    <th>Orphan?</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($content_inventory)): ?>
                    <tr>
                        <td colspan="7" class="no-data">No content found. Click "Sync Now" to scan your website.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($content_inventory as $content): ?>
                        <tr class="content-row" data-type="<?php echo esc_attr($content->post_type); ?>" data-orphan="<?php echo $content->is_orphan ? 'yes' : 'no'; ?>">
                            <td class="content-title">
                                <a href="<?php echo esc_url(get_edit_post_link($content->ID)); ?>" target="_blank">
                                    <?php echo esc_html($content->post_title); ?>
                                </a>
                            </td>
                            <td class="content-url">
                                <a href="<?php echo esc_url($content->url); ?>" target="_blank">
                                    <?php echo esc_html($content->url_display); ?>
                                </a>
                            </td>
                            <td class="published-date"><?php echo esc_html(date('M j, Y', strtotime($content->post_date))); ?></td>
                            <td class="updated-date"><?php echo esc_html(date('M j, Y', strtotime($content->post_modified))); ?></td>
                            <td class="word-count"><?php echo esc_html(number_format($content->word_count)); ?></td>
                            <td class="internal-links"><?php echo esc_html($content->internal_links); ?></td>
                            <td class="orphan-status">
                                <?php if ($content->is_orphan): ?>
                                    <span class="status-orphan">❌ Yes</span>
                                <?php else: ?>
                                    <span class="status-linked">✅ No</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    
    <div class="pagination-section">
        <p class="showing-text">Showing 1-50 of <?php echo $total_pages; ?> pages</p>
        <div class="pagination-controls">
            <button class="pagination-btn" id="prev-page">Prev</button>
            <button class="pagination-btn" id="next-page">Next</button>
        </div>
    </div>
</div>