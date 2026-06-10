/**
 * Manage Requests Admin Logic
 */

// Track the highest ID seen to detect truly new requests
let lastTopId = 0;

function initRequestsPage(initialTopId) {
    lastTopId = initialTopId;
    
    // Start polling
    setInterval(fetchUpdates, 5000);
    
    // Initial fetch to confirm
    fetchUpdates();
}

function showVerifyModal(btn) {
    const data = JSON.parse(btn.getAttribute('data-row'));
    const modal = document.getElementById('verifyModal');
    
    document.getElementById('modal_name').textContent = data.student_name;
    document.getElementById('modal_type').textContent = data.certificate_type;
    document.getElementById('modal_purpose').textContent = data.purpose;
    document.getElementById('modal_lrn').textContent = data.lrn || 'N/A';
    document.getElementById('modal_grade').textContent = data.grade_level || 'N/A';
    document.getElementById('modal_section').textContent = data.section_track || 'N/A';
    document.getElementById('modal_curriculum').textContent = data.curriculum || 'N/A';
    document.getElementById('modal_sy').textContent = data.school_year || 'N/A';
    document.getElementById('modal_bplace').textContent = data.place_of_birth || 'N/A';
    document.getElementById('modal_bdate').textContent = data.birth_date || 'N/A';
    document.getElementById('modal_address').textContent = data.address || 'N/A';
    
    document.getElementById('modal_id_img').src = data.id_image || 'https://via.placeholder.com/400x300?text=No+ID+Image';
    document.getElementById('modal_selfie_img').src = data.selfie_image || 'https://via.placeholder.com/400x300?text=No+Selfie+Image';
    
    // Make images clickable for lightbox
    document.getElementById('modal_id_img').onclick = () => openLightbox(document.getElementById('modal_id_img').src);
    document.getElementById('modal_selfie_img').onclick = () => openLightbox(document.getElementById('modal_selfie_img').src);
    document.getElementById('modal_id_img').style.cursor = 'zoom-in';
    document.getElementById('modal_selfie_img').style.cursor = 'zoom-in';
    
    // Action buttons in modal
    const approveBtn = document.getElementById('modal_approve_btn');
    const rejectBtn = document.getElementById('modal_reject_btn');
    
    if (data.certificate_type === 'FORM 137 / SF10') {
        approveBtn.href = `?action=process_sf10&id=${data.id}`;
        approveBtn.textContent = 'Approve & Start Processing (SF10)';
    } else {
        approveBtn.href = `?action=ready&id=${data.id}`;
        approveBtn.textContent = 'Approve & Ready for Pickup';
    }
    
    rejectBtn.onclick = () => showRejectModal(data.id);
    
    modal.style.display = 'flex';
}

function closeVerifyModal() {
    document.getElementById('verifyModal').style.display = 'none';
}

function openLightbox(src) {
    const lb = document.getElementById('imageLightbox');
    document.getElementById('lightboxImg').src = src;
    lb.style.display = 'flex';
}

function closeLightbox() {
    document.getElementById('imageLightbox').style.display = 'none';
}

async function showRejectModal(id) {
    const reason = await Modal.prompt("Please enter the reason for rejection:", "Reject Request", "warning");
    if (reason !== null && reason.trim() !== '') {
        window.location.href = `?action=reject&id=${id}&remarks=${encodeURIComponent(reason)}`;
    }
}

