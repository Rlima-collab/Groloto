console.log('Notifications script loaded');

// Configuration de la base URL - utiliser la variable globale si disponible
const APP_BASE_URL = typeof window.APP_BASE_URL !== 'undefined' ? window.APP_BASE_URL : '';

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
    // --- Modal & view handling ---
    const modalOverlay = document.getElementById('notificationModal');
    const modalMessage = document.getElementById('modalMessage');
    const replySection = document.getElementById('replySection');
    const replyTextarea = replySection ? replySection.querySelector('textarea') : null;
    const replyNotifId = document.getElementById('replyNotifId');
    const sendReply = document.getElementById('sendReply');
    const cancelReply = document.getElementById('cancelReply');
    const closeModal = document.getElementById('closeModal');
    const closeBtn = document.getElementById('closeModalBtn');

    let currentMessageLink = null;
    function openModalFromCard(card) {
        const type = card.dataset.type;
        const link = card.dataset.link || null;
        currentMessageLink = link;
        // If modal overlay exists, show modal; otherwise populate card inline
        if (modalOverlay) {
            // If there's a link to /contact/message/{id}, fetch details
            if (link && link.includes('/contact/message/')) {
                fetch(link, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                    .then(r => r.json())
                    .then(data => {
                        if (data.error) {
                            modalMessage.textContent = 'Accès refusé.';
                            if (replySection) replySection.style.display = 'none';
                        } else {
                            let html = `<div>${escapeHtml(data.message)}</div>`;
                            if (data.reponse) {
                                html += `<hr><div><strong>Réponse de l\'admin (${escapeHtml(data.reponduPar || 'Administrateur')}) :</strong></div>`;
                                html += `<div class="message-content">${escapeHtml(data.reponse)}</div>`;
                            }
                            html += `<div class="message-meta">Envoyé le ${escapeHtml(data.createdAt)}${data.reponduLe ? ' • Répondu le ' + escapeHtml(data.reponduLe) : ''}</div>`;
                            modalMessage.innerHTML = html;

                            // Show reply box so user can answer again
                            if (replySection) {
                                replySection.style.display = 'block';
                                replyTextarea.value = '';
                                replyNotifId.value = data.id;
                            }
                        }
                        modalOverlay.classList.add('open');
                        markAsRead(card.dataset.id, card);
                    })
                    .catch(err => {
                        console.error(err);
                        modalMessage.textContent = 'Erreur lors du chargement du message.';
                        modalOverlay.classList.add('open');
                    });
            } else {
                // fallback: show the message text present in data-message
                modalMessage.textContent = card.dataset.message || '';
                if (type === 'contact' && replySection) {
                    replySection.style.display = 'block';
                    replyTextarea.value = '';
                    replyNotifId.value = card.dataset.id;
                } else if (replySection) {
                    replySection.style.display = 'none';
                }
                modalOverlay.classList.add('open');
                markAsRead(card.dataset.id, card);
            }
        } else {
            // Inline expansion: populate the .notification-content inside the card
            const content = card.querySelector('.notification-content');
            if (link && link.includes('/contact/message/')) {
                fetch(link, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                    .then(r => r.json())
                    .then(data => {
                        if (data.error) {
                            content.innerHTML = '<div>Accès refusé.</div>';
                        } else {
                            let html = `<div class="user-message"><strong>Votre message initial :</strong><br>${escapeHtml(data.message)}</div>`;
                            if (data.reponse) {
                                html += `<div class="admin-reply"><strong>Réponse de l'admin :</strong><div>${escapeHtml(data.reponse)}</div></div>`;
                            }
                            html += `<div class="reply-form"><form class="reply-form-inner" data-id="${data.id}"><textarea class="reply-textarea" required></textarea><div class="reply-actions"><button type="button" class="btn btn-outline" data-action="cancel">Annuler</button><button type="submit" class="btn btn-primary">Envoyer la réponse</button></div></form></div>`;
                            content.innerHTML = html;
                        }
                        card.classList.add('open');
                        markAsRead(card.dataset.id, card);
                    })
                    .catch(err => {
                        console.error(err);
                        content.innerHTML = '<div>Erreur lors du chargement du message.</div>';
                        card.classList.add('open');
                    });
            } else {
                content.innerHTML = `<div class="user-message"><strong>Message :</strong><br>${escapeHtml(card.dataset.message)}</div>`;
                card.classList.add('open');
                markAsRead(card.dataset.id, card);
            }
        }
    }

    // attach view handlers
    document.querySelectorAll('[data-action="view"]').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const card = this.closest('.notification-card');
            openModalFromCard(card);
        });
    });

    // attach close
    function closeOverlay() {
        if (modalOverlay) modalOverlay.classList.remove('open');
    }
    [closeModal, closeBtn, cancelReply].forEach(el => {
        if (el) el.addEventListener('click', () => closeOverlay());
    });

    // send reply
    if (sendReply) {
        sendReply.addEventListener('click', () => {
            const id = replyNotifId.value;
            const response = replyTextarea.value.trim();
            if (!response) return;

            // choose route depending on context: if currentMessageLink points to contact message, call user-reply endpoint; otherwise call admin route
            if (currentMessageLink && currentMessageLink.includes('/contact/message/')) {
                fetch(`/contact/message/${id}/reply`, {
                    method: 'POST',
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    body: new URLSearchParams({ message: response })
                })
                .then(r => {
                    if (r.ok) {
                        closeOverlay();
                        location.reload();
                    } else {
                        alert('Impossible d\'envoyer la réponse.');
                    }
                })
                .catch(err => { console.error(err); alert('Erreur réseau'); });
            } else {
                fetch(`/admin/messages/${id}/repondre`, {
                    method: 'POST',
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    body: new URLSearchParams({ reponse: response })
                })
                .then(r => {
                    if (r.ok) {
                        closeOverlay();
                        location.reload();
                    } else {
                        alert('Impossible d\'envoyer la réponse.');
                    }
                })
                .catch(err => { console.error(err); alert('Erreur réseau'); });
            }
        });
    }

    // helper: mark as read (reuse earlier logic)
    function markAsRead(id, card) {
        fetch(APP_BASE_URL + `/notifications/${id}/marquer-lue`, { method: 'POST' })
            .then(() => {
                card.classList.remove('notification-unread');
                card.querySelector('[data-action="mark-read"]')?.remove();
            });
    }

    function escapeHtml(unsafe) {
        if (!unsafe) return '';
        return String(unsafe)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }
});