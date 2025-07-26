<?php
$plugin = new LinkInspectorPlugin();
$orphan_pages = $plugin->get_orphan_pages();
?>

<div class="links-table-container">
    <table class="links-table">
        <thead>
            <tr>
                <th>Page Title</th>
                <th>URL</th>
                <th>Post Type</th>
                <th>Published On</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($orphan_pages)): ?>
                <tr>
                    <td colspan="5" class="no-data">No orphan pages found. Click "Sync Now" to scan your website.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($orphan_pages as $page): ?>
                    <tr>
                        <td class="page-title"><?php echo esc_html($page->page_title); ?></td>
                        <td class="page-url">
                            <a href="<?php echo esc_url(get_site_url() . $page->url); ?>" target="_blank">
                                <?php echo esc_html($page->url); ?>
                            </a>
                        </td>
                        <td class="post-type"><?php echo esc_html($page->post_type); ?></td>
                        <td class="published-date"><?php echo esc_html(date('M j, Y', strtotime($page->published_on))); ?></td>
                        <td class="add-link-action">
                            <button class="add-link-btn" data-url="<?php echo esc_attr($page->url); ?>">
                                ➕ Add Link
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>