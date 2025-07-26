<?php
$plugin = new LinkInspectorPlugin();
$internal_links = $plugin->get_internal_links();
?>

<div class="links-table-container">
    <table class="links-table">
        <thead>
            <tr>
                <th>Link URL</th>
                <th>Used In Pages</th>
                <th>Anchor Text</th>
                <th>First Found</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($internal_links)): ?>
                <tr>
                    <td colspan="5" class="no-data">No internal links found. Click "Sync Now" to scan your website.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($internal_links as $link): ?>
                    <tr>
                        <td class="link-url">
                            <a href="<?php echo esc_url($link->link_url); ?>" target="_blank">
                                <?php echo esc_html($link->link_url); ?>
                            </a>
                        </td>
                        <td class="used-pages"><?php echo esc_html($link->used_in_pages); ?></td>
                        <td class="anchor-text">"<?php echo esc_html($link->anchor_text); ?>"</td>
                        <td class="first-found"><?php echo esc_html(date('M j, Y', strtotime($link->first_found))); ?></td>
                        <td class="status">
                            <span class="status-active">✓</span>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>