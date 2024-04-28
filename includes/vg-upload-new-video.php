<?php

function vg_upload_new_video() {
    // Check if API credentials exist
    $access_token = get_option('vg_access_token');
    $secret_key = get_option('vg_secret_key');

    if (empty($access_token) || empty($secret_key)) {
        echo '<div class="vi-notice-error"><p>The API key is missing or invalid. Please go to the <a href="' . esc_url(admin_url('admin.php?page=videograph-ai-settings')) . '">settings</a> page and update it with the correct one.</p></div>';
        return;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Verify nonce
        if (!isset($_POST['vg_upload_nonce']) || !wp_verify_nonce($_POST['vg_upload_nonce'], 'vg_upload_nonce')) {
            wp_die('Security check failed. Please try again.');
        }

        // Check if a file was uploaded
        if (!isset($_FILES['video_file']) || $_FILES['video_file']['error'] !== UPLOAD_ERR_OK) {
            echo '<div class="vi-notice-error"><p>Please select a valid video file to upload.</p></div>';
            return;
        }

        // Get the video file details from the form submission
        $video_file = $_FILES['video_file'];
        $video_name_with_extension = sanitize_file_name($video_file['name']); // Sanitize file name
        $video_duration = 0; // Replace with the actual duration of the video (in seconds)

        // Get the upload URL from the API
        $upload_url_endpoint = 'https://api.videograph.ai/video/services/api/v1/uploads';
        $headers = [
            'Authorization' => 'Basic ' . base64_encode($access_token . ':' . $secret_key),
            'Content-Type' => 'application/json',
        ];

        // Prepare the payload for the API request
        $payload = [
            'file_name' => $video_name_with_extension,
            'duration' => $video_duration,
        ];

        // Send the request using WordPress HTTP API
        $response = wp_remote_post($upload_url_endpoint, [
            'headers' => $headers,
            'body' => wp_json_encode($payload),
        ]);

        if (is_wp_error($response)) {
            echo '<div class="notice notice-error"><p>Failed to communicate with Videograph AI API.</p></div>';
            return;
        }

        $response_code = wp_remote_retrieve_response_code($response);
        $response_body = wp_remote_retrieve_body($response);

        if ($response_code === 201) {
            $response_data = json_decode($response_body, true);
            if (isset($response_data['status']) && $response_data['status'] === 'Success') {
                $upload_url = $response_data['data']['url'];

                // Upload the video file to the obtained upload URL
                $file_contents = file_get_contents($video_file['tmp_name']);
                $file_size = filesize($video_file['tmp_name']);

                $upload_response = wp_remote_request($upload_url, [
                    'method' => 'PUT',
                    'body' => $file_contents,
                    'headers' => [
                        'Content-Type' => 'video/mp4',
                        'Content-Length' => $file_size,
                    ],
                ]);

                $http_code = wp_remote_retrieve_response_code($upload_response);

                // Check the upload response for success or failure
                if ($http_code === 200) {
                    echo '<div class="notice notice-success"><p>Video uploaded successfully!</p></div>';
                } else {
                    echo '<div class="notice notice-error"><p>Failed to upload the video. Please try again.</p></div>';
                }
            } else {
                echo '<div class="notice notice-error"><p>Failed to get the video upload URL. Please try again.</p></div>';
            }
        } else {
            echo '<div class="notice notice-error"><p>Failed to communicate with Videograph AI API.</p></div>';
        }
    }

?>


<div class="wrap">
    <h1 class="wp-heading-inline">Upload Video</h1>
    <hr class="wp-header-end"><br>

    <form method="post" enctype="multipart/form-data">
        <?php wp_nonce_field( 'vg_upload_nonce', 'vg_upload_nonce' ); ?>

        <div class="form-field">
            <div id="drop_zone">
                <div class="drop-cnt">
                    <p>Drop files to upload</p>
                    <span>or</span>
                    <button class="button">Select Files</button>
                </div>
                <input type="file" name="video_file" id="video_file" style="display: none;" required >
            </div>
        </div>

        <!-- Progress bar to display the upload percentage -->
        <!-- <div class="progress-bar" style="display: none;">
            <div class="progress-bar-fill" style="width: 0%;"></div>
        </div> -->
       
        <input type="submit" class="button button-primary" value="Upload">
    </form>
</div>

    
    <?php
}
