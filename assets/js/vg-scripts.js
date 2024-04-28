/* videograph-ai-scripts.js */
document.addEventListener('DOMContentLoaded', function () {
    const dropZone = document.getElementById('drop_zone');
    const videoFileInput = document.getElementById('video_file');
    if (dropZone) {
        // Prevent the default behavior when a file is dragged over the drop zone
        dropZone.addEventListener('dragover', function (event) {
            event.preventDefault();
            dropZone.style.border = '2px dashed #666';
        });

        // Restore the drop zone style when the dragged file leaves the drop zone
        dropZone.addEventListener('dragleave', function () {
            dropZone.style.border = '2px dashed #ccc';
        });

        // Handle the file when it is dropped onto the drop zone
        dropZone.addEventListener('drop', function (event) {
            event.preventDefault();
            dropZone.style.border = '2px dashed #ccc';

            // Get the dropped file
            const file = event.dataTransfer.files[0];

            // Check if the file is a video
            if (file.type.startsWith('video/')) {
                // Assign the dropped file to the video_file input
                videoFileInput.files = event.dataTransfer.files;
                updateDropZoneText(file.name);
            } else {
                alert('Please drop a valid video file.');
            }
        });

        // Handle the click event on the drop zone to trigger the file input click
        dropZone.addEventListener('click', function () {
            videoFileInput.click();
        });
    }


    if (videoFileInput) {
        // Listen for changes on the file input and update the drop zone text accordingly
        videoFileInput.addEventListener('change', function () {
            const files = videoFileInput.files;
            if (files.length > 0) {
                updateDropZoneText(files[0].name);
            } else {
                dropZone.querySelector('p').textContent = 'Drag and drop video file here or click to select from local storage.';
            }
        });
    }

    // Function to update the drop zone text with the selected file name
    function updateDropZoneText(fileName) {
        dropZone.querySelector('p').textContent = `${fileName}`;
    }

    const liveStreamForm = document.getElementById('live-stream-form');
    if (liveStreamForm) {
        liveStreamForm.addEventListener('submit', function () {
            document.getElementById('loader').style.display = 'block';
        });
    }
});