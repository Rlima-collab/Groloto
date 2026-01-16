/**
 * Messagerie Admin - JavaScript
 * Gestion de l'interface de messagerie pour les administrateurs
 * 
 * @package Groloto
 * @since 1.0.0
 */

// Variables globales
let currentConversationId = null;
let currentSubject = '';
let currentRecipient = '';
let currentRecipientEmail = '';
let currentRecipientName = '';
let currentRecipientRole = '';
let currentRecipientProfileImage = '';
let currentOtherEmail = '';
let confirmCallback = null;
let currentEditMessageId = null;

/**
 * Affiche une modal de confirmation
 */
function showConfirm(title, message, onConfirm) {
    const modal = document.getElementById('confirmModal');
    const titleEl = document.getElementById('confirmTitle');
    const messageEl = document.getElementById('confirmMessage');
    
    titleEl.textContent = title;
    messageEl.textContent = message;
    confirmCallback = onConfirm;
    modal.classList.add('active');
}

/**
 * Affiche une notification toast
 */
function showToast(message, type = 'info') {
    const container = document.getElementById('toastContainer');
    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
    
    const icon = type === 'success' ? '✓' : type === 'error' ? '✕' : 'ℹ';
    toast.innerHTML = `<span style="font-size: 20px;">${icon}</span><span>${message}</span>`;
    
    container.appendChild(toast);
    
    setTimeout(() => {
        toast.style.animation = 'slideIn 0.3s ease reverse';
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

/**
 * Échappe le HTML pour éviter les injections XSS
 */
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

/**
 * Génère le HTML pour un avatar
 */
function createAvatarHtml(name, profileImage) {
    if (profileImage && profileImage.trim() !== '') {
        return '<img src="/uploads/profiles/' + escapeHtml(profileImage) + '" alt="Photo de profil">';
    }
    const initials = name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2);
    return initials;
}

/**
 * Fait défiler les messages jusqu'en bas
 */
function scrollToBottom() {
    const chatMessages = document.getElementById('chatMessages');
    if (chatMessages) {
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }
}

/**
 * Marque les notifications d'une conversation comme lues
 */
function markConversationNotificationsAsRead(conversationId) {
    fetch('/notifications/mark-conversation-read/' + conversationId, {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Content-Type': 'application/json'
        }
    }).catch(err => console.log('Erreur marquage notification:', err));
}

/**
 * Toggle le menu d'une conversation
 */
function toggleConversationMenu(e, convId) {
    e.stopPropagation();
    document.querySelectorAll('.conversation-dropdown').forEach(m => m.classList.remove('show'));
    document.getElementById('convMenu' + convId).classList.add('show');
}

/**
 * Toggle le menu d'actions d'un message
 */
function toggleMessageMenu(event, messageId) {
    event.stopPropagation();
    const menu = document.getElementById('msgMenu' + messageId);
    const allMenus = document.querySelectorAll('.message-actions-dropdown');
    
    allMenus.forEach(m => {
        if (m !== menu) m.classList.remove('active');
    });
    
    menu.classList.toggle('active');
}

/**
 * Met à jour l'élément sélectionné dans l'autocomplétion
 */
function updateSelectedItem(items, selectedIndex) {
    items.forEach((item, index) => {
        if (index === selectedIndex) {
            item.classList.add('selected');
            item.scrollIntoView({ block: 'nearest' });
        } else {
            item.classList.remove('selected');
        }
    });
}

// Exposition des fonctions au scope global
window.toggleConversationMenu = toggleConversationMenu;
window.toggleMessageMenu = toggleMessageMenu;
window.showConfirm = showConfirm;
window.showToast = showToast;
window.escapeHtml = escapeHtml;
window.createAvatarHtml = createAvatarHtml;
window.scrollToBottom = scrollToBottom;
window.markConversationNotificationsAsRead = markConversationNotificationsAsRead;
