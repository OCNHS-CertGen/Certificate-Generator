/**
 * Dashboard Real-time Logic
 */

let lastTopId = 0;

// Initialize top ID to prevent notifications for existing requests on first load
function initDashboard() {
    fetch('api/fetch_requests.php?limit=1')
        .then(res => res.json())
        .then(data => {
            if (Array.isArray(data) && data.length > 0) {
                lastTopId = Math.max(...data.map(r => parseInt(r.id)));
            }
        })
        .catch(err => console.error('Init Error:', err));

    // Poll for updates every 5 seconds
    setInterval(checkDashboardUpdates, 5000);
}

function checkDashboardUpdates() {
    fetch('api/fetch_requests.php?_=' + new Date().getTime())
        .then(res => res.json())
        .then(data => {
            if (!Array.isArray(data)) return;

            // Update Pending Badge
            const pendingCount = data.filter(r => r.status === 'Pending').length;
            const badge = document.getElementById('pendingBadge');
            if (badge) {
                badge.textContent = pendingCount;
                badge.style.display = pendingCount > 0 ? 'inline-block' : 'none';
            }

            // Check for Truly New Requests
            const currentTopId = data.length > 0 ? Math.max(...data.map(r => parseInt(r.id))) : 0;
            
            if (currentTopId > lastTopId && lastTopId !== 0) {
                showDashboardNotification();
                lastTopId = currentTopId;
            } else if (lastTopId === 0) {
                lastTopId = currentTopId;
            }
        })
        .catch(err => console.error('Dashboard Sync Error:', err));
}

function showDashboardNotification() {
    const sound = document.getElementById('notificationSound');
    if (sound) sound.play().catch(e => console.log("Audio playback blocked"));

    const toast = document.getElementById('newRequestToast');
    if (toast) {
        toast.style.display = 'block';
        setTimeout(() => {
            toast.style.display = 'none';
        }, 5000);
    }
}

// Interactive Card Logic
document.addEventListener('DOMContentLoaded', function () {
    initDashboard();

    const interactiveCards = document.querySelectorAll('.interactive-group');
    interactiveCards.forEach(card => {
        card.addEventListener('click', function (e) {
            // Don't toggle if clicking a link inside
            if (e.target.closest('.sub-options a')) return;
            this.classList.toggle('active');
        });
    });
});
