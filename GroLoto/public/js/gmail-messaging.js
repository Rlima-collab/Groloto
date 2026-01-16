document.addEventListener('DOMContentLoaded', function () {
    const messageItems = document.querySelectorAll('.gmail-message-item');
    const filterTabs = document.querySelectorAll('.filter-tab');
    const messagesList = document.getElementById('messagesList');

    // Toggle message full view
    messageItems.forEach(item => {
        const preview = item.querySelector('.gmail-message-preview');
        const full = item.querySelector('.gmail-message-full');
        const replyBtn = item.querySelector('.gmail-reply-btn');
        const replyForm = item.querySelector('.gmail-reply-form');

        preview.addEventListener('click', function () {
            const isOpen = full.style.display === 'block';
            // Close all
            document.querySelectorAll('.gmail-message-full').forEach(f => f.style.display = 'none');
            document.querySelectorAll('.gmail-reply-form').forEach(f => f.style.display = 'none');
            // Open this one
            if (!isOpen) {
                full.style.display = 'block';
            }
        });

        if (replyBtn) {
            replyBtn.addEventListener('click', function (e) {
                e.stopPropagation();
                replyForm.style.display = replyForm.style.display === 'block' ? 'none' : 'block';
            });
        }
    });

    // Filter tabs
    filterTabs.forEach(tab => {
        tab.addEventListener('click', function () {
            filterTabs.forEach(t => t.classList.remove('active'));
            this.classList.add('active');
            const filter = this.getAttribute('data-filter');

            messageItems.forEach(item => {
                const itemFilter = item.getAttribute('data-filter');
                if (filter === 'all' || itemFilter === filter || (filter === 'unread' && item.classList.contains('unread'))) {
                    item.style.display = 'block';
                } else {
                    item.style.display = 'none';
                }
            });
        });
    });
});