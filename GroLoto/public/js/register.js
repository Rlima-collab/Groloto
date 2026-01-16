console.log('🔥 Register.js CHARGÉ !');

document.addEventListener('DOMContentLoaded', function() {
    console.log('🚀 Init Register...');
    initRegisterJS();
});

function initRegisterJS() {
    const passwordInput = document.getElementById('password');
    if (!passwordInput) {
        console.error('❌ Password input manquant');
        return;
    }

    console.log('✅ Éléments OK');

    const form = document.querySelector('form');
    const siretHiddenInput = document.getElementById('siret');

    // 🔥 SIRET 14 CASES - IMMÉDIATEMENT
    if (siretHiddenInput) {
        console.log('🔥 Création 14 cases SIRET...');
        initSiret14Cases();
    }

    // Autres initialisations
    createHoneypot(form);
    initPasswordStrength();
    initValidations();
    
    if (form) {
        form.addEventListener('submit', handleSubmit);
    }
}

// 🔥 FONCTION SIRET 14 CASES - PETITES + AUTO-PASSAGE
function initSiret14Cases() {
    const container = document.getElementById('siret-inputs');
    const siretHidden = document.getElementById('siret');
    
    if (!container || !siretHidden) {
        console.error('❌ Container siret manquant');
        return;
    }

    container.innerHTML = '';

    // CRÉER 14 CASES PETITES
    for (let i = 0; i < 14; i++) {
        const input = document.createElement('input');
        input.type = 'text';
        input.className = 'siret-input';
        input.maxLength = 1;
        input.dataset.index = i;
        input.placeholder = '_';
        
        // 🔥 ÉVÉNEMENTS AVEC AUTO-PASSAGE
        input.addEventListener('input', handleSiretInput);
        input.addEventListener('keydown', handleSiretKeydown);
        input.addEventListener('focus', () => {
            input.select();
            input.classList.add('focus-visible');
        });
        input.addEventListener('blur', () => input.classList.remove('focus-visible'));
        
        container.appendChild(input);
    }

    console.log('✅ 14 CASES SIRET PETITES CRÉÉES !');

    // REMPLIR SI EXISTANT
    const currentSiret = siretHidden.value.replace(/\D/g, '');
    for (let i = 0; i < currentSiret.length && i < 14; i++) {
        container.children[i].value = currentSiret[i];
    }

    // 🔥 FOCUS + SÉLECTION PREMIÈRE CASE
    setTimeout(() => {
        if (container.children[0]) {
            container.children[0].focus();
            container.children[0].select();
        }
    }, 100);
}

function handleSiretInput(e) {
    const input = e.target;
    let value = input.value.replace(/\D/g, '');
    
    // 1 CHIFFRE MAX
    if (value.length > 1) {
        value = value[0];
        input.value = value;
    } else {
        input.value = value;
    }
    
    // 🔥 AUTO-PASSAGE INSTANTANÉ
    if (value && input.dataset.index < 13) {
        const nextIndex = parseInt(input.dataset.index) + 1;
        const nextInput = document.querySelector(`.siret-input[data-index="${nextIndex}"]`);
        if (nextInput) {
            nextInput.focus();
            nextInput.select();
        }
    }
    
    // UPDATE CACHÉ
    updateSiretHidden();
}

function handleSiretKeydown(e) {
    const input = e.target;
    const index = parseInt(input.dataset.index);
    const container = document.getElementById('siret-inputs');
    
    // BACKSPACE → PRÉCÉDENT
    if (e.key === 'Backspace' && !input.value && index > 0) {
        container.children[index - 1].focus();
        container.children[index - 1].select();
    }
    
    // FLÈCHES
    if (e.key === 'ArrowLeft' && index > 0) {
        e.preventDefault();
        container.children[index - 1].focus();
        container.children[index - 1].select();
    }
    if (e.key === 'ArrowRight' && index < 13) {
        e.preventDefault();
        container.children[index + 1].focus();
        container.children[index + 1].select();
    }
}

function updateSiretHidden() {
    const inputs = document.querySelectorAll('.siret-input');
    const siret = Array.from(inputs).map(i => i.value).join('');
    document.getElementById('siret').value = siret;
}

function createHoneypot(form) {
    if (document.getElementById('honeypot')) return;
    const honeypot = document.createElement('input');
    honeypot.type = 'text';
    honeypot.name = 'website';
    honeypot.id = 'honeypot';
    honeypot.style.cssText = 'position:absolute;left:-9999px;opacity:0;';
    form.appendChild(honeypot);
    console.log('🛡️ Honeypot OK');
}

