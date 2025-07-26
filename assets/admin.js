jQuery(document).ready(function($) {
    // Sync Now button functionality
    $('#sync-now-btn').on('click', function() {
        var $btn = $(this);
        var $overlay = $('#loading-overlay');
        
        // Show loading state
        $btn.addClass('syncing').prop('disabled', true);
        $overlay.show();
        
        // Perform AJAX sync
        $.ajax({
            url: linkInspectorAjax.ajax_url,
            type: 'POST',
            data: {
                action: 'link_inspector_sync',
                nonce: linkInspectorAjax.nonce
            },
            timeout: 60000, // 60 seconds timeout
            success: function(response) {
                if (response.success) {
                    // Reload the page to show updated data
                    location.reload();
                } else {
                    alert('Sync failed: ' + (response.data || 'Unknown error'));
                }
            },
            error: function() {
                alert('Sync failed. This may take a while for large sites. Please check your connection and try again.');
            },
            complete: function() {
                $btn.removeClass('syncing').prop('disabled', false);
                $overlay.hide();
            }
        });
    });
    
    // Export CSV functionality
    $('#export-csv-btn').on('click', function() {
        $.ajax({
            url: linkInspectorAjax.ajax_url,
            type: 'POST',
            data: {
                action: 'link_inspector_export_csv',
                nonce: linkInspectorAjax.nonce
            },
            success: function(response) {
                if (response.success && response.data.csv_data) {
                    downloadCSV(response.data.csv_data, 'anchor-text-analysis.csv');
                } else {
                    alert('Export failed. Please try again.');
                }
            },
            error: function() {
                alert('Export failed. Please try again.');
            }
        });
    });
    
    // Anchor text filter functionality
    $('#anchor-filter').on('change', function() {
        var filterValue = $(this).val();
        var $rows = $('.anchor-row');
        
        $rows.show(); // Show all rows first
        
        if (filterValue === 'over-optimized') {
            $rows.each(function() {
                if ($(this).data('optimized') !== 'yes') {
                    $(this).hide();
                }
            });
        } else if (filterValue === 'under-used') {
            $rows.each(function() {
                if (parseInt($(this).data('frequency')) > 2) {
                    $(this).hide();
                }
            });
        }
    });
    
    // Edit button functionality for broken links
    $('.edit-btn').on('click', function() {
        var url = $(this).data('url');
        var newUrl = prompt('Enter the correct URL:', url);
        
        if (newUrl && newUrl !== url) {
            // Here you would implement the URL replacement functionality
            alert('URL replacement functionality would be implemented here.');
        }
    });
    
    // Add Link button functionality for orphan pages
    $('.add-link-btn').on('click', function() {
        var url = $(this).data('url');
        var linkText = prompt('Enter link text for this page:');
        
        if (linkText) {
            // Here you would implement the add link functionality
            alert('Add link functionality would be implemented here.');
        }
    });
    
    // View anchor text details
    $('.view-link').on('click', function(e) {
        e.preventDefault();
        var anchor = $(this).data('anchor');
        
        // Here you would show a modal or expand details
        alert('Showing pages that use anchor text: "' + anchor + '"');
    });
    
    // SEO Health Report functionality
    $('#download-pdf-btn').on('click', function() {
        // Generate PDF report
        alert('PDF report generation would be implemented here.');
    });
    
    $('#generate-now-btn').on('click', function() {
        // Generate report now
        alert('Generating SEO health report...');
    });
    
    $('#schedule-weekly-report').on('change', function() {
        var isChecked = $(this).is(':checked');
        if (isChecked) {
            alert('Weekly reports will be sent to your email.');
        } else {
            alert('Weekly reports disabled.');
        }
    });
    
    // Content Inventory functionality
    $('#content-search').on('input', function() {
        var searchTerm = $(this).val().toLowerCase();
        $('.content-row').each(function() {
            var title = $(this).find('.content-title').text().toLowerCase();
            var url = $(this).find('.content-url').text().toLowerCase();
            
            if (title.includes(searchTerm) || url.includes(searchTerm)) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    });
    
    $('#content-filter').on('change', function() {
        var filterValue = $(this).val();
        var $rows = $('.content-row');
        
        $rows.show();
        
        if (filterValue === 'post') {
            $rows.each(function() {
                if ($(this).data('type') !== 'post') {
                    $(this).hide();
                }
            });
        } else if (filterValue === 'page') {
            $rows.each(function() {
                if ($(this).data('type') !== 'page') {
                    $(this).hide();
                }
            });
        } else if (filterValue === 'orphan') {
            $rows.each(function() {
                if ($(this).data('orphan') !== 'yes') {
                    $(this).hide();
                }
            });
        } else if (filterValue === 'low-links') {
            $rows.each(function() {
                var linkCount = parseInt($(this).find('.internal-links').text());
                if (linkCount >= 2) {
                    $(this).hide();
                }
            });
        }
    });
    
    $('#export-inventory-csv').on('click', function() {
        // Export content inventory to CSV
        alert('Content inventory CSV export would be implemented here.');
    });
    
    // Redirect Manager functionality
    $('#add-redirect-btn').on('click', function() {
        var oldUrl = $('#old-page-url').val();
        var newUrl = $('#new-page-url').val();
        var redirectType = $('#redirect-type').val();
        
        if (!oldUrl || !newUrl) {
            alert('Please fill in both URL fields.');
            return;
        }
        
        // Here you would implement the add redirect functionality
        alert('Redirect added: ' + oldUrl + ' -> ' + newUrl + ' (' + redirectType + ')');
        
        // Clear form
        $('#old-page-url, #new-page-url').val('');
    });
    
    $('#redirect-search').on('input', function() {
        var searchTerm = $(this).val().toLowerCase();
        $('.redirect-row').each(function() {
            var fromUrl = $(this).find('.from-url').text().toLowerCase();
            var toUrl = $(this).find('.to-url').text().toLowerCase();
            
            if (fromUrl.includes(searchTerm) || toUrl.includes(searchTerm)) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    });
    
    $('#redirect-type-filter').on('change', function() {
        var filterValue = $(this).val();
        var $rows = $('.redirect-row');
        
        $rows.show();
        
        if (filterValue !== 'all') {
            $rows.each(function() {
                if ($(this).data('type') !== filterValue) {
                    $(this).hide();
                }
            });
        }
    });
    
    $('.edit-redirect').on('click', function() {
        var redirectId = $(this).data('id');
        alert('Edit redirect functionality would be implemented here for ID: ' + redirectId);
    });
    
    $('.delete-redirect').on('click', function() {
        var redirectId = $(this).data('id');
        if (confirm('Are you sure you want to delete this redirect?')) {
            alert('Delete redirect functionality would be implemented here for ID: ' + redirectId);
        }
    });
    
    $('#auto-redirect-broken').on('change', function() {
        var isChecked = $(this).is(':checked');
        if (isChecked) {
            alert('Auto-redirect for broken internal links enabled.');
        } else {
            alert('Auto-redirect for broken internal links disabled.');
        }
    });
    
    $('#export-logs-csv, #export-logs-json').on('click', function() {
        var format = $(this).attr('id').includes('csv') ? 'CSV' : 'JSON';
        alert('Export redirect logs as ' + format + ' would be implemented here.');
    });
    
    // Helper function to download CSV
    function downloadCSV(csvData, filename) {
        var csvContent = csvData.map(function(row) {
            return row.map(function(field) {
                // Escape double quotes and wrap in quotes if needed
                if (typeof field === 'string' && (field.includes(',') || field.includes('"') || field.includes('\n'))) {
                    return '"' + field.replace(/"/g, '""') + '"';
                }
                return field;
            }).join(',');
        }).join('\n');
        
        var blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
        
        if (navigator.msSaveBlob) { // IE 10+
            navigator.msSaveBlob(blob, filename);
        } else {
            var link = document.createElement('a');
            if (link.download !== undefined) {
                var url = URL.createObjectURL(blob);
                link.setAttribute('href', url);
                link.setAttribute('download', filename);
                link.style.visibility = 'hidden';
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            }
        }
    }
    
    // Add smooth scrolling to anchor links
    $('a[href^="#"]').on('click', function(e) {
        e.preventDefault();
        var target = $(this.getAttribute('href'));
        if (target.length) {
            $('html, body').animate({
                scrollTop: target.offset().top - 100
            }, 500);
        }
    });
    
    // Auto-refresh stats every 30 seconds (optional)
    if (typeof linkInspectorAutoRefresh !== 'undefined' && linkInspectorAutoRefresh) {
        setInterval(function() {
            // Refresh only the stats, not the full page
            updateStats();
        }, 30000);
    }
    
    function updateStats() {
        $.ajax({
            url: linkInspectorAjax.ajax_url,
            type: 'POST',
            data: {
                action: 'link_inspector_get_stats',
                nonce: linkInspectorAjax.nonce
            },
            success: function(response) {
                if (response.success && response.data) {
                    $('.internal-card .stat-number').text(response.data.internal);
                    $('.external-card .stat-number').text(response.data.external);
                    $('.broken-card .stat-number').text(response.data.broken);
                    $('.orphan-card .stat-number').text(response.data.orphan);
                }
            }
        });
    }
    
    // Handle table row hover effects
    $('.links-table tbody tr').hover(
        function() {
            $(this).addClass('hover-effect');
        },
        function() {
            $(this).removeClass('hover-effect');
        }
    );
    
    // Add confirmation for destructive actions
    $('.delete-btn, .remove-btn').on('click', function(e) {
        if (!confirm('Are you sure you want to perform this action?')) {
            e.preventDefault();
            return false;
        }
    });
    
    // Initialize tooltips if available
    if (typeof $().tooltip === 'function') {
        $('[data-toggle="tooltip"]').tooltip();
    }
    
    // Handle responsive table scrolling
    $('.links-table-container').on('scroll', function() {
        var scrollLeft = $(this).scrollLeft();
        $(this).find('thead th:first-child').css('transform', 'translateX(' + scrollLeft + 'px)');
    });
    
    // Add loading states to buttons
    $('button').on('click', function() {
        var $btn = $(this);
        if (!$btn.hasClass('no-loading')) {
            $btn.addClass('loading');
            setTimeout(function() {
                $btn.removeClass('loading');
            }, 2000);
        }
    });
});

// Additional utility functions
function showNotification(message, type) {
    type = type || 'success';
    var notification = jQuery('<div class="notification ' + type + '">' + message + '</div>');
    jQuery('body').append(notification);
    
    setTimeout(function() {
        notification.fadeOut(function() {
            notification.remove();
        });
    }, 3000);
}

function formatDate(dateString) {
    var date = new Date(dateString);
    var options = { year: 'numeric', month: 'short', day: 'numeric' };
    return date.toLocaleDateString('en-US', options);
}

function formatNumber(num) {
    return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',');
}