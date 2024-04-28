jQuery(document).ready(function($) {
            $('#upload-button').click(function() {
                var formData = new FormData();
                formData.append('video_file', $('#video-file')[0].files[0]);

                $.ajax({
                    url: 'https://api.videograph.ai/video/services/api/v1/uploads',
                    type: 'POST',
                    data: formData,
                    contentType: false,
                    processData: false,
                    headers: {
                        'Authorization': $('#authorization-header').val()
                    },
                    xhr: function() {
                        var xhr = new window.XMLHttpRequest();
                        xhr.upload.addEventListener("progress", function(evt) {
                            if (evt.lengthComputable) {
                                var percentComplete = (evt.loaded / evt.total) * 100;
                                $('#upload-progress').val(percentComplete);
                            }
                        }, false);
                        return xhr;
                    },
                    success: function(response) {
                        // Handle the response here
                        console.log(response);
                    },
                    error: function(xhr, status, error) {
                        // Handle errors here
                        console.error(error);
                    }
                });
            });
        });