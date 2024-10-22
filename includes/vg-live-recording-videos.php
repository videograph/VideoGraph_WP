<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

    function videograph_livestreamRecording_shortcode($atts) {
        $atts = shortcode_atts(array('stream_id' => '',), $atts, 'videograph-livestream');
        if (empty($atts['stream_id'])) {
            return '<p>Error: Livestream ID is missing.</p>';
        }
        $streamId = sanitize_text_field($atts['stream_id']);
        return '<div class="vg-video-iframe"><iframe width="100%" height="100%" src="https://dashboard.videograph.ai/videos/embed?streamId=' . $streamId . '" frameborder="0" allowfullscreen></iframe> </div>';
    }
    add_shortcode('videograph-livestream', 'videograph_livestreamRecording_shortcode');
// Livestream Recording Videos page
    function videograph_live_recordings() 
{
        // Check if API keys are inserted
        $access_token = get_option('videograph_access_token');
        $secret_key = get_option('videograph_secret_key');
        if (empty($access_token) || empty($secret_key)) {
            echo '<div class="vi-notice-error"><p>The API key is missing or invalid. Please go to the <a href="' . esc_url(admin_url('admin.php?page=videograph-settings')) . '">settings</a> page and update it with the correct one.</p></div>';
            return;
        }
        // Fetch Livestream videos
        $api_url = 'https://api.videograph.ai/video/services/api/v1/livestreams?record=true';
        $response = wp_remote_get($api_url, array('headers' => array('Authorization' => 'Basic ' . base64_encode($access_token . ':' . $secret_key), 'Content-Type' => 'application/json')));
        if (is_wp_error($response)) {
            $error_message = $response->get_error_message();
            echo '<div class="notice notice-error"><p>cURL Error: ' . esc_html($error_message) . '</p></div>';
        } else {
            $http_code = wp_remote_retrieve_response_code($response);
            $response_body = wp_remote_retrieve_body($response);
            $response_data = json_decode($response_body, true);
            // Check if the API request was successful
            if ($http_code === 200) {
                // $response_data = json_decode($response, true);
                if ($response_data['status'] === 'Success') {
                    $livestreams = $response_data['data'];
                    if (isset($_GET['vg_videos_per_page_nonce_field'])) {
                        if (wp_verify_nonce(sanitize_text_field(wp_unslash($_GET['vg_videos_per_page_nonce_field'])), 'vg_videos_per_page_nonce')) {
                            $videograph_search_query = isset($_GET['s']) ? sanitize_text_field(wp_unslash($_GET['s'])) : '';
                        } else {
                            $videograph_search_query = '';
                            echo '<div class="notice notice-error"><p>' . esc_html__('Security check failed. Please try again.', 'videograph') . '</p></div>';
                        }
                    } else {
                        // No form submission (first page load or no nonce sent), set default search query
                        $videograph_search_query = isset($_GET['s']) ? sanitize_text_field(wp_unslash($_GET['s'])) : '';
                    }

                    // Custom filtering function based on the search query
                    function videograph_custom_video_filter($content, $videograph_search_query) {
                        return strpos($livestream['streamUUID'], $videograph_search_query) !== false || strpos($livestream['title'], $videograph_search_query) !== false;
                    }
                    // Filter videos based on the search query
                    if (!empty($videograph_search_query)) {
                        $filtered_videos = array_filter($livestreams, function ($content) use ($videograph_search_query) {
                            return videograph_custom_video_filter($content, $videograph_search_query);
                        });
                    } else {
                        $filtered_videos = $livestreams;
                    }
                    // If search query provided and no matching videos found
                    if (!empty($videograph_search_query) && empty($filtered_videos)) {
                        echo '<div class="notice notice-warning"><p>No Livestream(s) found matching the search query: ' . esc_html($videograph_search_query) . '</p></div>';
                    }
                    $per_page = isset($_GET['per_page']) ? intval($_GET['per_page']) : 10; // Number of videos per page
                    $total_videos = count($livestreams);
                    $total_pages = ceil($total_videos / $per_page);
                    $current_page = isset($_GET['paged']) ? intval($_GET['paged']) : 1;
                    $start_index = ($current_page - 1) * $per_page;
                    $end_index = $start_index + $per_page;
                    $livestreams_data = array_slice($filtered_videos, $start_index, $per_page);
                ?>


        <div class="wrap vg-wrap">
            <h1 class="wp-heading-inline"><?php esc_html_e('Live Stream Recording' , 'videograph'); ?></h1>
            <a href="<?php echo esc_url(admin_url('admin.php?page=videograph-create-livestream')) ?>" class="page-title-action"><?php esc_html_e('Create Live Stream' , 'videograph'); ?></a>
            <hr class="wp-header-end"><br>

            <div class="wp-filter">
                <div class="search-form">
                    <label for="media-search-input" class="media-search-input-label"><?php esc_html_e('Search' , 'videograph'); ?></label>
                    <input type="search" id="media-search-input" class="search" name="s" value="<?php echo esc_attr($videograph_search_query); ?>" />
                </div>

                <form id="videos-per-page-form" method="GET" action="">
                    <input type="hidden" name="page" value="videograph-live-recordings">
                    <?php wp_nonce_field('vg_videos_per_page_nonce', 'vg_videos_per_page_nonce_field'); ?>
                    <div class="videos-count">
                        <label for="videos-per-page"><?php esc_html_e('Items per page' , 'videograph'); ?></label>
                        <select id="videos-per-page" name="per_page">
                            <option value="10" <?php selected($per_page, 10); ?>><?php esc_html_e('10', 'videograph'); ?></option>
                            <option value="20" <?php selected($per_page, 20); ?>><?php esc_html_e('20', 'videograph'); ?></option>
                            <option value="30" <?php selected($per_page, 30); ?>><?php esc_html_e('30', 'videograph'); ?></option>
                        </select>
                    </div>
                </form>
            </div>
                        <?php
                // Process the livestreams data
                if (!empty($livestreams)) { ?>
                <div class="livestream-table">        
                        <table class="wp-list-table widefat fixed striped table-view-list posts">
                            <thead>
                                <tr>
                                    <th scope="col"><?php esc_html_e('Created at' , 'videograph'); ?></th>
                                    <th scope="col"><?php esc_html_e('Title' , 'videograph'); ?></th>
                                    <th scope="col"><?php esc_html_e('Video ID' , 'videograph'); ?></th>
                                    <th scope="col"><?php esc_html_e('Thumbnail' , 'videograph'); ?></th>
                                    <th scope="col"><?php esc_html_e('Status' , 'videograph'); ?></th>
                                    <th scope="col"><?php esc_html_e('Actions' , 'videograph'); ?></th>
                                </tr>
                            </thead>
                            <tbody id="the-list">
                                <?php foreach ($livestreams_data as $livestream) :
                                    // Extract and display the livestream details
                                    $streamId = sanitize_text_field($livestream['streamUUID']);
                                    $title = sanitize_text_field($livestream['title']);
                                    $thumbnailUrl = isset($livestream['thumbnailUrl']) ? esc_url($livestream['thumbnailUrl']) : '';
                                    $created_at = $livestream['created_at'];
                                    $timestamp = $created_at / 1000;
                                    $createdOn = date_i18n('d/m/y h:i a', $timestamp);
                                    $status = sanitize_text_field($livestream['status']);
                                ?>
                                    <tr>
                                        <td><?php echo esc_html($createdOn); ?></td>
                                        <td><?php echo esc_html($title); ?></td>
                                        <td>
                                            <a href="#" class="view-details-link" data-stream-id="<?php echo esc_attr($streamId); ?>">
                                                <?php echo esc_html($streamId); ?>
                                            </a>
                                        </td>
                                        <td>
                                            <a href="#" class="view-details-link" data-stream-id="<?php echo esc_attr($streamId); ?>">
                                                <figure style="background-image: url(<?php echo esc_url($thumbnailUrl); ?>);"></figure>
                                            </a>
                                        </td>
                                        <td id="<?php echo esc_attr($status); ?>" class="status">
                                            <?php if ($status === 'Idle') : ?>
                                                <p class="idle">Offline <span class="dashicons dashicons-clock"></span></p>
                                            <?php elseif ($status === 'Active') : ?>
                                                <p class="active">Live <span class="dashicons dashicons-yes-alt"></span></p>
                                            <?php elseif ($status === 'Failed') : ?>
                                                <p class="failed">Failed <span class="dashicons dashicons-dismiss"></span></p>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="video-actions">
                                                <button class="video-actions-btn">
                                                    <span class="dots">.</span>
                                                </button>
                                                <div class="video-actions-menu">
                                                    <ul>
                                                        <li>
                                                            <a href="#" class="view-details-link" data-stream-id="<?php echo esc_attr($streamId); ?>">
                                                                <span class="dashicons dashicons-info"></span><?php esc_html_e('Stream Details' , 'videograph'); ?>
                                                            </a>
                                                        </li>
                                                        <li>
                                                            <a href="#" class="video-delete" data-stream-id="<?php echo esc_attr($streamId); ?>">
                                                                <span class="dashicons dashicons-trash"></span><?php esc_html_e('Delete Stream' , 'videograph'); ?>
                                                            </a>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>

                        <div id="no-results-message" style="display: none;"><?php esc_html_e('No search results found' , 'videograph'); ?></div>
                    </div>
                </div>
                        <?php
                } else {
            ?>
                            <div class="notice notice-error">
                                <p><?php esc_html_e('No Live Recording(s) found. You can Create Live Recording ' , 'videograph'); ?><a href="<?php echo esc_url(admin_url('admin.php?page=videograph-create-livestream')); ?>">here</a></p>
                            </div>
                        <?php
                }
            ?>

                <div class="vg-popup-content">
                    <div class="popup-overlay"></div>
                    <div class="popup-main">
                        <div class="livestream-popup-cnt">
                            <div class="video-popup-header">
                                <h2><?php esc_html_e('Live Recording Details' , 'videograph'); ?></h2>
                                <button class="close-button"><span class="dashicons dashicons-no"></span></button>
                            </div>
                            <div class="livestream-popup-main" id="liveStreamDetailsPopup">
                                
                            </div>
                        </div>
                    </div>
                </div>

                <script>
                    function videograph_fetch_livestream_details(streamId) {
                        // Make API call to fetch livestream details
                        var apiUrl = 'https://api.videograph.ai/video/services/api/v1/livestreams/' + streamId;
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
                            var livestreamDetails = data.data;
                            var title = livestreamDetails.title;
                            var description = livestreamDetails.description;
                            var ingestUrl = livestreamDetails.ingestUrl;
                            var streamKey = livestreamDetails.streamKey;
                            var streamUUID = livestreamDetails.streamUUID;
                            var playbackUrl = livestreamDetails.playbackUrl;
                            var status = livestreamDetails.status;

                            var created_at = livestreamDetails.createdOn;
                            var timestamp = created_at / 1000;

                            // Convert timestamp to a JavaScript Date object
                            // var date = new Date(created_at);
                            // var options = {
                            //     year: '2-digit',
                            //     month: '2-digit',
                            //     day: '2-digit',
                            //     hour: '2-digit',
                            //     minute: '2-digit',
                            //     hour12: true,
                            //     timeZone: 'Asia/Kolkata' // Set the time zone to India/Kolkata
                            // };
                            // var formattedDate = date.toLocaleString('en-IN', options);

                             // Convert timestamp to a JavaScript Date object
                             var date = new Date(timestamp * 1000);

                            // Set the timezone offset for India/Kolkata
                            var timezoneOffset = 0.05 * 60 * 60 * 1000;
                            date.setTime(date.getTime() + timezoneOffset);

                            // Format the date in desired format
                            var options = { year: '2-digit', month: '2-digit', day: '2-digit', hour: '2-digit', minute: '2-digit', hour12: true };
                            var formattedDate = date.toLocaleString('en-IN', options);

                            var popupContent = `
                                <div class="livestream-popup-main-left">
                                    <div class="livestream-player">
                                        <span class="${status}">${status === 'Active' ? 'Live' : 'Offline'}</span>
                                        ${status === 'Active' ? `
                                        <iframe width="100%" style="position: absolute; height: 100%; border: none;" src="https://dashboard.videograph.ai/videos/embed?streamId=${streamUUID}" allowfullscreen></iframe>
                                        ` : ''}
                                    </div>
                                </div>
                                <div class="livestream-popup-main-right">
                                    <div class="livestream-details-top">
                                        <p><strong>Live Stream ID: </strong> ${streamUUID}</p>
                                        <p><strong>File Name: </strong> ${title}</p>
                                        <p><strong>Created On: </strong> ${formattedDate}</p>
                                        <p><strong>Published On: </strong> ${formattedDate}</p>
                                    </div>
                                    <div class="livestream-details-main">
                                        <p>
                                            <strong>Title: </strong>
                                            <input type="text" value="${title}" readonly />
                                        </p>
                                        <p>
                                            <strong>Description: </strong>
                                            <textarea readonly>${description}</textarea>
                                        </p>
                                        <p>
                                            <strong>RTMP URL: </strong>
                                            <input type="text" value="${ingestUrl}" readonly />
                                            <button onclick="videograph_copytext(this.previousElementSibling, this)"><span class="dashicons dashicons-admin-page"></span></button>
                                        </p>
                                        <p>
                                            <strong>Stream Key: </strong>
                                            <input type="text" value="${streamKey}" readonly />
                                            <button onclick="videograph_copytext(this.previousElementSibling, this)"><span class="dashicons dashicons-admin-page"></span></button>
                                        </p>
                                        <p>
                                            <strong>Short Code: </strong>
                                            <input type="text" value="[videograph-livestream stream_id='${streamUUID}']" readonly />
                                            <button onclick="videograph_copytext(this.previousElementSibling, this)"><span class="dashicons dashicons-admin-page"></span></button>
                                        </p>
                                    </div>
                                    <div class="livestream-details-bottom">
                                        <a href="https://dashboard.videograph.ai/" target="_blank">Edit on videograph.ai</a>
                                        <span>|</span>
                                        <a href="#" class="video-delete" data-stream-id="${streamUUID}">Delete Permanently</a>
                                    </div>
                                </div>
                            `;

                            document.getElementById('liveStreamDetailsPopup').innerHTML = popupContent;
                            document.querySelector('.popup-overlay').style.display = 'block';
                            document.querySelector('.vg-popup-content').style.display = 'block';

                            var deleteVideoLink = document.querySelector('.vg-popup-content .video-delete');
                            if (deleteVideoLink) {
                                deleteVideoLink.addEventListener('click', function (event) {
                                    event.preventDefault();
                                    var streamId = this.getAttribute('data-stream-id');
                                    videograph_delete_video(streamId);
                                });
                            }
                        })
                        .catch(error => {
                            console.log('Error:', error);
                        });
                    }

                    function videograph_delete_video(streamId) {
                        if (confirm("Are you sure you want to delete this video?")) {
                            var apiUrl = 'https://api.videograph.ai/video/services/api/v1/livestreams/' + streamId;
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

                    document.addEventListener('DOMContentLoaded', function () {
                        var viewDetailsLinks = document.querySelectorAll('.view-details-link');
                        var body = document.querySelector('body');

                        viewDetailsLinks.forEach(function (link) {
                            link.addEventListener('click', function (event) {
                                event.preventDefault();
                                var streamId = this.getAttribute('data-stream-id');
                                videograph_fetch_livestream_details(streamId);
                                body.style.overflow = 'hidden';
                            });
                        });

                        var popupOverlay = document.querySelector('.popup-overlay');
                        var popupContent = document.querySelector('.vg-popup-content');

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
                        deleteVideoLinks.forEach(function (link) {
                            link.addEventListener('click', function (event) {
                                event.preventDefault();
                                var streamId = this.getAttribute('data-stream-id');
                                videograph_delete_video(streamId);
                            });
                        });
                    });
                </script>



            <div class="wrap">
                <?php
                    if ($total_pages >= 1) {
                        echo '<div class="vg-pagination">';
                        echo '<span class="displaying-num">' . esc_html($total_videos) . ' items </span>';
                        // Get the current videos per page setting
                        $per_page = isset($_GET['per_page']) ? intval($_GET['per_page']) : 10;
                        // Add a "First Page" button
                        if ($current_page > 1) {
                            $first_url = add_query_arg(array('paged' => 1, 'per_page' => $per_page), admin_url('admin.php?page=videograph-live-recordings'));
                            echo '<a href="' . esc_url($first_url) . '" class="first-page button">« First</a>';
                        } else {
                            echo '<a class="first-page button" disabled>« First</a>';
                        }
                        if ($current_page > 1) {
                            $prev_page = $current_page - 1;
                            $prev_url = add_query_arg(array('paged' => $prev_page, 'per_page' => $per_page), admin_url('admin.php?page=videograph-live-recordings'));
                            echo '<a href="' . esc_url($prev_url) . '" class="prev-page button">‹</a>';
                        } else {
                            echo '<a class="prev-page button" disabled>‹</a>';
                        }
                        // Display current page number and total pages
                        echo '<span class="current-page">' . esc_html($current_page) . ' of ' . esc_html($total_pages) . '</span>';
                        if ($current_page < $total_pages) {
                            $next_page = $current_page + 1;
                            $next_url = add_query_arg(array('paged' => $next_page, 'per_page' => $per_page), admin_url('admin.php?page=videograph-live-recordings'));
                            echo '<a href="' . esc_url($next_url) . '" class="next-page button">›</a>';
                        } else {
                            echo '<a class="next-page button" disabled>›</a>';
                        }
                        // Add a "Last Page" button
                        if ($current_page < $total_pages) {
                            $last_url = add_query_arg(array('paged' => $total_pages, 'per_page' => $per_page), admin_url('admin.php?page=videograph-live-recordings'));
                            echo '<a href="' . esc_url($last_url) . '" class="last-page button">Last »</a>';
                        } else {
                            echo '<a class="last-page button" disabled>Last »</a>';
                        }
                        echo '</div>';
                    }
                ?>
            </div>



                <script>
                    // Function to search and reload videos
                    jQuery(document).ready(function ($) {
                        // Function to filter table rows based on search input
                        function videograph_filter_table_rows(searchQuery) {
                            const $tableRows = $('#the-list tr');
                            const $noResultsMessage = $('#no-results-message'); // Added message element
                            const $noPagination = $('.vg-pagination');

                            let foundMatch = false;

                            $tableRows.each(function () {
                                const contentId = $(this).find('td:nth-child(3)').text();
                                const title = $(this).find('td:nth-child(2)').text();

                                if (
                                    contentId.toLowerCase().includes(searchQuery.toLowerCase()) ||
                                    title.toLowerCase().includes(searchQuery.toLowerCase())
                                ) {
                                    $(this).show();
                                	foundMatch = true;
                                } else {
                                    $(this).hide();
                                }
                            });

                            // Show/hide the no results message based on the flag
			                if (foundMatch) {
			                    $noResultsMessage.hide();
			                    $noPagination.hide();
                                if(searchQuery.trim() === ''){
                                    $noPagination.show();
                                }
			                } else {
			                    $noResultsMessage.show();
			                    $noPagination.hide();
			                }
                        }

                        // Event listener for search input changes
                        $('#media-search-input').on('input', function () {
                            const searchQuery = $(this).val();
                            videograph_filter_table_rows(searchQuery);
                        });
                    });

                    // Function to submit the form when the "Videos per page" dropdown value changes
                    document.getElementById('videos-per-page').addEventListener('change', function() {
                        document.getElementById('videos-per-page-form').submit();
                    });

                    // Initialize the "Videos per page" dropdown with the current value
                    const currentVideosPerPage = <?php echo esc_html($per_page); ?>;
                    document.getElementById('videos-per-page').value = currentVideosPerPage;

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
                            
                            const newUrl_live = "<?php echo esc_url(admin_url('admin.php?page=videograph-live-recordings')); ?>" +
                                "&per_page=" + selectedPerPage;
                            
                            window.location.href = newUrl_live;
                        });
                    });
                </script>

                <?php
            } else {
                echo '<div class="notice notice-error"><p>' . esc_html($response_data['message']) . '</p></div>';
            }
        } else {
            echo '<div class="notice notice-error"><p>Failed to fetch live stream videos from Videograph AI API. Check your API Credentials in <a href="' . esc_url(admin_url('admin.php?page=videograph-settings')) . '">Settings</a> Page</p></div';
        }
    }
}