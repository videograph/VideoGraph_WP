<?php

function vg_add_new_video()
{
    // Check if API credentials exist
    $access_token = get_option('vg_access_token');
    $secret_key = get_option('vg_secret_key');

    if (empty($access_token) || empty($secret_key)) {
        echo '<div><p>The API key is missing or invalid. Please go to the <a href="' . esc_url(admin_url('admin.php?page=vg-settings')) . '">settings</a> page and update it with the correct one.</p></div>';
        return;
    }


    $api_url = 'https://api.videograph.ai/video/services/api/v1/contents';
    $headers = array(
        'Authorization' => 'Basic ' . base64_encode($access_token . ':' . $secret_key),
        'Content-Type' => 'application/json',
    );

    $response = wp_remote_get($api_url, array('headers' => $headers));

    if (is_wp_error($response)) {
        echo '<div class="notice notice-error"><p>Failed to fetch videos from Videograph AI API.</p></div>';
        return;
    }

    $body = wp_remote_retrieve_body($response);
    $response_code = wp_remote_retrieve_response_code($response);

    // Check if the API request was successful
    if ($response_code !== 200) {
        echo '<div class="notice notice-error"><p>Check your API Credentials in <a href="' . esc_url(admin_url('admin.php?page=vg-settings')) . '">Settings</a> Page</p></div>';
        return;
    }

    if (isset($_GET['success']) && $_GET['success'] === '1') {
        echo '<div class="notice notice-success"><p>Video uploaded successfully!</p></div>';
    } elseif (isset($_GET['success']) && $_GET['success'] !== '1') {
        echo '<div class="notice notice-error"><p>Error: Invalid Videograph API credentials. Please check your API credentials in the <a href="' . esc_url(admin_url('admin.php?page=vg-settings')) . '">Settings</a> page.</p></div>';
    }

    ?>
    <div class="wrap">
        <h1 class="wp-heading-inline">Add New Video</h1>
        <a href="<?php echo esc_url(admin_url('admin.php?page=vg-upload-new-video')); ?>" class="page-title-action">Upload Video</a>
        <hr class="wp-header-end">
        <div class="vg-add-video-wrap">
            <div class="vg-add-video-form">
                <?php
                // Display error messages
                if (isset($_GET['error'])) {
                    echo '<div class="notice notice-error"><p>Error: ' . esc_html(sanitize_text_field($_GET['error'])) . '</p></div>';
                }
                ?>
                <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                    <input type="hidden" name="action" value="create_post_action">
                    <?php wp_nonce_field('create_post_nonce', 'nonce'); // Add nonce field ?>

                    <div class="form-field">
                        <!-- <label for="post_title">Title</label> -->
                        <input type="text" id="post_title" name="post_title" class="regular-text" required placeholder="Enter video title here" oninput="validateTitle(this)">
                        <div id="vg-title-error" style="color: red;"></div>
                    </div>
                    <div class="form-field">
                        <!-- <label for="post_url">Video URL</label> -->
                        <input type="url" id="post_url" name="post_url" class="regular-text" required placeholder="Enter video URL here">
                    </div>
                    <button type="submit" id="add_video_button" name="add_video" class="button button-primary" disabled>Add Video</button>
                </form>
            </div>
        </div>
    </div>


<script>
function validateTitle(input) {
    // Trim consecutive spaces
    const title = input.value.replace(/\s\s+/g, ' ');
    input.value = title;

    const errorDiv = document.getElementById('vg-title-error');
    const symbols = /[!@#$%^&*()_+{}\[\]:;<>,.?~\\|-]/;

    if (title.length > 50) {
        input.value = title.substring(0, 50); // Truncate to 50 characters
        errorDiv.textContent = 'Title should not exceed 50 characters.';
        input.setCustomValidity('');
    } else if (/^\s/.test(title)) {
        // Block space at the beginning
        input.value = title.trim();
        input.setCustomValidity('');
    } else {
        errorDiv.textContent = '';
        input.setCustomValidity('');
    }
}

  document.addEventListener('DOMContentLoaded', function() {
    const titleField = document.getElementById('post_title');
    const urlField = document.getElementById('post_url');
    const addButton = document.getElementById('add_video_button');

    // Function to check if both fields are non-empty
    function validateFields() {
      if (titleField.value.trim() !== '' && urlField.value.trim() !== '') {
        addButton.removeAttribute('disabled');
      } else {
        addButton.setAttribute('disabled', true);
      }
    }

    // Add input event listeners to both fields
    titleField.addEventListener('input', validateFields);
    urlField.addEventListener('input', validateFields);
  });
</script>



    <?php
}

// Callback function for form submission
function vg_add_new_video_callback()
{
    if (isset($_POST['nonce']) && wp_verify_nonce(wp_unslash(sanitize_text_field($_POST['nonce'])), 'create_post_nonce')) {
        // Check if API credentials exist
        $access_token = get_option('vg_access_token');
        $secret_key = get_option('vg_secret_key');

        if (empty($access_token) || empty($secret_key)) {
            wp_die('Error: Videograph API credentials do not exist. Add your API Credentials in Settings Page.');
        }

        // Get the title and video URL from the form submission
        $title = sanitize_text_field($_POST['post_title']);
        $url = esc_url_raw($_POST['post_url']);

        // Validate title and URL
        if (empty($title) || empty($url)) {
            wp_die('Error: Title and URL are required fields.');
        }

        // Prepare and send the POST request
        $api_url = 'https://api.videograph.ai/video/services/api/v1/contents';
        $headers = array(
            'Content-Type' => 'application/json',
            'Authorization' => 'Basic ' . base64_encode($access_token . ':' . $secret_key)
        );

        $data = array(
            'title' => $title,
            'content' => array(
                array(
                    'url' => $url
                )
            ),
            'playback_policy' => array(
                'public',
                'signed'
            ),
            'mp4_support' => true,
            'save_original_copy' => true
        );

        $response = wp_remote_post($api_url, array(
            'method' => 'POST',
            'headers' => $headers,
            'body' => wp_json_encode($data),
            'timeout' => 30,
            'sslverify' => true // Change this to true if your server supports SSL verification
        ));

        // Handle the response
        if (is_wp_error($response)) {
            wp_die('Error: Failed to communicate with Videograph AI API.');
        }

        $response_code = wp_remote_retrieve_response_code($response);
        $response_body = json_decode(wp_remote_retrieve_body($response));

        if ($response_code === 201) {
            // Success message with stream ID
            $stream_id = isset($response_body->id) ? $response_body->id : '';
            wp_safe_redirect(admin_url('admin.php?page=vg-add-new-video&success=1'));
            exit;
        } elseif ($response_code === 401) {
            // Authentication error
            wp_die('Error: Invalid Videograph API credentials. Please check your API credentials in the Settings page.');
        } else {
            // Other errors
            wp_die('Error: Failed to upload video.');
        }
    } else {
        // Invalid nonce
        wp_die('Error: Invalid nonce. Form submission is not valid.');
    }
}
