<?php

// Settings page
function vg_settings()
{
    // Save settings or reset settings
    if (isset($_POST['vg_save_settings']) && wp_verify_nonce($_POST['vg_settings_nonce'], 'vg_settings_action')) {
        // Save settings
        $access_token = sanitize_text_field($_POST['vg_access_token']);
        $secret_key = sanitize_text_field($_POST['vg_secret_key']);
        $keys_connected = !empty($access_token) && !empty($secret_key);

        // Perform API key validation
        $validation_result = vg_validate_api_keys($access_token, $secret_key);

        // Save the settings if the keys are valid
        if ($validation_result === 'success') {
            update_option('vg_access_token', $access_token);
            update_option('vg_secret_key', $secret_key);
            echo '<div class="notice notice-success"><p>Connected Successfully</p></div>';

            // Enable the reset button
            echo '<script>document.getElementById("vg_reset_settings").disabled = false;</script>';

            echo '<script>document.getElementById("vg_save_settings").disabled = true;</script>';
        } else {
            // If the keys are invalid, store the entered values in a variable
            $entered_access_token = $access_token;
            $entered_secret_key = $secret_key;

            // Clear the old values from the settings
            update_option('vg_access_token', '');
            update_option('vg_secret_key', '');
        }
    } elseif (isset($_POST['vg_reset_settings']) && wp_verify_nonce($_POST['vg_settings_nonce'], 'vg_settings_action')) {
        // Reset settings
        update_option('vg_access_token', '');
        update_option('vg_secret_key', '');
        echo '<div class="notice notice-success"><p>Settings reset.</p></div>';

        // Disable the reset button again
        echo '<script>document.getElementById("vg_reset_settings").disabled = true;</script>';

        echo '<script>document.getElementById("vg_save_settings").disabled = false;</script>';
    }

    // Retrieve the saved settings
    $access_token = get_option('vg_access_token');
    $secret_key = get_option('vg_secret_key');

    // Initially disable the reset button if API keys are not connected
    $reset_button_disabled = empty($access_token) && empty($secret_key) ? 'disabled' : '';
    $connect_button_disabled = !empty($access_token) && !empty($secret_key) ? 'disabled' : '';

    // Nonce field
    $nonce_field = wp_nonce_field('vg_settings_action', 'vg_settings_nonce');
    ?>

    <div class="wrap">
        <h1 class="wp-heading-inline">Videograph.ai  API key</h1>
        <div class="notification-cnt">
            <p><a href="https://www.videograph.ai/" target="_blank">Videograph.ai</a> offers robust video infrastructure tailored for product builders.</p>
            <p>Utilize <a href="https://www.videograph.ai/" target="_blank">Videograph.ai</a>'s lightning-fast video APIs to seamlessly integrate, expand, and efficiently manage on-demand and low-latency live streaming capabilities on your WordPress site.</p>
            <p>If you haven't obtained an API key yet, you can easily register for an account on <a href="https://www.videograph.ai/" target="_blank">Videograph.ai</a>.</p>
            <p>Discover the process of generating an access token ID and secret key by <a href="https://docs.videograph.ai/docs/authentication-authorization" target="_blank">clicking here</a>.</p>
        </div>
        <hr class="wp-header-end">

        <div class="livestrea-wrap">
            <div class="settings-form">
                <form method="post" action="">
                    <?php echo esc_html($nonce_field); ?>
                    <table class="form-table" role="presentation">
                        <tbody>
                            <tr>
                                <th>
                                    <input type="text" id="vg_access_token" name="vg_access_token" value="<?php echo isset($entered_access_token) ? esc_attr($entered_access_token) : esc_attr($access_token); ?>" class="regular-text" placeholder="Enter Access Token ID" <?php if (empty($secret_key) || isset($entered_secret_key)) { echo ''; } else { echo 'readonly'; } ?>>
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
                                    <input type="text" id="vg_secret_key" name="vg_secret_key" value="<?php echo isset($entered_secret_key) ? esc_attr($entered_secret_key) : esc_attr($secret_key); ?>" class="regular-text" placeholder="Enter Secret Key" <?php if (empty($secret_key) || isset($entered_secret_key)) { echo ''; } else { echo 'readonly'; } ?>>
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
                        <button type="submit" name="vg_save_settings" class="button button-primary" id="vg_save_settings" <?php echo esc_html($connect_button_disabled); ?>>Connect</button>
                        <button type="submit" name="vg_reset_settings" class="button" id="vg_reset_settings" <?php echo esc_html($reset_button_disabled); ?>>Reset</button>
                    </p>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const accessInput = document.getElementById('vg_access_token');
            const secretInput = document.getElementById('vg_secret_key');
            const connectButton = document.getElementById('vg_save_settings');
            const resetButton = document.getElementById('vg_reset_settings');

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

            accessInput.addEventListener('input', clearAccessErrorMessage);
            secretInput.addEventListener('input', clearSecretErrorMessage);
            accessInput.addEventListener('input', handleInput);
            secretInput.addEventListener('input', handleInput);

            function clearAccessErrorMessage() {
                const accessErrorMessage = document.querySelector('.access-token-error-msg');
                if (accessErrorMessage) {
                    accessErrorMessage.style.display = 'none';
                }
            }

            function clearSecretErrorMessage() {
                const secretErrorMessage = document.querySelector('.secret-key-error-msg');
                if (secretErrorMessage) {
                    secretErrorMessage.style.display = 'none';
                }
            }

        });
    </script>

<?php
}
function vg_validate_api_keys($access_token, $secret_key) {
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
