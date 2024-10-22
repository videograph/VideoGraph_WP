<?php

// Live Stream page
function videograph_create_livestream()
{
    // Check if API keys are inserted
    $access_token = get_option('videograph_access_token');
    $secret_key = get_option('videograph_secret_key');

    if (empty($access_token) || empty($secret_key)) {
        echo '<div class="vi-notice-error"><p>';
        esc_html_e('The API key is missing or invalid. Please go to the', 'videograph');
        echo ' <a href="' . esc_url(admin_url('admin.php?page=videograph-settings')) . '">';
        esc_html_e('settings', 'videograph');
        echo '</a> ';
        esc_html_e('page and update it with the correct one.', 'videograph');
        echo '</p></div>';
        return;
    }

    // Check if form is submitted
    if (isset($_POST['start_live_stream'])) {
        if (isset($_POST['start_live_stream_nonce_field']) &&
            wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['start_live_stream_nonce_field'])), 'start_live_stream_nonce')
        ) {
            // Create Live Stream
            $title = isset($_POST['live_stream_title']) ? sanitize_text_field(wp_unslash($_POST['live_stream_title'])) : '';
            $description = 'desc';
            $region = isset($_POST['live_stream_region']) ? sanitize_text_field(wp_unslash($_POST['live_stream_region'])) : '';
            $record = isset($_POST['live_stream_record']) ? true : false;

            // Prepare API request data
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
                'dvrDurationInMins' => 0,
                'tags' => ['string'],
                'metadata' => [['key' => 'string', 'value' => 'string']],
                'playback_policy' => ['public'],
                'recordings_playback_policy' => ['public'],
            ));

            // Send API request
            $response = wp_remote_post($api_url, array('headers' => $headers, 'body' => $body, 'sslverify' => true));

            if (is_wp_error($response)) {
                echo '<div class="notice notice-error"><p>';
                esc_html_e('Failed to create live stream. Error: ', 'videograph');
                echo esc_html($response->get_error_message());
                echo '</p></div>';
            } else {
                $response_code = wp_remote_retrieve_response_code($response);
                $body = wp_remote_retrieve_body($response);
                $live_stream_data = json_decode($body, true);

                // Check if the API request was successful
                if ($response_code === 201) {
                    $streamId = $live_stream_data['data']['streamUUID'];
                    echo '<div class="notice notice-success"><p>';
                    esc_html_e('Live stream created successfully.', 'videograph');
                    echo '<br>';
                    esc_html_e('Stream ID: ', 'videograph');
                    echo '<strong>' . esc_html($streamId) . '</strong></p></div>';
                } else {
                    echo '<div class="notice notice-error"><p>';
                    esc_html_e('Failed to create live stream. Check your API Credentials in the ', 'videograph');
                    echo '<a href="' . esc_url(admin_url('admin.php?page=videograph-settings')) . '">';
                    esc_html_e('Settings', 'videograph');
                    echo '</a> ';
                    esc_html_e('Page.', 'videograph');
                    echo '</p></div>';
                }
            }
        } else {
            // Nonce verification failed
            wp_die(esc_html__('Error: Nonce verification failed. Form submission is not valid.', 'videograph'));
        }
    }

    // Display live stream form
    ?>
    <div class="wrap vg-wrap">
        <hr class="wp-header-end">
        <div class="livestream-wrap">
            <div class="live_stream_form">
                <h2><?php esc_html_e('Create a Live Stream', 'videograph'); ?></h2>
                <div id="loader" style="display: none;">
                    <div class="loader"></div>
                </div>
                <form method="post" id="live-stream-form">
                    <input type="hidden" name="action" value="start_live_stream_action">
                    <?php wp_nonce_field('start_live_stream_nonce', 'start_live_stream_nonce_field'); ?>
                    <div class="form-field">
                        <label for="live-stream-title"><?php esc_html_e('Title:', 'videograph'); ?></label>
                        <input type="text" id="live-stream-title" name="live_stream_title" required 
                               placeholder="<?php esc_attr_e('Enter live stream title here', 'videograph'); ?>" 
                               oninput="validateTitle(this)">
                        <div id="vg-title-error" style="color: red;"></div>
                    </div>

                    <div class="form-field">
                        <label for="live-stream-region"><?php esc_html_e('Region:', 'videograph'); ?></label>
                        <select id="live-stream-region" name="live_stream_region">
                            <option value="ap-south-1"><?php esc_html_e('AP South 1', 'videograph'); ?></option>
                            <option value="us-west-1"><?php esc_html_e('US West 1', 'videograph'); ?></option>
                        </select>
                    </div>

                    <div class="form-field record">
                        <label for="live-stream-record"><?php esc_html_e('Record:', 'videograph'); ?></label>
                        <input type="checkbox" id="live-stream-record" name="live_stream_record" checked>
                        <span><?php esc_html_e('Live Recording', 'videograph'); ?></span>
                    </div>

                    <button type="submit" id="start_live_stream_button" name="start_live_stream" 
                            class="button button-primary" disabled>
                        <?php esc_html_e('Create Live Stream', 'videograph'); ?>
                    </button>
                </form>
            </div>
        </div>
    </div>
    <?php
}
