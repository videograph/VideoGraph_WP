<?php

// Validation function for API keys
function videograph_validate_api_keys($access_token, $secret_key) {
    // API URL for checking API keys
    $api_url = 'https://api.videograph.ai/video/services/api/v1/contents';

    // Headers for the API request
    $headers = array(
        'Authorization' => 'Basic ' . base64_encode($access_token . ':' . $secret_key),
        'Content-Type' => 'application/json',
    );

    // Perform API request to check the validity of API keys
    $response = wp_remote_get($api_url, array('headers' => $headers));

    if (is_wp_error($response)) {
        return 'Failed to fetch videos from Videograph AI API.';
    }

    $response_code = wp_remote_retrieve_response_code($response);

    if ($response_code === 200) {
        return 'success';
    } else {
        return 'Failed to fetch videos from Videograph AI API. Check your API Credentials.';
    }
}


// Settings page
function videograph_settings()
{
    // Nonce verification
    if (isset($_POST['videograph_save_settings']) || isset($_POST['videograph_reset_settings'])) {
        if (!isset($_POST['videograph_settings_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['videograph_settings_nonce'])), 'videograph_settings_nonce')) {
            // Nonce verification failed, do not proceed
            return;
        }
    }

    // Save settings or reset settings
    if (isset($_POST['videograph_save_settings'])) {
        // Nonce verification passed, continue with saving settings
        // Save settings
        $access_token = '';

        if (isset($_POST['videograph_access_token'])) {
            $access_token = sanitize_text_field(wp_unslash($_POST['videograph_access_token']));
        }
        
        $secret_key = '';

if (isset($_POST['videograph_secret_key'])) {
    $secret_key = sanitize_text_field(wp_unslash($_POST['videograph_secret_key']));
}

        $keys_connected = !empty($access_token) && !empty($secret_key);

        // Perform API key validation
        $validation_result = videograph_validate_api_keys($access_token, $secret_key);

        // Save the settings if the keys are valid
        if ($validation_result === 'success') {
            update_option('videograph_access_token', $access_token);
            update_option('videograph_secret_key', $secret_key);
            echo '<div class="notice notice-success"><p>' . esc_html__('Connected Successfully', 'videograph') . '</p></div>';

            // Enable the reset button
            echo '<script>document.getElementById("videograph_reset_settings").disabled = false;</script>';
            echo '<script>document.getElementById("videograph_save_settings").disabled = true;</script>';
        } else {
            // If the keys are invalid, store the entered values in a variable
            $entered_access_token = $access_token;
            $entered_secret_key = $secret_key;

            // Clear the old values from the settings
            update_option('videograph_access_token', '');
            update_option('videograph_secret_key', '');
        }
    } elseif (isset($_POST['videograph_reset_settings'])) {
        // Reset settings
        update_option('videograph_access_token', '');
        update_option('videograph_secret_key', '');
        echo '<div class="notice notice-success"><p>Settings reset.</p></div>';

        // Disable the reset button again
        echo '<script>document.getElementById("videograph_reset_settings").disabled = true;</script>';
        echo '<script>document.getElementById("videograph_save_settings").disabled = false;</script>';
    }

    // Retrieve the saved settings
    $access_token = get_option('videograph_access_token');
    $secret_key = get_option('videograph_secret_key');

    // Initially disable the reset button if API keys are not connected
    $reset_button_disabled = empty($access_token) && empty($secret_key) ? 'disabled' : '';
    $connect_button_disabled = !empty($access_token) && !empty($secret_key) ? 'disabled' : '';
    ?>

    <div class="wrap vg-wrap">
        <h1 class="wp-heading-inline"><?php esc_html_e('Videograph AI API Key', 'videograph'); ?></h1>
           <div class="notification-cnt">
                <p>
                    <a href="<?php echo esc_url('https://www.videograph.ai/'); ?>" target="_blank">
                        <?php esc_html_e('Videograph.ai', 'videograph'); ?>
                    </a> 
                    <?php esc_html_e('offers robust video infrastructure tailored for product builders.', 'videograph'); ?>
                </p>
                
                <p>
                    <?php esc_html_e('Utilize', 'videograph'); ?>
                    <a href="<?php echo esc_url('https://www.videograph.ai/'); ?>" target="_blank">
                        <?php esc_html_e("Videograph.ai's", 'videograph'); ?>
                    </a>
                    <?php esc_html_e("lightning-fast video APIs to seamlessly integrate, expand, and efficiently manage on-demand and low-latency live streaming capabilities on your WordPress site.", 'videograph'); ?>
                </p>

                <p>
                    <?php esc_html_e("If you haven't obtained an API key yet, you can easily register for an account on", 'videograph'); ?>
                    <a href="<?php echo esc_url('https://www.videograph.ai/'); ?>" target="_blank">
                        <?php esc_html_e('Videograph.ai', 'videograph'); ?>
                    </a>.
                </p>

                <p>
                    <?php esc_html_e('Discover the process of generating an access token ID and secret key by', 'videograph'); ?>
                    <a href="<?php echo esc_url('https://docs.videograph.ai/docs/authentication-authorization'); ?>" target="_blank">
                        <?php esc_html_e('clicking here', 'videograph'); ?>
                    </a>.
                </p>
            </div>

        <hr class="wp-header-end">

        <div class="livestrea-wrap">
            <div class="settings-form">
                <form method="post" action="">
                    <?php wp_nonce_field('videograph_settings_nonce', 'videograph_settings_nonce'); ?>
                    <table class="form-table" role="presentation">
                        <tbody>
                            <tr>
                                <th>
                                    <input type="text" id="videograph_access_token" name="videograph_access_token" value="<?php echo isset($entered_access_token) ? esc_attr($entered_access_token) : esc_attr($access_token); ?>" class="regular-text" placeholder="Enter Access Token ID" <?php if (empty($secret_key) || isset($entered_secret_key)) { echo ''; } else { echo 'readonly'; } ?>>
                                    <?php
                                    if (isset($entered_access_token)) {
                                        echo '<span class="error-msg access-token-error-msg">Access token ID is not added / valid.</span>';
                                    } elseif (empty($access_token)) {
                                        echo '';
                                    } else {
                                        echo '<span class="success-msg"><span class="dashicons dashicons-yes-alt"></span></span>';
                                    }
                                    ?>
                                </th>
                            </tr>
                            <tr>
                                <th>
                                    <input type="text" id="videograph_secret_key" name="videograph_secret_key" value="<?php echo isset($entered_secret_key) ? esc_attr($entered_secret_key) : esc_attr($secret_key); ?>" class="regular-text" placeholder="Enter Secret Key" <?php if (empty($secret_key) || isset($entered_secret_key)) { echo ''; } else { echo 'readonly'; } ?>>
                                    <?php
                                    if (isset($entered_secret_key)) {
                                        echo '<span class="error-msg secret-key-error-msg">Access token ID is not added / valid.</span>';
                                    } elseif ( empty($secret_key)) {
                                        echo '';
                                    } else {
                                        echo '<span class="success-msg"><span class="dashicons dashicons-yes-alt"></span></span>';
                                    }
                                    ?>
                                </th>
                            </tr>
                        </tbody>
                    </table>
                    <p class="submit">
                        <button type="submit" name="videograph_save_settings" class="button button-primary" id="videograph_save_settings" <?php echo esc_html($connect_button_disabled); ?>><?php esc_html_e('Connect', 'videograph'); ?></button>
                        <button type="submit" name="videograph_reset_settings" class="button" id="videograph_reset_settings" <?php echo esc_html($reset_button_disabled); ?>><?php esc_html_e('Reset', 'videograph'); ?></button>
                    </p>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const accessInput = document.getElementById('videograph_access_token');
            const secretInput = document.getElementById('videograph_secret_key');
            const connectButton = document.getElementById('videograph_save_settings');
            const resetButton = document.getElementById('videograph_reset_settings');

            // Function to handle input events
            function handleInput() {
                // Check if both fields are empty
                const accessValue = accessInput.value.trim();
                const secretValue = secretInput.value.trim();

                if (accessValue === '' || secretValue === '' || document.querySelector('.success-msg')) {
                    // At least one field is empty or there's a success message, disable the button
                    connectButton.disabled = true;
                } else {
                    connectButton.disabled = false;
                }
            }

            // Initially check and disable if both fields are empty
            handleInput();

            accessInput.addEventListener('input', videograph_clear_access_error_message);
            secretInput.addEventListener('input', videograph_clear_secret_error_message);
            accessInput.addEventListener('input', handleInput);
            secretInput.addEventListener('input', handleInput);

            function videograph_clear_access_error_message() {
                const accessErrorMessage = document.querySelector('.access-token-error-msg');
                if (accessErrorMessage) {
                    accessErrorMessage.style.display = 'none';
                }
            }

            function videograph_clear_secret_error_message() {
                const secretErrorMessage = document.querySelector('.secret-key-error-msg');
                if (secretErrorMessage) {
                    secretErrorMessage.style.display = 'none';
                }
            }
        });
    </script>

    <?php
}
?>