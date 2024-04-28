jQuery(document).ready(function ($) {
    $('#video_file').change(function () {
        // Show the progress bar
        $('.progress-bar').show();

        // Create a FormData object and append the selected file
        var formData = new FormData();
        formData.append('video_file', $('#video_file')[0].files[0]);

        // Perform an AJAX request to upload the video
        $.ajax({
            url: ajaxurl, // Use the WordPress AJAX URL
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            xhr: function () {
                // Create an XHR object with progress tracking
                var xhr = $.ajaxSettings.xhr();
                xhr.upload.onprogress = function (e) {
                    if (e.lengthComputable) {
                        // Calculate and update the upload progress
                        var percentComplete = (e.loaded / e.total) * 100;
                        $('.progress-bar-fill').css('width', percentComplete + '%');
                    }
                };
                return xhr;
            },
            success: function (response) {
                if (response.success) {
                    // Handle a successful upload
                    var status = response.data.status;
                    if (status === 'Waiting') {
                        // Video is waiting
                        $('.progress-bar-fill').css('width', '100%');
                        $('.progress-bar-fill').text('Waiting...');
                        // Periodically check the video status using another AJAX request
                        checkVideoStatus(response.data.upload_id);
                    } else if (status === 'Processing') {
                        // Video is processing
                        $('.progress-bar-fill').text('Processing...');
                    } else if (status === 'Ready') {
                        // Video is ready
                        $('.progress-bar-fill').css('width', '100%');
                        $('.progress-bar-fill').text('Ready');
                    }
                } else {
                    // Handle an error response
                    console.error('Video upload failed: ' + response.data.message);
                }
            },
            error: function (error) {
                // Handle AJAX errors
                console.error('AJAX error: ' + error.responseText);
            },
        });
    });

    function checkVideoStatus(uploadId) {
        $.ajax({
            url: ajaxurl, // Use the WordPress AJAX URL
            type: 'POST',
            data: {
                action: 'check_video_status',
                upload_id: uploadId,
            },
            success: function (response) {
                if (response.success) {
                    var status = response.data.status;
                    if (status === 'Processing') {
                        // Video is still processing, check again after a delay
                        setTimeout(function () {
                            checkVideoStatus(uploadId);
                        }, 5000); // Check every 5 seconds
                    } else if (status === 'Ready') {
                        // Video is ready
                        $('.progress-bar-fill').css('width', '100%');
                        $('.progress-bar-fill').text('Ready');
                    }
                } else {
                    // Handle an error response
                    console.error('Failed to check video status: ' + response.data.message);
                }
            },
            error: function (error) {
                // Handle AJAX errors
                console.error('AJAX error: ' + error.responseText);
            },
        });
    }
});
