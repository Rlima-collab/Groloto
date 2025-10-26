console.log('Notifications script loaded');

function toggleNotifications(event) {
    event.preventDefault();
    event.stopPropagation();
    console.log('toggleNotifications called');

    const dropdown = document.getElementById('notificationDropdown');
    const settingsMenu = document.getElementById('settingsMenu');

    if (settingsMenu) {
        settingsMenu.style.display = 'none';
        console.log('Settings menu hidden');
    }

    if (dropdown) {
        if (dropdown.style.display === 'block') {
            dropdown.style.display = 'none';
            dropdown.classList.remove('show-animation');
            console.log('Notification dropdown hidden');
        } else {
            dropdown.style.display = 'block';
            dropdown.classList.add('show-animation');
            console.log('Notification dropdown shown');
        }
    } else {
        console.error('Notification dropdown not found');
    }
}

document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM fully loaded');
    const notificationBell = document.getElementById('notificationBell');
    const settingsToggle = document.getElementById('settingsToggle');

    if (notificationBell) {
        notificationBell.addEventListener('click', toggleNotifications);
        console.log('Notification bell listener attached');
    } else {
        console.error('Notification bell not found');
    }

    if (settingsToggle) {
        settingsToggle.addEventListener('click', function(event) {
            event.preventDefault();
            event.stopPropagation();
            console.log('toggleSettingsMenu called');
            const menu = document.getElementById('settingsMenu');
            const notificationDropdown = document.getElementById('notificationDropdown');
            if (notificationDropdown) {
                notificationDropdown.style.display = 'none';
                notificationDropdown.classList.remove('show-animation');
                console.log('Notification dropdown hidden');
            }
            if (menu) {
                menu.style.display = menu.style.display === 'block' ? 'none' : 'block';
                console.log('Settings menu toggled');
            } else {
                console.error('Settings menu not found');
            }
        });
        console.log('Settings toggle listener attached');
    } else {
        console.error('Settings toggle not found');
    }

    document.addEventListener('click', function(event) {
        const notificationDropdown = document.getElementById('notificationDropdown');
        const notificationWrapper = document.querySelector('.notification-icon-wrapper');
        const settingsMenu = document.getElementById('settingsMenu');
        const settingsDropdown = document.querySelector('.settings-dropdown');

        if (notificationDropdown && notificationWrapper && !notificationWrapper.contains(event.target)) {
            notificationDropdown.style.display = 'none';
            notificationDropdown.classList.remove('show-animation');
            console.log('Notification dropdown closed due to outside click');
        }

        if (settingsMenu && settingsDropdown && !settingsDropdown.contains(event.target)) {
            settingsMenu.style.display = 'none';
            console.log('Settings menu closed due to outside click');
        }
    });
});