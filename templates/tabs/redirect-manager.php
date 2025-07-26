<?php
$plugin = new LinkInspectorPlugin();
$redirects = $plugin->get_redirects();
$redirect_count = count($redirects);
?>

<div class="redirect-manager-container">
    <h2>Redirect Broken or Expired Links</h2>
    <p class="redirect-description">Create and manage 301/302 redirects directly from your dashboard. Track broken links and fix them in one click.</p>
    
    <div class="redirect-form-section">
        <div class="redirect-form">
            <input type="text" id="old-page-url" placeholder="/old-page-url" class="redirect-input">
            <input type="text" id="new-page-url" placeholder="/new-page-url" class="redirect-input">
            <select id="redirect-type" class="redirect-select">
                <option value="301">301 Permanent</option>
                <option value="302">302 Temporary</option>
            </select>
            <button id="add-redirect-btn" class="add-redirect-btn">Add Redirect</button>
        </div>
    </div>
    
    <div class="redirect-controls">
        <div class="search-filter-section">
            <input type="text" id="redirect-search" placeholder="Filter by From URL or Status" class="search-input">
        </div>
        
        <div class="filter-controls">
            <select id="redirect-type-filter" class="filter-select">
                <option value="all">Filter by Type (All)</option>
                <option value="301">301 Redirects</option>
                <option value="302">302 Redirects</option>
            </select>
            
            <select id="redirect-sort" class="filter-select">
                <option value="created">Sort by Created On</option>
                <option value="hits">Sort by Hit Count</option>
                <option value="status">Sort by Status</option>
            </select>
        </div>
    </div>
    
    <div class="redirects-table-container">
        <table class="redirects-table">
            <thead>
                <tr>
                    <th>From URL</th>
                    <th>To URL</th>
                    <th>Type</th>
                    <th>Created On</th>
                    <th>Hit Count</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($redirects)): ?>
                    <tr>
                        <td colspan="7" class="no-data">No redirects found. Add your first redirect above.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($redirects as $redirect): ?>
                        <tr class="redirect-row" data-type="<?php echo esc_attr($redirect->redirect_type); ?>">
                            <td class="from-url"><?php echo esc_html($redirect->from_url); ?></td>
                            <td class="to-url">
                                <a href="<?php echo esc_url($redirect->to_url); ?>" target="_blank">
                                    <?php echo esc_html($redirect->to_url); ?>
                                </a>
                            </td>
                            <td class="redirect-type">
                                <span class="type-badge type-<?php echo esc_attr($redirect->redirect_type); ?>">
                                    <?php echo esc_html($redirect->redirect_type); ?>
                                </span>
                            </td>
                            <td class="created-date"><?php echo esc_html(date('M j, Y', strtotime($redirect->created_on))); ?></td>
                            <td class="hit-count"><?php echo esc_html($redirect->hit_count); ?></td>
                            <td class="status">
                                <?php if ($redirect->status === 'active'): ?>
                                    <span class="status-active">✅ Active</span>
                                <?php else: ?>
                                    <span class="status-inactive">❌ Inactive</span>
                                <?php endif; ?>
                            </td>
                            <td class="actions">
                                <button class="action-btn edit-redirect" data-id="<?php echo esc_attr($redirect->id); ?>">✏️ Edit</button>
                                <button class="action-btn delete-redirect" data-id="<?php echo esc_attr($redirect->id); ?>">🗑️ Delete</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    
    <div class="pagination-section">
        <p class="showing-text">Showing 1-25 of <?php echo $redirect_count; ?> redirects</p>
        <div class="pagination-controls">
            <button class="pagination-btn" id="redirect-prev">Prev</button>
            <button class="pagination-btn" id="redirect-next">Next</button>
        </div>
    </div>
    
    <div class="advanced-options">
        <h3>Advanced Redirect Options</h3>
        <div class="advanced-controls">
            <label class="toggle-container">
                <span>Auto-create redirect for broken internal links</span>
                <input type="checkbox" id="auto-redirect-broken">
                <span class="toggle-slider"></span>
            </label>
            
            <div class="export-buttons">
                <button id="export-logs-csv" class="export-btn">Export Logs CSV</button>
                <button id="export-logs-json" class="export-btn">Export Logs JSON</button>
            </div>
        </div>
    </div>
</div>