<style>
    .system-credit-footer {
        width: 100%;
        text-align: center;
        padding: 20px 0;
        margin-top: auto;
        font-size: 0.85rem;
        color: rgba(255, 255, 255, 0.4);
        font-family: 'Outfit', 'Inter', sans-serif;
        letter-spacing: 0.5px;
        text-shadow: 0 1px 3px rgba(0, 0, 0, 0.3);
    }

    /* Adapting for pages that might have light backgrounds instead of the dark body background */
    @media (prefers-color-scheme: light) {
        body:not(.dark-mode) .system-credit-footer {
            /* Defaults handle this fine based on background */
        }
    }

    /* Print hide */
    @media print {
        .system-credit-footer {
            display: none !important;
        }
    }
</style>
<div class="system-credit-footer no-print">
    System Developed by John Wisdom Deguit | OJT of COMTEQ COMPUTER AND BUSINESS COLLEGE
</div>

<script>
    // Magnetic button effects (Senior Refactor: Simplified & Global Selectors)
    document.querySelectorAll('.action-btn-primary, .action-btn-outline, .view-btn').forEach(btn => {
        btn.addEventListener('mousemove', function (e) {
            const rect = this.getBoundingClientRect();
            const x = (e.clientX - rect.left) - (rect.width / 2);
            const y = (e.clientY - rect.top) - (rect.height / 2);
            this.style.transform = `translate(${x * 0.1}px, ${y * 0.1}px)`;
        });
        btn.addEventListener('mouseleave', function () {
            this.style.transform = 'translate(0px, 0px)';
        });
    });
</script>
</body>

</html>