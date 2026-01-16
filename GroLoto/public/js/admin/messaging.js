/**
 * Messagerie Admin - JavaScript spécifique
 * Fonctions spécifiques à l'interface admin (autocomplétion, envoi de masse, etc.)
 * 
 * @package Groloto
 * @since 1.0.0
 */

/**
 * Variables globales pour l'autocomplétion
 */
let autocompleteTimeout;
let selectedIndex = -1;

/**
 * Initialise l'autocomplétion pour le champ email
 */
function initAutocomplete(inputEl, listEl, onSelect) {
    selectedIndex = -1;
    
    // Navigation au clavier dans l'autocomplétion
    inputEl.addEventListener('keydown', function(e) {
        const items = listEl.querySelectorAll('.autocomplete-item');
        
        if (items.length === 0) return;
        
        if (e.key === 'ArrowDown') {
            e.preventDefault();
            selectedIndex = (selectedIndex + 1) % items.length;
            updateSelectedItem(items, selectedIndex);
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            selectedIndex = selectedIndex <= 0 ? items.length - 1 : selectedIndex - 1;
            updateSelectedItem(items, selectedIndex);
        } else if (e.key === 'Enter' && selectedIndex >= 0) {
            e.preventDefault();
            items[selectedIndex].click();
        } else if (e.key === 'Escape') {
            listEl.style.display = 'none';
            selectedIndex = -1;
        }
    });
    
    inputEl.addEventListener('input', function() {
        const query = this.value.trim();
        
        clearTimeout(autocompleteTimeout);
        selectedIndex = -1;
        
        if (query.length < 2) {
            listEl.style.display = 'none';
            return;
        }
        
        autocompleteTimeout = setTimeout(async () => {
            try {
                const response = await fetch('/admin/messages/emails?q=' + encodeURIComponent(query));
                const users = await response.json();
                
                if (users.length > 0) {
                    listEl.innerHTML = users.map(user => {
                        let roleLabel = '';
                        if (user.role === 'ROLE_BENEVOLE') roleLabel = 'Bénévole';
                        else if (user.role === 'ROLE_MECENE') roleLabel = 'Mécène';
                        
                        return `
                            <div class="autocomplete-item" data-email="${user.email}" data-name="${user.prenom} ${user.nom}" data-role="${roleLabel}">
                                <div class="autocomplete-item-email">${user.email}</div>
                                <div class="autocomplete-item-name">${user.prenom} ${user.nom} ${roleLabel ? '(' + roleLabel + ')' : ''}</div>
                            </div>
                        `;
                    }).join('');
                    listEl.style.display = 'block';
                    
                    listEl.querySelectorAll('.autocomplete-item').forEach(item => {
                        item.addEventListener('click', function() {
                            if (onSelect) {
                                onSelect(this.dataset.email, this.dataset.name, this.dataset.role);
                            }
                            listEl.style.display = 'none';
                            selectedIndex = -1;
                        });
                    });
                } else {
                    listEl.style.display = 'none';
                }
            } catch (err) {
                console.error('Erreur autocomplétion:', err);
            }
        }, 300);
    });
    
    // Fermer l'autocomplétion si on clique ailleurs
    document.addEventListener('click', (e) => {
        if (!inputEl.contains(e.target) && !listEl.contains(e.target)) {
            listEl.style.display = 'none';
        }
    });
}

/**
 * Valide un email via l'API
 */
async function validateEmail(email) {
    try {
        const response = await fetch('/admin/messages/emails?q=' + encodeURIComponent(email));
        const users = await response.json();
        return users.find(u => u.email.toLowerCase() === email.toLowerCase());
    } catch (err) {
        console.error('Erreur validation email:', err);
        return null;
    }
}

/**
 * Normalise un destinataire pour détection de groupe
 */
function normalizeRecipient(recipient) {
    const normalized = recipient.toLowerCase()
        .normalize("NFD")
        .replace(/[\u0300-\u036f]/g, "")
        .replace(/\s+/g, '_');
    
    const isAllBenevoles = ['all_benevoles', 'tous_les_benevoles', 'tous_benevoles', 'tous'].includes(normalized);
    const isAllMecenes = ['all_mecenes', 'tous_les_mecenes', 'tous_mecenes'].includes(normalized);
    
    return {
        isAllBenevoles,
        isAllMecenes,
        isGroup: isAllBenevoles || isAllMecenes,
        groupName: isAllBenevoles ? 'ALL_BENEVOLES' : (isAllMecenes ? 'ALL_MECENES' : null),
        displayName: isAllBenevoles ? 'Tous les bénévoles' : (isAllMecenes ? 'Tous les mécènes' : null),
        role: isAllBenevoles ? 'Bénévole' : (isAllMecenes ? 'Mécène' : null)
    };
}

/**
 * Met à jour la checkbox "Marqué comme répondu"
 */
async function markAsReplied(conversationId, mark, adminEmail, onSuccess, onError) {
    try {
        const url = '/admin/messages/' + conversationId + '/mark-replied';
        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: 'mark=' + encodeURIComponent(mark ? '1' : '0')
        });
        const data = await response.json();
        if (data.success) {
            if (onSuccess) onSuccess(data);
        } else {
            if (onError) onError(data.error || 'Erreur');
        }
    } catch (err) {
        console.error(err);
        if (onError) onError('Erreur réseau');
    }
}

// Exposition des fonctions au scope global
window.initAutocomplete = initAutocomplete;
window.validateEmail = validateEmail;
window.normalizeRecipient = normalizeRecipient;
window.markAsReplied = markAsReplied;
