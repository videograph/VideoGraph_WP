function videograph_validate_title(input) {
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
function videograph_validate_fields() {
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
