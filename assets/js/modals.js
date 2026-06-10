/**
 * Custom Modal System Logic
 */

const Modal = {
    overlay: null,
    content: null,
    title: null,
    message: null,
    input: null,
    buttons: null,

    init() {
        if (this.overlay) return;

        // Create modal elements
        const overlay = document.createElement('div');
        overlay.className = 'custom-modal-overlay';
        overlay.innerHTML = `
            <div class="custom-modal-content">
                <div class="modal-icon" id="modalIcon">🔔</div>
                <div class="custom-modal-title" id="modalTitle">Notification</div>
                <div class="custom-modal-msg" id="modalMsg">Message goes here...</div>
                <input type="text" class="modal-input" id="modalInput" style="display: none;" placeholder="Enter details...">
                <div class="modal-buttons" id="modalButtons">
                    <button class="modal-btn modal-btn-primary" id="modalConfirm">OK</button>
                </div>
            </div>
        `;

        document.body.appendChild(overlay);
        this.overlay = overlay;
        this.title = document.getElementById('modalTitle');
        this.message = document.getElementById('modalMsg');
        this.input = document.getElementById('modalInput');
        this.buttons = document.getElementById('modalButtons');
        this.confirmBtn = document.getElementById('modalConfirm');
        this.icon = document.getElementById('modalIcon');
    },

    alert(msg, title = 'Notification', type = 'info') {
        this.init();
        this.title.textContent = title;
        this.message.textContent = msg;
        this.input.style.display = 'none';
        this.buttons.innerHTML = '<button class="modal-btn modal-btn-primary">OK</button>';
        
        const btn = this.buttons.querySelector('button');
        
        this.setIcon(type);
        this.overlay.classList.add('active');

        return new Promise((resolve) => {
            btn.onclick = () => {
                this.overlay.classList.remove('active');
                resolve();
            };
        });
    },

    confirm(msg, title = 'Are you sure?', type = 'warning') {
        this.init();
        this.title.textContent = title;
        this.message.textContent = msg;
        this.input.style.display = 'none';
        this.buttons.innerHTML = `
            <button class="modal-btn modal-btn-secondary" id="mCancel">Cancel</button>
            <button class="modal-btn modal-btn-primary" id="mConfirm">Yes, Continue</button>
        `;
        
        this.setIcon(type);
        this.overlay.classList.add('active');

        return new Promise((resolve) => {
            document.getElementById('mCancel').onclick = () => {
                this.overlay.classList.remove('active');
                resolve(false);
            };
            document.getElementById('mConfirm').onclick = () => {
                this.overlay.classList.remove('active');
                resolve(true);
            };
        });
    },

    prompt(msg, title = 'Input Required', type = 'info') {
        this.init();
        this.title.textContent = title;
        this.message.textContent = msg;
        this.input.style.display = 'block';
        this.input.value = '';
        this.buttons.innerHTML = `
            <button class="modal-btn modal-btn-secondary" id="mCancel">Cancel</button>
            <button class="modal-btn modal-btn-primary" id="mConfirm">Submit</button>
        `;
        
        this.setIcon(type);
        this.overlay.classList.add('active');
        this.input.focus();

        return new Promise((resolve) => {
            document.getElementById('mCancel').onclick = () => {
                this.overlay.classList.remove('active');
                resolve(null);
            };
            document.getElementById('mConfirm').onclick = () => {
                const val = this.input.value;
                this.overlay.classList.remove('active');
                resolve(val);
            };
            this.input.onkeyup = (e) => {
                if (e.key === 'Enter') document.getElementById('mConfirm').click();
            };
        });
    },

    setIcon(type) {
        this.icon.className = 'modal-icon ' + type;
        switch(type) {
            case 'success': this.icon.textContent = '✅'; break;
            case 'error': this.icon.textContent = '❌'; break;
            case 'warning': this.icon.textContent = '⚠️'; break;
            default: this.icon.textContent = 'ℹ️'; break;
        }
    }
};

// Global shorthand
window.swal = (title, msg, type) => Modal.alert(msg, title, type);
