<?php

// Live Stream page
function vg_live_stream()
{
    // Check if API keys are inserted
    $access_token = get_option('vg_access_token');
    $secret_key = get_option('vg_secret_key');

    if (empty($access_token) || empty($secret_key)) {
        echo '<div class="vi-notice-error"><p>The API key is missing or invalid. Please go to the <a href="' . esc_url(admin_url('admin.php?page=vg-settings')) . '">settings</a> page and update it with the correct one.</p></div>';
        return;
    }

    // Check if form is submitted
    if (isset($_POST['start_live_stream'])) {
        if (isset($_POST['start_live_stream_nonce_field']) && wp_verify_nonce(wp_unslash(sanitize_text_field($_POST['start_live_stream_nonce_field'])), 'start_live_stream_nonce')) {
        // Create Live Stream
        $title = sanitize_text_field($_POST['live_stream_title']);
        $description = "desc";
        $region = sanitize_text_field($_POST['live_stream_region']);
        $record = isset($_POST['live_stream_record']) ? true : false;
        $dvrDurationInMins = 0;
        $tags = ['string'];
        $metadata = [['key' => 'string', 'value' => 'string']];
        $playback_policy = ['public'];
        $recordings_playback_policy = ['public'];

        // Send POST request to create live stream
        $api_url = 'https://api.videograph.ai/video/services/api/v1/livestreams';
        $headers = array(
            'Authorization' => 'Basic ' . base64_encode($access_token . ':' . $secret_key),
            'Content-Type' => 'application/json',
        );
        $body = wp_json_encode(array(
            'title' => $title,
            'description' => $description,
            'region' => $region,
            'record' => $record,
            'dvrDurationInMins' => $dvrDurationInMins,
            'tags' => $tags,
            'metadata' => $metadata,
            'playback_policy' => $playback_policy,
            'recordings_playback_policy' => $recordings_playback_policy,
        ));

        $response = wp_remote_post($api_url, array('headers' => $headers, 'body' => $body));

        if (is_wp_error($response)) {
            echo '<div class="notice notice-error"><p>Failed to create live stream. Error: ' . esc_html($response->get_error_message()) . '</p></div>';
        } else {
            $response_code = wp_remote_retrieve_response_code($response);
            $response_message = wp_remote_retrieve_response_message($response);
            $body = wp_remote_retrieve_body($response);
            $live_stream_data = json_decode($body, true);

            // Check if the API request was successful
            if ($response_code === 201) {
                $streamId = $live_stream_data['data']['streamUUID'];

                echo '<div class="notice notice-success"><p style="text-align:left;">Live stream created successfully.<br>Stream ID: <strong>' . esc_html($streamId) . '</strong></p>';

                echo '</div>';
            } else {
                echo '<div class="notice notice-error"><p>Failed to create live stream. Check your API Credentials in <a href="' . esc_url(admin_url('admin.php?page=vg-settings')) . '">Settings</a> Page</p></div>';
            }
        }
    }
    else {
        // Nonce verification failed
        wp_die('Error: Nonce verification failed. Form submission is not valid.');
    }
    }

    // Display live stream form
    ?>
    <div class="wrap">
        <hr class="wp-header-end">
        <div class="livestream-wrap">
            <div class="live_stream_form">
                <h2>Create a Live Stream</h2>
                <div id="loader" style="display: none;">
                    <div class="loader"></div>
                </div>
                <form method="post" id="live-stream-form">
                <input type="hidden" name="action" value="start_live_stream_action">
                <?php wp_nonce_field('start_live_stream_nonce', 'start_live_stream_nonce_field'); ?>
                    <div class="form-field">
                        <label for="live-stream-title">Title:</label>
                        <input type="text" id="live-stream-title" name="live_stream_title" required placeholder="Enter live stream title here" oninput="validateTitle(this)">
                        <div id="vg-title-error" style="color: red;"></div>
                    </div>

                    <div class="form-field">
                        <label for="live-stream-region">Region:</label>
                        <select id="live-stream-region" name="live_stream_region">
                            <option value="ap-south-1">AP South 1</option>
                            <option value="us-west-1">US West 1</option>
                        </select>
                    </div>
                    <div class="form-field record">
                        <label for="live-stream-record">Record:</label>
                        <input type="checkbox" id="live-stream-record" name="live_stream_record" checked>
                        <span>Live Recording</span>
                    </div>
                    <button type="submit" id="start_live_stream_button" name="start_live_stream" class="button button-primary" disabled>Create Live Stream</button>
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
    const titleField = document.getElementById('live-stream-title');
    const regionField = document.getElementById('live-stream-region');
    const recordCheckbox = document.getElementById('live-stream-record');
    const startButton = document.getElementById('start_live_stream_button');

    // Function to check if required fields are filled
    function validateFields() {
      const titleValue = titleField.value.trim();
      const regionValue = regionField.value;
      
      if (titleValue !== '' && regionValue !== '') {
        startButton.removeAttribute('disabled');
      } else {
        startButton.setAttribute('disabled', true);
      }
    }

    // Add input and change event listeners to the fields
    titleField.addEventListener('input', validateFields);
    regionField.addEventListener('change', validateFields);
    recordCheckbox.addEventListener('change', validateFields);
  });
</script>

    <?php
}