function initPasswordStrength() {
    const passwordInput = document.getElementById('password');
    const passwordGroup = passwordInput.closest('.registration-form-group');
    if (!passwordGroup) return;

    const existingBar = document.getElementById('password-strength-bar');
    const existingText = document.querySelector('.strength-text');
    if (existingBar) existingBar.remove();
    if (existingText) existingText.remove();

    const strengthBar = document.createElement('div');
    strengthBar.id = 'password-strength-bar';
    strengthBar.className = 'password-strength-bar';
    strengthBar.innerHTML = '<div class="strength-fill" style="height:100%;width:0%;transition:width 0.4s cubic-bezier(0.4,0,0.2,1);background:#ef4444;"></div>';
    strengthBar.style.cssText = 'height:6px;background:#f3f4f6;border-radius:4px;overflow:hidden;margin-top:8px;width:100%;';

    const strengthText = document.createElement('small');
    strengthText.className = 'strength-text';
    strengthText.textContent = '⚠️ Faible';
    strengthText.style.cssText = 'font-size:0.8rem;font-weight:500;margin-top:4px;display:block;color:#ef4444;';

    passwordGroup.appendChild(strengthBar);
    passwordGroup.appendChild(strengthText);

    passwordInput.addEventListener('input', updatePasswordStrength);
    setTimeout(updatePasswordStrength, 100);
}

function updatePasswordStrength() {
    const strengthBar = document.getElementById('password-strength-bar');
    const strengthText = document.querySelector('.strength-text');
    if (!strengthBar || !strengthText) return;

    const passwordInput = document.getElementById('password');
    const password = passwordInput.value;
    const strength = calculateStrength(password);
    const fill = strengthBar.querySelector('.strength-fill');

    if (fill) {
        fill.style.width = strength + '%';
        if (strength < 40) {
            fill.style.background = '#ef4444';
            strengthText.textContent = '⚠️ Faible';
            strengthText.style.color = '#ef4444';
        } else if (strength < 70) {
            fill.style.background = '#f59e0b';
            strengthText.textContent = '⚡ Moyenne';
            strengthText.style.color = '#f59e0b';
        } else {
            fill.style.background = '#10b981';
            strengthText.textContent = '✅ Forte';
            strengthText.style.color = '#10b981';
        }
    }
}

function calculateStrength(password) {
    if (!password) return 0;
    let score = 0;
    if (password.length >= 12) score += 30;
    else if (password.length >= 8) score += 20;
    else if (password.length >= 6) score += 10;
    if (/[a-z]/.test(password)) score += 10;
    if (/[A-Z]/.test(password)) score += 15;
    if (/[0-9]/.test(password)) score += 15;
    if (/[^a-zA-Z0-9]/.test(password)) score += 20;
    return Math.min(score, 100);
}

function initValidations() {
    const emailInput = document.getElementById('email');
    const telephoneInput = document.getElementById('telephone');
    const prenomInput = document.getElementById('prenom');
    const nomInput = document.getElementById('nom');
    const organisationInput = document.getElementById('organisation');
    const confirmPasswordInput = document.getElementById('confirm_password');
    
    if (emailInput) {
        emailInput.addEventListener('blur', validateEmail);
        emailInput.addEventListener('input', () => clearError('email'));
    }
    if (telephoneInput) {
        telephoneInput.addEventListener('blur', validateTelephone);
        telephoneInput.addEventListener('input', () => clearError('telephone'));
    }
    if (organisationInput) {
        organisationInput.addEventListener('blur', () => validateRequired('organisation'));
        organisationInput.addEventListener('input', () => clearError('organisation'));
    }
    if (confirmPasswordInput) {
        confirmPasswordInput.addEventListener('input', validatePasswordMatch);
    }
    [prenomInput, nomInput, document.getElementById('password')].forEach(input => {
        if (input) {
            input.addEventListener('blur', () => validateRequired(input.id));
            input.addEventListener('input', () => clearError(input.id));
        }
    });
}

function validateRequired(fieldId) {
    const input = document.getElementById(fieldId);
    if (input && !input.value.trim()) {
        showError(fieldId, 'Ce champ est requis');
        return false;
    }
    return true;
}

function validateEmail() {
    const email = document.getElementById('email').value;
    if (!email) return true;
    
    const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!regex.test(email)) {
        showError('email', 'Email invalide');
        return false;
    }
    clearError('email');
    return true;
}

