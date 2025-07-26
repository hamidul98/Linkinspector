<?php
$plugin = new LinkInspectorPlugin();
$external_links = $plugin->get_external_links();
?>

<div class="links-table-container">
    <table class="links-table">
        <thead>
            <tr>
                <th>External URL</th>
                <th>Used In Pages</th>
                <th>Anchor Text</th>
                <th>Status Code</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($external_links)): ?>
                <tr>
                    <td colspan="5" class="no-data">No external links found. Click "Sync Now" to scan your website.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($external_links as $link): ?>
                    <tr>
                        <td class="link-url">
                            <a href="<?php echo esc_url($link->external_url); ?>" target="_blank">
                                <?php echo esc_html($link->external_url); ?>
                            </a>
                        </td>
                        <td class="used-pages"><?php echo esc_html($link->used_in_pages); ?></td>
                        <td class="anchor-text">"<?php echo esc_html($link->anchor_text); ?>"</td>
                        <td class="status-code"><?php echo esc_html($link->status_code); ?></td>
                        <td class="status">
                            <span class="status-active">✓</span>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>