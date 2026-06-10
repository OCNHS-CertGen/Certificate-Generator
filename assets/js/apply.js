/**
 * Online Application Form Logic
 */

function updateProgress(step) {
    const totalSteps = 3;
    const progress = (step / totalSteps) * 100;
    const progressBar = document.getElementById('formProgress');
    if (progressBar) progressBar.style.width = progress + '%';

    document.querySelectorAll('.step').forEach(s => {
        if (parseInt(s.dataset.step) <= step) s.classList.add('active');
        else s.classList.remove('active');
    });
}

function nextStep(step) {
    const currentSection = document.querySelector('.form-section.active');
    const inputs = currentSection.querySelectorAll('input[required], select[required], textarea[required]');
    let valid = true;

    inputs.forEach(input => {
        if (!input.value) {
            input.style.borderColor = 'red';
            valid = false;
        } else {
            input.style.borderColor = '';
        }
    });

    if (!valid) {
        Modal.alert('Please fill in all required fields.', 'Validation Error', 'warning');
        return;
    }

    document.querySelectorAll('.form-section').forEach(s => s.classList.remove('active'));
    const targetSection = document.getElementById('step' + step);
    if (targetSection) targetSection.classList.add('active');
    updateProgress(step);
}

function prevStep(step) {
    document.querySelectorAll('.form-section').forEach(s => s.classList.remove('active'));
    const targetSection = document.getElementById('step' + step);
    if (targetSection) targetSection.classList.add('active');
    updateProgress(step);
}

function toggleTracker() {
    const tracker = document.getElementById('statusTracker');
    if (tracker) {
        tracker.style.display = tracker.style.display === 'none' ? 'block' : 'none';
    }
}

function checkStatus() {
    const ref = document.getElementById('track_ref').value;
    const resultDiv = document.getElementById('statusResult');

    if (!ref) return Modal.alert('Mangyaring ilagay ang iyong Reference Number.', 'Input Required', 'warning');

    resultDiv.style.display = 'block';
    resultDiv.style.background = '#eee';
    resultDiv.innerHTML = 'Searching...';

    fetch(`api/check_status.php?ref=${ref}`)
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                let displayStatus = data.request_status;
                let bgColor = '#e2e3e5';

                if (data.request_status === 'Pending') {
                    displayStatus = 'Pending Verification';
                    bgColor = '#fff3cd';
                } else if (data.request_status === 'Processing') {
                    displayStatus = 'Processing';
                    bgColor = '#cce5ff';
                } else if (data.request_status === 'Ready' || data.request_status === 'Approved') {
                    displayStatus = 'Ready for Release / Pickup';
                    bgColor = '#d4edda';
                } else if (data.request_status === 'Rejected') {
                    displayStatus = 'Rejected (Please contact the office)';
                    bgColor = '#f8d7da';
                } else if (data.request_status === 'Released') {
                    displayStatus = 'Claimed / Released';
                    bgColor = '#d4edda';
                }

                resultDiv.innerHTML = `STATUS: <strong>${displayStatus}</strong>`;
                resultDiv.style.background = bgColor;
            } else {
                resultDiv.innerHTML = 'Reference Number not found.';
                resultDiv.style.background = '#f8d7da';
            }
        })
        .catch(err => {
            resultDiv.innerHTML = 'Error connecting to server.';
            resultDiv.style.background = '#f8d7da';
        });
}

// Initialization
document.addEventListener('DOMContentLoaded', function () {
    updateProgress(1);
});
