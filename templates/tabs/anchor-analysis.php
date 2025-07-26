<?php
$plugin = new LinkInspectorPlugin();
$anchor_data = $plugin->get_anchor_text_analysis();
$top_anchors = array_slice($anchor_data, 0, 5, true);
?>

<div class="anchor-analysis-container">
    <h2>Anchor Text Usage Breakdown</h2>
    
    <div class="filter-section">
        <label for="anchor-filter">Filter:</label>
        <select id="anchor-filter">
            <option value="all">All Anchors</option>
            <option value="over-optimized">Over-Optimized</option>
            <option value="under-used">Under-Used</option>
        </select>
    </div>

    <div class="anchor-table-container">
        <table class="anchor-table">
            <thead>
                <tr>
                    <th>Anchor Text</th>
                    <th>Pages Used In</th>
                    <th>Total Frequency</th>
                    <th>Over-Optimized?</th>
                    <th>Suggested Variations</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($anchor_data)): ?>
                    <tr>
                        <td colspan="5" class="no-data">No anchor text data found. Click "Sync Now" to analyze your website.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($anchor_data as $anchor => $data): ?>
                        <?php 
                        $pages_count = count(array_unique($data['pages']));
                        $frequency = $data['frequency'];
                        $is_over_optimized = $frequency > 5; // Simple rule: more than 5 uses is over-optimized
                        
                        $suggestions = array();
                        if ($is_over_optimized) {
                            $suggestions[] = strtolower($anchor) . ' here';
                            $suggestions[] = 'explore ' . strtolower($anchor);
                            $suggestions[] = 'discover more';
                        }
                        ?>
                        <tr class="anchor-row" data-frequency="<?php echo $frequency; ?>" data-optimized="<?php echo $is_over_optimized ? 'yes' : 'no'; ?>">
                            <td class="anchor-text">"<?php echo esc_html($anchor); ?>"</td>
                            <td class="pages-count">
                                <?php echo $pages_count; ?>
                                <a href="#" class="view-link" data-anchor="<?php echo esc_attr($anchor); ?>">(View)</a>
                            </td>
                            <td class="frequency"><?php echo $frequency; ?></td>
                            <td class="over-optimized">
                                <?php if ($is_over_optimized): ?>
                                    <span class="warning-icon">⚠️</span> Yes
                                <?php else: ?>
                                    No
                                <?php endif; ?>
                            </td>
                            <td class="suggestions">
                                <?php if (!empty($suggestions)): ?>
                                    <?php echo esc_html(implode(', ', $suggestions)); ?>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php if (!empty($top_anchors)): ?>
    <div class="top-anchors-section">
        <h3>Top 5 Most Used Anchor Texts</h3>
        <div class="chart-container">
            <?php 
            $max_frequency = max(array_column($top_anchors, 'frequency'));
            foreach ($top_anchors as $anchor => $data): 
                $percentage = ($data['frequency'] / $max_frequency) * 100;
            ?>
                <div class="chart-bar">
                    <span class="anchor-label">"<?php echo esc_html($anchor); ?>"</span>
                    <div class="bar-container">
                        <div class="bar" style="width: <?php echo $percentage; ?>%"></div>
                        <span class="frequency-label"><?php echo $data['frequency']; ?></span>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <div class="export-section">
        <button id="export-csv-btn" class="export-btn">Export CSV</button>
    </div>
</div>