function validateTelephone() {
    const tel = document.getElementById('telephone').value.replace(/\s/g, '');
    if (!tel) return true;
    
    const regex = /^0[1-9]([-.]?[0-9]{2}){4}$/;
    if (!regex.test(tel)) {
        showError('telephone', 'Format: 06 12 34 56 78');
        return false;
    }
    clearError('telephone');
    return true;
}

function validateSiret() {
    const siretHidden = document.getElementById('siret');
    const siret = siretHidden.value.replace(/\D/g, '');
    if (siret.length !== 14) {
        showError('siret', 'SIRET invalide (14 chiffres requis)');
        document.querySelectorAll('.siret-input').forEach(input => input.classList.add('error'));
        return false;
    }
    clearError('siret');
    document.querySelectorAll('.siret-input').forEach(input => input.classList.remove('error'));
    return true;
}

function validatePasswordMatch() {
    const pass1 = document.getElementById('password').value;
    const pass2 = document.getElementById('confirm_password').value;
    
    if (!pass1 || !pass2) {
        clearError('confirm_password');
        return true;
    }
    
    if (pass1 !== pass2) {
        showError('confirm_password', 'Mots de passe différents');
        return false;
    } else {
        clearError('confirm_password');
        return true;
    }
}

function showError(field, message) {
    const input = document.getElementById(field);
    if (!input) return;
    
    input.classList.add('error');
    
    // Trouver le conteneur parent (qui peut avoir l'icône œil)
    const container = input.parentNode;
    const oldError = container.parentNode.querySelector('.error-message');
    if (oldError) oldError.remove();
    
    const errorEl = document.createElement('small');
    errorEl.className = 'error-message';
    errorEl.textContent = message;
    errorEl.style.cssText = 'color: #ef4444; font-size: 0.8rem; margin-top: 5px; display: block;';
    
    // Insérer après le conteneur parent pour afficher en dessous
    container.parentNode.insertBefore(errorEl, container.nextSibling);
}

function clearError(field) {
    const input = document.getElementById(field);
    if (input) {
        input.classList.remove('error');
        // Chercher l'erreur au niveau du grand-parent maintenant
        const container = input.parentNode;
        const error = container.parentNode.querySelector('.error-message');
        if (error) error.remove();
    }
}

function handleSubmit(e) {
    const honeypot = document.getElementById('honeypot');
    if (honeypot && honeypot.value !== '') {
        e.preventDefault();
        return false;
    }

    let valid = true;
    
    if (!validateRequired('prenom')) valid = false;
    if (!validateRequired('nom')) valid = false;
    if (!validateRequired('email')) valid = false;
    if (!validateRequired('telephone')) valid = false;
    if (!validateRequired('password')) valid = false;
    if (!validateRequired('confirm_password')) valid = false;
    if (!validateRequired('organisation')) valid = false;
    if (!validateSiret()) valid = false;
    if (!validateEmail()) valid = false;
    if (!validateTelephone()) valid = false;
    if (!validatePasswordMatch()) valid = false;

    const strength = calculateStrength(document.getElementById('password').value);
    if (strength < 40) {
        showError('password', 'Mot de passe trop faible');
        valid = false;
    }

    if (!valid) {
        e.preventDefault();
        const firstError = document.querySelector('.error');
        if (firstError) firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
        showToast('Veuillez corriger les erreurs', 'error');
        return false;
    }

    showToast('Inscription en cours...', 'success');
    
    const submitBtn = form.querySelector('button[type="submit"]');
    if (submitBtn) {
        submitBtn.disabled = true;
        const btnText = submitBtn.querySelector('.btn-text');
        if (btnText) btnText.textContent = 'Inscription...';
    }
    
    return true;
}

function showToast(message, type) {
    const toast = document.createElement('div');
    toast.textContent = message;
    toast.style.cssText = `
        position: fixed; top: 20px; right: 20px; z-index: 9999;
        padding: 15px 20px; border-radius: 8px; color: white;
        font-weight: 500; box-shadow: 0 10px 30px rgba(0,0,0,0.3);
        transform: translateX(400px); transition: all 0.3s ease;
        background: ${type === 'error' ? '#ef4444' : '#10b981'};
    `;
    
    document.body.appendChild(toast);
    setTimeout(() => toast.style.transform = 'translateX(0)', 100);
    setTimeout(() => {
        toast.style.transform = 'translateX(400px)';
        setTimeout(() => toast.remove(), 300);
    }, 4000);
}