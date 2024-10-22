<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; 
}

add_shortcode('videograph', 'videograph_video_shortcode');
function videograph_video_shortcode($atts) {
    $atts = shortcode_atts(
        array(
            'content_id' => '',
        ),
        $atts
    );
    
    // Sanitize content_id attribute
    $content_id = sanitize_text_field($atts['content_id']);
    
    if (empty($content_id)) {
        return '';
    }
    
    $url = "https://dashboard.videograph.ai/videos/embed?videoId=" . esc_attr($content_id);
    
    ob_start();
    ?>
<div class="vg-video-iframe">
    <iframe loading="lazy" src="<?php echo esc_url($url); ?>" width="100%" height="100%" frameborder="0"
        allowfullscreen="true" style="max-width: 100%; max-height: 100%;"></iframe>
</div>
<?php
    
    return ob_get_clean();
}

// Library page
function videograph_video_library()
{   
    $access_token = get_option('videograph_access_token');
    $secret_key = get_option('videograph_secret_key');

    if (empty($access_token) || empty($secret_key)) {
        echo '<div class="vi-notice-error"><p>The API key is missing or invalid. Please go to the <a href="' . esc_url(admin_url('admin.php?page=videograph-settings')) . '">settings</a> page and update it with the correct one.</p></div>';
        return;
    }

    // Check if form submission (pagination or per_page change) occurred
    if (isset($_GET['vg_videos_per_page_nonce_field'])) {
        // Verify nonce when the form is submitted
        if (wp_verify_nonce(sanitize_text_field(wp_unslash($_GET['vg_videos_per_page_nonce_field'])), 'vg_videos_per_page_nonce')) {
            // Retrieve pagination and per_page values when nonce is valid
            $per_page = isset($_GET['per_page']) ? intval(wp_unslash($_GET['per_page'])) : 10;
            $current_page = isset($_GET['paged']) ? intval(wp_unslash($_GET['paged'])) : 1;
        } else {
            // Security check failed, display an error message
            $per_page = 10;
            $current_page = 1;
            echo '<div class="notice notice-error"><p>' . esc_html__('Security check failed. Please try again.', 'videograph') . '</p></div>';
        }
    } else {
        // No form submission (first page load), use default values
        $per_page = isset($_GET['per_page']) ? intval(wp_unslash($_GET['per_page'])) : 10;
        $current_page = isset($_GET['paged']) ? intval(wp_unslash($_GET['paged'])) : 1;
    }
    
    

    $start_index = ($current_page - 1) * $per_page;
    $videograph_search_query = isset($_GET['s']) ? sanitize_text_field(wp_unslash($_GET['s'])) : '';
    $api_url = 'https://api.videograph.ai/video/services/api/v1/contents';
    $params = array(
        'start' => $start_index,
        'limit' => $per_page,
    );

    $api_url = add_query_arg($params, $api_url);

    $response = wp_remote_get($api_url, array(
        'headers' => array(
            'Authorization' => 'Basic ' . base64_encode($access_token . ':' . $secret_key),
            'Content-Type' => 'application/json',
        ),
    ));

    if (is_wp_error($response)) {
        echo '<div class="notice notice-error"><p>Failed to fetch videos from Videograph AI API: ' . esc_html($response->get_error_message()) . '</p></div>';
        return;
    }

    $body = wp_remote_retrieve_body($response);
    $response_code = wp_remote_retrieve_response_code($response);

    // Check if the API request was successful
    if ($response_code !== 200) {
        echo '<div class="notice notice-error"><p>Failed to fetch videos from Videograph AI API. Check your API Credentials in <a href="' . esc_url(admin_url('admin.php?page=videograph-settings')) . '">Settings</a> Page</p></div>';
        return;
    }

    $videos = json_decode($body, true);

    // Check if the response is valid and contains an array of videos
    if (!is_array($videos) || empty($videos['data'])) {
        echo '<div class="notice notice-error"><p>No Videos Found. You can add videos from <a href="' . esc_url(admin_url('admin.php?page=videograph-add-new-video')) . '">here</a></p></div>';
        return;
    }

    // Sanitize view_mode parameter
    $view_mode = isset($_GET['view']) ? sanitize_text_field(wp_unslash($_GET['view'])) : 'list';
    $valid_view_modes = array('grid', 'list');

    if (!in_array($view_mode, $valid_view_modes)) {
        $view_mode = 'list';
    }

    // Custom filtering function based on the search query
    function videograph_video_filter($content, $videograph_search_query) {
        return strpos($content['contentId'], $videograph_search_query) !== false
            || strpos($content['title'], $videograph_search_query) !== false;
    }

    // Filter videos based on the search query
    if (!empty($videograph_search_query)) {
        $filtered_videos = array_filter($videos['data'], function ($content) use ($videograph_search_query) {
            return videograph_video_filter($content, $videograph_search_query);
        });
    } else {
        $filtered_videos = $videos['data'];
    }

    // If search query provided and no matching videos found
    if (!empty($videograph_search_query) && empty($filtered_videos)) {
        echo '<div><p>No Videos found matching the search query: ' . esc_html($videograph_search_query) . '</p></div>';
    }

    $total_videos = count($filtered_videos);
    $total_pages = ceil($total_videos / $per_page);

    $videos_data = array_slice($filtered_videos, $start_index, $per_page);
    
    // Display videos
    ?>
<div class="wrap vg-wrap">
    <h1 class="wp-heading-inline"><?php esc_html_e('Videograph AI Library' , 'videograph'); ?></h1>
    <a href="<?php echo esc_url(admin_url('admin.php?page=videograph-add-new-video')); ?>"
        class="page-title-action"><?php esc_html_e('Add New' , 'videograph'); ?></a>
    <hr class="wp-header-end">
    <div class="wp-filter">
        <div class="view-switch <?php echo esc_attr($view_mode); ?>">
            <a href="<?php echo esc_url(add_query_arg('view', 'list')); ?>" class="view-list" id="view-switch-list">
                <span class="screen-reader-text"><?php esc_html_e('List view', 'videograph'); ?></span>
            </a>
            <a href="<?php echo esc_url(add_query_arg('view', 'grid')); ?>" class="view-grid" id="view-switch-grid" 
                aria-current="page">
                <span class="screen-reader-text"><?php esc_html_e('Grid view', 'videograph'); ?></span>
            </a>
        </div>
        <div class="filter-items-right">

            <?php $url = esc_url(add_query_arg(array('view' => $view_mode), admin_url('admin.php?page=videograph-video-library&view=' . $view_mode))); ?>


            <form id="videos-per-page-form" method="GET" action="<?php echo esc_url(admin_url('admin.php?page=videograph-video-library')); ?>">
                <input type="hidden" name="page" value="videograph-video-library">
                <input type="hidden" name="view" value="<?php echo esc_attr($view_mode); ?>">
                <?php wp_nonce_field('vg_videos_per_page_nonce', 'vg_videos_per_page_nonce_field'); ?>
                <div class="videos-count">
                    <label for="videos-per-page"><?php esc_html_e('Items per page', 'videograph'); ?></label>
                    <select id="videos-per-page" name="per_page">
                        <option value="10" <?php selected($per_page, 10); ?>><?php esc_html_e('10', 'videograph'); ?></option>
                        <option value="20" <?php selected($per_page, 20); ?>><?php esc_html_e('20', 'videograph'); ?></option>
                        <option value="30" <?php selected($per_page, 30); ?>><?php esc_html_e('30', 'videograph'); ?></option>
                    </select>
                </div>
            </form>

            <div class="search-form">
                <label for="media-search-input" class="media-search-input-label"><?php esc_html_e('Search', 'videograph'); ?></label>
                <input type="search" id="media-search-input" class="search" name="s"
                    value="<?php echo esc_attr($videograph_search_query); ?>" />
            </div>

        </div>

    </div>

    <div class="vg-video-container <?php echo esc_attr(($view_mode === 'list') ? 'list-view' : 'grid-view'); ?>">
        <table class="wp-list-table widefat fixed striped table-view-list posts">
            <thead>
                <tr>
                    <th scope="col" class="manage-column"><?php esc_html_e('Created at', 'videograph'); ?></th>
                    <th scope="col" class="manage-column"><?php esc_html_e('Title', 'videograph'); ?></th>
                    <th scope="col" class="manage-column"><?php esc_html_e('Video ID', 'videograph'); ?></th>
                    <th scope="col" class="manage-column"><?php esc_html_e('Thumbnail', 'videograph'); ?></th>
                    <th scope="col" class="manage-column"><?php esc_html_e('Duration', 'videograph'); ?></th>
                    <th scope="col" class="manage-column"><?php esc_html_e('Resolution', 'videograph'); ?></th>
                    <th scope="col" id="date" class="manage-column"><?php esc_html_e('Status', 'videograph'); ?></th>
                    <th scope="col" id="date" class="manage-column"><?php esc_html_e('Actions', 'videograph'); ?></th>
                </tr>
            </thead>



            <tbody id="the-list">
                <?php foreach ($videos_data as $content) { 
            $created_at = $content['created_at'];
            $timestamp = $created_at / 1000;
            // date_default_timezone_set('Asia/Kolkata');
            // $date = date('d/m/y h:i a', $timestamp);
            $date = date_i18n('d/m/y h:i a', $timestamp); // Use date_i18n() to format the date based on WordPress timezone settings
            $duration = $content['status'] === 'Ready' ? gmdate('H:i:s', round($content['duration'] / 1000)) : '_';
            $resolution = $content['status'] === 'Ready' ? $content['resolution'] : '_';
            $videoId = $content['contentId'];
            $classes = 'view-details';
            if ($content['status'] === 'Ready') {
                $classes .= ' view-details-link';
            }
        ?>
                <tr>
                    <td><?php echo esc_html(date_i18n('d/m/y h:i a', $content['created_at'] / 1000)); ?></td>
                    <td>
                        <a class="<?php echo esc_attr($content['status'] === 'Ready' ? 'view-details view-details-link' : 'view-details'); ?>"
                            data-stream-id="<?php echo esc_attr($content['contentId']); ?>">
                            <?php echo esc_html($content['title']); ?>
                        </a>
                    </td>
                    <td>
                        <a class="<?php echo esc_attr($content['status'] === 'Ready' ? 'view-details view-details-link' : 'view-details'); ?>"
                            data-stream-id="<?php echo esc_attr($content['contentId']); ?>">
                            <?php echo esc_attr($content['contentId']); ?>
                        </a>
                    </td>
                    <td>
                        <a class="<?php echo esc_attr($classes) ?>"
                            data-stream-id="<?php echo esc_attr($content['contentId']); ?>">
                            <figure style="background-image: url(<?php echo esc_url($content['thumbnailUrl']); ?>);">
                            </figure>
                        </a>
                    </td>
                    <td><?php echo esc_attr($duration); ?></td>
                    <td><?php echo esc_attr($resolution); ?></td>
                    <td id="<?php echo esc_attr($content['status']); ?>" class="status">
                        <p class="pending">Pending <span class="dashicons dashicons-update"></span></p>
                        <p class="process">Processing <span class="dashicons dashicons-update"></span></p>
                        <p class="ready">Ready <span class="dashicons dashicons-yes-alt"></span></p>
                        <p class="failed">Failed <span class="dashicons dashicons-dismiss"></span></p>
                    </td>
                    <td>
                        <div class="video-actions">
                            <button class="video-actions-btn">
                                <span class="dots">.</span>
                            </button>
                            <div class="video-actions-menu">
                                <ul>
                                    <li><a data-stream-id="<?php echo esc_attr($content['contentId']); ?>"
                                            class="<?php echo esc_attr($classes) ?>">
                                            <span class="dashicons dashicons-info"></span>Video Details
                                        </a></li>
                                    <li><a class="video-delete"
                                            data-stream-id="<?php echo esc_attr($content['contentId']); ?>">
                                            <span class="dashicons dashicons-trash"></span>Delete Video
                                        </a></li>
                                </ul>
                            </div>
                        </div>
                    </td>
                </tr>


                <div class="video-item" id="the-grid">
                    <span
                        class="<?php echo $content['status'] === 'Failed' ? 'failed' : ''; ?> vg-video-item-status"></span>
                    <a class="<?php echo esc_attr($classes) ?>"
                        data-stream-id="<?php echo esc_attr($content['contentId']); ?>"></a>
                    <figure style="background-image: url(<?php echo esc_url($content['thumbnailUrl']); ?>);"></figure>
                    <h3 class="video-title"><?php echo esc_html($content['title']); ?></h3>
                </div>
                <?php } ?>


            </tbody>
        </table>

    </div>

    <div id="no-results-message" style="display: none;">No search results found</div>



    <div class="wrap">
        <?php
    if ($total_pages >= 1) {
        echo '<div class="vg-pagination">';
        echo '<span class="displaying-num">' . esc_html($total_videos) . ' items </span>';

        // Get the current videos per page setting
        $per_page = isset($_GET['per_page']) ? intval($_GET['per_page']) : 10;

        // Add a "First Page" button
        if ($current_page > 1) {
            $first_url = add_query_arg(array('paged' => 1, 'view' => $view_mode, 'per_page' => $per_page), admin_url('admin.php?page=videograph-video-library&view=' . $view_mode));
            echo '<a href="' . esc_url($first_url) . '" class="first-page button">« First</a>';
        } else {
            echo '<a class="first-page button" disabled>« First</a>';
        }

        if ($current_page > 1) {
            $prev_page = $current_page - 1;
            $prev_url = add_query_arg(array('paged' => $prev_page, 'view' => $view_mode, 'per_page' => $per_page), admin_url('admin.php?page=videograph-video-library&view=' . $view_mode));
            echo '<a href="' . esc_url($prev_url) . '" class="prev-page button">‹</a>';
        } else {
            echo '<a class="prev-page button" disabled>‹</a>';
        }

        // Display current page number and total pages
        echo '<span class="current-page">' . esc_html($current_page) . ' of ' . esc_html($total_pages) . '</span>';

        if ($current_page < $total_pages) {
            $next_page = $current_page + 1;
            $next_url = add_query_arg(array('paged' => $next_page, 'view' => $view_mode, 'per_page' => $per_page), admin_url('admin.php?page=videograph-video-library&view=' . $view_mode));
            echo '<a href="' . esc_url($next_url) . '" class="next-page button">›</a>';
        } else {
            echo '<a class="next-page button" disabled>›</a>';
        }

        // Add a "Last Page" button
        if ($current_page < $total_pages) {
            $last_url = add_query_arg(array('paged' => $total_pages, 'view' => $view_mode, 'per_page' => $per_page), admin_url('admin.php?page=videograph-video-library&view=' . $view_mode));
            echo '<a href="' . esc_url($last_url) . '" class="last-page button">Last »</a>';
        } else {
            echo '<a class="last-page button" disabled>Last »</a>';
        }

        echo '</div>';
    }
    ?>
    </div>


    <style>
    .container {
        position: relative;
        overflow: hidden;
        width: 100%;
        padding-top: 56.50%;
    }

    .responsive-iframe {
        position: absolute;
        top: 0;
        left: 0;
        bottom: 0;
        right: 0;
        width: 100%;
        height: 100%;
    }
    </style>

    <script>
    jQuery(document).ready(function($) {
        function videograph_filter_items(videograph_search_query) {
            const $tableRows = $('#the-list tr');
            const $gridItems = $('.video-item');
            const $noResultsMessageTable = $('#no-results-message');
            const $noResultsMessageGrid = $('#no-results-message1');
            const $noPagination = $('.vg-pagination');

            let foundMatchTable = false; // Flag for table rows
            let foundMatchGrid = false; // Flag for grid items

            // Filter table rows
            $tableRows.each(function() {
                const contentId = $(this).find('td:nth-child(3)').text();
                const title = $(this).find('td:nth-child(2)').text();

                if (
                    contentId.toLowerCase().includes(videograph_search_query.toLowerCase()) ||
                    title.toLowerCase().includes(videograph_search_query.toLowerCase())
                ) {
                    $(this).show();
                    foundMatchTable = true;
                } else {
                    $(this).hide();
                }
            });

            // Filter grid items
            $gridItems.each(function() {
                const title = $(this).find('.video-title').text();

                if (title.toLowerCase().includes(videograph_search_query.toLowerCase())) {
                    $(this).show();
                    foundMatchGrid = true;
                } else {
                    $(this).hide();
                }
            });

            if ((foundMatchTable && videograph_search_query.trim() !== '') || foundMatchGrid) {
                $noResultsMessageTable.hide();
                $noResultsMessageGrid.hide();
                $noPagination.hide();
                if (videograph_search_query.trim() === '') {
                    $noPagination.show();
                }
            } else {
                $noResultsMessageTable.show();
                $noResultsMessageGrid.show();
            }
        }

        // Event listener for search input changes
        $('#media-search-input').on('input', function() {
            const videograph_search_query = $(this).val();
            videograph_filter_items(videograph_search_query);
        });

        // Initially show pagination
        $('.vg-pagination').show();
    });

    function videograph_copytext(inputElement, copyButton) {
        inputElement.select();
        inputElement.setSelectionRange(0, 99999); // For mobile devices

        document.execCommand("copy");

        // Use requestAnimationFrame to ensure smooth update of button label
        requestAnimationFrame(function() {
            setTimeout(1500);
        });
    }
    </script>



    <div class="vg-popup-content">
        <div class="popup-overlay"></div>
        <!-- <button class="close-popup"><span class="dashicons dashicons-no"></span></button> -->
        <div class="popup-main">
            <div class="video-popup-cnt">
                <div class="video-popup-header">
                    <h2><?php esc_html_e('Video Details' , 'videograph'); ?></h2>
                    <button class="close-button"><span class="dashicons dashicons-no"></span></button>
                </div>
                <div class="video-popup-main" id="liveStreamDetailsPopup">

                </div>
            </div>
        </div>
    </div>


    <script>
    function videograph_fetch_video_details(videoId) {
        // Make API call to fetch livestream details
        var apiUrl = 'https://api.videograph.ai/video/services/api/v1/contents/' + videoId;
        var headers = {
            'Authorization': 'Basic <?php echo esc_html(base64_encode($access_token . ':' . $secret_key)); ?>',
            'Content-Type': 'application/json'
        };

        fetch(apiUrl, {
                method: 'GET',
                headers: headers
            })
            .then(response => response.json())
            .then(data => {
                // Process and display the livestream details in the popup
                var videoDetails = data.data;
                var title = videoDetails.title;
                var description = videoDetails.description;
                var status = videoDetails.status;
                var contentId = videoDetails.contentId;
                var created_at = videoDetails.created_at;
                var timestamp = created_at / 1000;

                var date = new Date(created_at);
                var options = {
                    year: '2-digit',
                    month: '2-digit',
                    day: '2-digit',
                    hour: '2-digit',
                    minute: '2-digit',
                    hour12: true,
                    // timeZone: 'browser' // Set the time zone to India/Kolkata
                };
                var formattedDate = date.toLocaleString('en-IN', options);

                var popupContent = `
                <div class="video-popup-main-left">
                    <div class="container">
                        <iframe id="video-iframe" class="responsive-iframe" src="https://dashboard.videograph.ai/videos/embed?videoId=${contentId}" allowfullscreen></iframe>
                    </div>
                </div>
                <div class="video-popup-main-right">
                    <div class="video-details-top">
                        <p><strong>File Name: </strong> ${title}</p>
                        <p><strong>Created On: </strong> ${formattedDate}</p>
                        <p><strong>Published On: </strong> ${formattedDate}</p>
                        <p><strong>Video updated On: </strong> ${formattedDate}</p>
                    </div>

                    <div class="video-details-main">
                        <p>
                            <strong>Title: </strong>
                            <input type="text" value="${title}" readonly />
                        </p>
                        <p>
                            <strong>Description: </strong>
                            <textarea readonly>
                                ${description}
                            </textarea>
                        </p>
                        <p>
                            <strong>Tags: </strong>
                            <input type="text" value="" readonly />
                        </p>
                        <p>
                            <strong>Video Shortcode: </strong>
                            <input type="text" value="[videograph content_id='${contentId}']" readonly />
                            <button onclick="videograph_copytext(this.previousElementSibling, this)"><span class="dashicons dashicons-admin-page"></span></button>
                        </p>
                        <p>
                            <strong>Sharable URL</strong>
                            <input type="text" value="https://dashboard.videograph.ai/videos/embed?videoId=${contentId}" readonly />
                            <button onclick="videograph_copytext(this.previousElementSibling, this)"><span class="dashicons dashicons-admin-page"></span></button>
                        </p>
                    </div>

                    <div class="video-details-bottom">
                        <a href="https://dashboard.videograph.ai/" target="_blank">Edit on videograph.ai</a> <span>|</span>
                        <a href="#" class="video-delete video-delete-pop" data-stream-id="${contentId}">Delete Permanently</a>
                    </div>
                </div>`;

                document.getElementById('liveStreamDetailsPopup').innerHTML = popupContent;
                document.querySelector('.popup-overlay').style.display = 'block';
                document.querySelector('.vg-popup-content').style.display = 'block';

                var deleteVideoLink = document.querySelector('.video-delete-pop');
                deleteVideoLink.addEventListener('click', function(event) {
                    event.preventDefault();
                    var videoId = this.getAttribute('data-stream-id');
                    videograph_delete_video(videoId);
                });
            })
            .catch(error => {
                console.log('Error:', error);
            });
    }

    function videograph_delete_video(videoId) {
        if (confirm("Are you sure you want to delete this video?")) {
            var apiUrl = 'https://api.videograph.ai/video/services/api/v1/contents/' + videoId;
            var headers = {
                'Authorization': 'Basic <?php echo esc_html(base64_encode($access_token . ':' . $secret_key)); ?>',
                'Content-Type': 'application/json'
            };

            fetch(apiUrl, {
                    method: 'DELETE',
                    headers: headers
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'Success') {
                        // Video deleted successfully
                        // You can update the UI or perform any other action as needed
                        location.reload(); // Refresh the page to update the video list
                    } else {
                        // Failed to delete the video
                        alert('Failed to delete the video. Please try again.');
                    }
                })
                .catch(error => {
                    console.log('Error:', error);
                });
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        var viewDetailsLinks = document.querySelectorAll('.view-details-link');
        var body = document.querySelector('body'); // Select the body element correctly

        viewDetailsLinks.forEach(function(link) {
            link.addEventListener('click', function(event) {
                event.preventDefault();
                var videoId = this.getAttribute('data-stream-id');
                videograph_fetch_video_details(videoId);
                body.style.overflow = 'hidden'; // Use pure JavaScript to modify CSS
            });
        });

        var popupOverlay = document.querySelector('.popup-overlay');
        var popupContent = document.querySelector('.vg-popup-content');
        var videoIframe = document.querySelector('.video-popup-main #video-iframe'); // Select the video iframe

        var closeButtons = document.querySelectorAll('.close-button, .popup-overlay');
        closeButtons.forEach(function(button) {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                var videoPopup = this.closest('.vg-popup-content');
                if (videoPopup) {
                    var iframe = videoPopup.querySelector('iframe');
                    if (iframe) {
                        // Stop the video when the popup is closed
                        iframe.src = iframe.src.replace('autoplay=1', 'autoplay=0');
                    }
                    popupContent.style.display = 'none';
                    popupOverlay.style.display = 'none';
                    body.style.overflow = 'visible';
                }
            });
        });

        // Delete video functionality
        var deleteVideoLinks = document.querySelectorAll('.video-delete');
        deleteVideoLinks.forEach(function(link) {
            link.addEventListener('click', function(event) {
                event.preventDefault();
                var videoId = this.getAttribute('data-stream-id');
                videograph_delete_video(videoId);
            });
        });
    });

    function videograph_copytext(inputElement, copyButton) {
        inputElement.select();
        inputElement.setSelectionRange(0, 99999); // For mobile devices
        document.execCommand("copy");
        // Use requestAnimationFrame to ensure smooth update of button label
        requestAnimationFrame(function() {
            setTimeout(1500);
        });
    }
    </script>


    <script>
    // Function to automatically submit the form when the "Videos per page" dropdown value changes
    document.getElementById('videos-per-page').addEventListener('change', function() {
        document.getElementById('videos-per-page-form').submit();
    });

    // Initialize the "Videos per page" dropdown with the current value
    const currentVideosPerPage = <?php echo esc_html($per_page); ?>;
    document.getElementById('videos-per-page').value = currentVideosPerPage;

    document.addEventListener("DOMContentLoaded", function() {
        const form = document.getElementById("videos-per-page-form");
        const perPageSelect = document.getElementById("videos-per-page");

        form.addEventListener("submit", function(event) {
            event.preventDefault();

            const selectedPerPage = perPageSelect.value;
            const currentView = "<?php echo esc_html($view_mode); ?>";

            const newUrl = "<?php echo esc_url(admin_url('admin.php?page=videograph-video-library')); ?>" +
                "&per_page=" + selectedPerPage + "&view=" + currentView;

            window.location.href = newUrl;
        });
    });
    </script>

    <?php
}