function fetchUpdates() {
    const statusLabel = document.getElementById('refreshStatus');
    const timestamp = new Date().getTime();
    const urlParams = new URLSearchParams(window.location.search);
    const currentPage = urlParams.get('page') || 1;
    const currentStatus = urlParams.get('status') || '';
    const url = `api/fetch_requests.php?page=${currentPage}&limit=20&status=${encodeURIComponent(currentStatus)}&_=${timestamp}`;
    
    fetch(url)
        .then(response => {
            if (!response.ok) throw new Error('HTTP error ' + response.status);
            return response.json();
        })
        .then(data => {
            if (statusLabel) statusLabel.textContent = 'Last checked: ' + new Date().toLocaleTimeString();

            const tbody = document.getElementById('requestsTableBody');
            if (!tbody) return;

            if (data.status === 'error') {
                console.error('API Error:', data.message);
                if (statusLabel) {
                    statusLabel.textContent = 'Connection Error: ' + data.message;
                    statusLabel.style.color = '#ff4757';
                }
                return;
            }

            if (!Array.isArray(data)) return;

            if (data.length === 0) {
                tbody.innerHTML = '<tr><td colspan="7" style="text-align: center; padding: 50px; color: var(--text-muted);">No requests found.</td></tr>';
                return;
            }

            if (statusLabel) statusLabel.style.color = '#2ed573';

            // Check for new requests
            const currentTopId = Math.max(...data.map(r => parseInt(r.id)));
            if (currentTopId > lastTopId) {
                if (lastTopId !== 0) notifyNewRequest();
                lastTopId = currentTopId;
            }

            let html = '';
            let pendingCount = 0;
            data.forEach(row => {
                if (row.status === 'Pending') pendingCount++;
                
                const date = new Date(row.created_at).toLocaleDateString('en-US', { month: 'short', day: '2-digit', year: 'numeric' });
                const statusClass = 'status-' + row.status.toLowerCase();
                
                let actionsHtml = '';
                if (row.status === 'Pending') {
                    actionsHtml = `<button class="action-btn-p btn-verify-p" onclick='showVerifyModal(this)' data-row="${JSON.stringify(row).replace(/"/g, '&quot;')}">Verify Request</button>`;
                } else if (row.status === 'Approved' || row.status === 'Processing') {
                    actionsHtml = `<a href="?action=ready&id=${row.id}" class="action-btn-p btn-ready-p">Ready for Pickup</a>`;
                } else if (row.status === 'Ready') {
                    actionsHtml = `<a href="?action=released&id=${row.id}" class="action-btn-p btn-release-p">Release Document</a>`;
                }

                html += `
                    <tr class="${row.status === 'Pending' ? 'pending-row' : ''} ${row.id > lastTopId ? 'new-request-row' : ''}">
                        <td class="cell-timestamp">${date}</td>
                        <td class="cell-mono">${row.ref_number}</td>
                        <td>
                            <div class="student-info-main">${row.student_name}</div>
                            <div class="student-info-sub">
                                <span>${row.email}</span>
                                <span>${row.contact_number}</span>
                            </div>
                        </td>
                        <td><span class="cert-badge">${row.certificate_type}</span></td>
                        <td style="max-width: 200px; font-size: 0.85rem; color: #64748b;">${row.purpose}</td>
                        <td id="status-${row.id}">
                            <span class="status-pill status-${row.status.toLowerCase()}">${row.status}</span>
                        </td>
                        <td class="actions-cell">
                            <div class="btn-container">
                                ${actionsHtml}
                            </div>
                        </td>
                    </tr>
                `;
            });
            tbody.innerHTML = html;

            // Update Pending Counter
            const counterDiv = document.getElementById('pendingCounter');
            const counterNum = document.getElementById('pendingCountNum');
            if (counterDiv && counterNum) {
                counterNum.textContent = pendingCount;
                counterDiv.style.display = pendingCount > 0 ? 'inline-block' : 'none';
            }
        })
        .catch(err => {
            console.error('Fetch Error:', err);
            if (statusLabel) {
                statusLabel.textContent = 'Offline / Connection Error';
                statusLabel.style.color = '#ff4757';
            }
        });
}

function notifyNewRequest() {
    const sound = document.getElementById('notificationSound');
    if (sound) sound.play().catch(e => console.log("Sound play blocked by browser"));
    
    const toast = document.getElementById('newRequestToast');
    if (toast) {
        toast.style.display = 'block';
        setTimeout(() => { toast.style.display = 'none'; }, 5000);
    }
}
