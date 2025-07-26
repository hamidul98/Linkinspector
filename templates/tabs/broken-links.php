<?php
$plugin = new LinkInspectorPlugin();
$broken_links = $plugin->get_broken_links();
?>

<div class="links-table-container">
    <table class="links-table">
        <thead>
            <tr>
                <th>Broken URL</th>
                <th>Error Type</th>
                <th>Found In Pages</th>
                <th>Last Checked</th>
                <th>Fix</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($broken_links)): ?>
                <tr>
                    <td colspan="5" class="no-data">No broken links found. Click "Sync Now" to scan your website.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($broken_links as $link): ?>
                    <tr>
                        <td class="link-url">
                            <a href="<?php echo esc_url($link->broken_url); ?>" target="_blank">
                                <?php echo esc_html($link->broken_url); ?>
                            </a>
                        </td>
                        <td class="error-type"><?php echo esc_html($link->error_type); ?></td>
                        <td class="found-pages"><?php echo esc_html($link->found_in_pages); ?></td>
                        <td class="last-checked"><?php echo esc_html(date('M j, Y', strtotime($link->last_checked))); ?></td>
                        <td class="fix-action">
                            <button class="edit-btn" data-url="<?php echo esc_attr($link->broken_url); ?>">
                                ✏️ Edit
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>