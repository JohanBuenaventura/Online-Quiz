// assets/js/notifications.js
document.addEventListener('DOMContentLoaded', function () {
    const bell = document.getElementById('notifBell');
    const dropdown = document.getElementById('notifDropdown');
    const list = document.getElementById('notifList');
    const countEl = document.getElementById('notifCount');
    const markBtn = document.getElementById('markReadBtn');

    if (!bell || !dropdown || !list || !countEl) return;

    // Toggle dropdown visibility
    bell.addEventListener('click', (e) => {
        e.stopPropagation();
        dropdown.classList.toggle('show'); // match your CSS
    });

    document.addEventListener('click', (e) => {
        if (!document.getElementById('notifArea').contains(e.target)) {
            dropdown.classList.remove('show');
        }
    });

    // Escape HTML to prevent XSS
    function escapeHtml(s) {
        if (!s) return '';
        return s.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;");
    }

    // Render notifications
    function render(items) {
        list.innerHTML = '';
        if (!items || items.length === 0) {
            list.innerHTML = '<div class="notif-item">No notifications</div>';
            countEl.style.display = 'none';
            return;
        }

        let unread = 0;
        items.forEach(i => {
            const div = document.createElement('div');
            div.className = 'notif-item' + (i.is_read == 0 ? ' unread' : '');
            div.innerHTML = `<strong>${escapeHtml(i.title)}</strong><br>
                             <small>${escapeHtml(i.message)}</small>`;
            list.appendChild(div);
            if (i.is_read == 0) unread++;
        });

        if (unread > 0) {
            countEl.style.display = 'inline-block';
            countEl.textContent = unread;
        } else {
            countEl.style.display = 'none';
        }
    }

    // Fetch notifications from server
    function load() {
        fetch('../includes/ajax_get_notif.php', {credentials:'include'}) // adjust path as needed
            .then(r => r.json())
            .then(data => render(data))
            .catch(err => {
                console.error('Failed to fetch notifications:', err);
                list.innerHTML = '<div class="notif-item">Error loading notifications</div>';
            });
    }

    // Mark all as read
    function markAll() {
        fetch('../includes/ajax_mark_read.php', {method:'POST', credentials:'include'})
            .then(() => load())
            .catch(err => console.error('Failed to mark notifications as read:', err));
    }

    markBtn && markBtn.addEventListener('click', markAll);

    // Initial load
    load();

    // Auto-refresh every 5 seconds
    setInterval(load, 5000);
});
