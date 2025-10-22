// DEBUG MODE ACTIVÉ - FICHIER EXTERNE
console.log('🔥 Register.js EXTERNE chargé avec succès!');

// Attendre que le DOM soit prêt
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initRegisterJS);
} else {
    initRegisterJS();
}

function initRegisterJS() {
    console.log('🚀 Initialisation register.js - DOM prêt');
    
    // VÉRIFICATION ÉLÉMENTS CRITIQUES
    const passwordInput = document.getElementById('password');
    console.log('🔍 Élément password trouvé:', !!passwordInput);
    
    if (!passwordInput) {
        console.error('❌ CRITIQUE: Champ password non trouvé!');
        console.log('📋 Éléments disponibles:', {
            form: document.querySelector('form'),
            email: document.getElementById('email'),
            prenom: document.getElementById('prenom'), 
            nom: document.getElementById('nom'),
            telephone: document.getElementById('telephone')
        });
        return;
    }

    const form = document.querySelector('form');
    const confirmPasswordInput = document.getElementById('confirm_password');
    const emailInput = document.getElementById('email');
    const telephoneInput = document.getElementById('telephone');
    const prenomInput = document.getElementById('prenom');
    const nomInput = document.getElementById('nom');

    console.log('✅ Éléments trouvés:', {
        form: !!form,
        password: !!passwordInput,
        confirmPassword: !!confirmPasswordInput,
        email: !!emailInput,
        telephone: !!telephoneInput,
        prenom: !!prenomInput,
        nom: !!nomInput
    });

    // Initialiser les fonctionnalités
    createHoneypot();
    initPasswordStrength();
    initValidations();
    
    if (form) {
        form.addEventListener('submit', handleSubmit);
        console.log('📝 Événement submit attaché');
    }
    
    console.log('🎉 Register.js initialisé avec succès!');

    function createHoneypot() {
        if (document.getElementById('honeypot')) {
            console.log('🛡️ Honeypot déjà existant');
            return;
        }

        const honeypot = document.createElement('input');
        honeypot.type = 'text';
        honeypot.name = 'website';
        honeypot.id = 'honeypot';
        honeypot.style.cssText = 'position:absolute;left:-9999px;opacity:0;';
        
        if (form) {
            form.appendChild(honeypot);
            console.log('🛡️ Honeypot créé');
        }
    }

    function initPasswordStrength() {
        console.log('🔧 Initialisation barre de force...');
        
        const passwordGroup = passwordInput.closest('.registration-form-group');
        if (!passwordGroup) {
            console.error('❌ Group password non trouvé');
            return;
        }

        // Nettoyer l'existant
        const existingBar = document.getElementById('password-strength-bar');
        const existingText = document.querySelector('.strength-text');
        if (existingBar) existingBar.remove();
        if (existingText) existingText.remove();

        // Créer la barre
        const strengthBar = document.createElement('div');
        strengthBar.id = 'password-strength-bar';
        strengthBar.className = 'password-strength-bar';
        strengthBar.innerHTML = '<div class="strength-fill" style="height:100%;width:0%;transition:width 0.4s;background:#ef4444;border-radius:4px;"></div>';
        strengthBar.style.cssText = 'height:6px;background:#f3f4f6;border-radius:4px;overflow:hidden;margin-top:8px;';

        // Créer le texte
        const strengthText = document.createElement('small');
        strengthText.className = 'strength-text';
        strengthText.textContent = '⚠️ Faible';
        strengthText.style.cssText = 'font-size:0.8rem;font-weight:500;margin-top:4px;display:block;color:#ef4444;';

        // Ajouter au DOM
        passwordGroup.appendChild(strengthBar);
        passwordGroup.appendChild(strengthText);

        // Événements
        passwordInput.addEventListener('input', updatePasswordStrength);
        console.log('✅ Barre de force créée');
        
        // Test initial
        setTimeout(updatePasswordStrength, 100);
    }

    function updatePasswordStrength() {
        const strengthBar = document.getElementById('password-strength-bar');
        const strengthText = document.querySelector('.strength-text');
        
        if (!strengthBar || !strengthText) return;

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
        console.log('🔐 Initialisation validations...');
        
        // Email
        if (emailInput) {
            emailInput.addEventListener('blur', validateEmail);
            emailInput.addEventListener('input', () => clearError('email'));
        }
        
        // Téléphone
        if (telephoneInput) {
            telephoneInput.addEventListener('blur', validateTelephone);
            telephoneInput.addEventListener('input', () => clearError('telephone'));
        }
        
        // Password match
        if (confirmPasswordInput) {
            confirmPasswordInput.addEventListener('input', validatePasswordMatch);
        }
        
        // Champs requis
        [prenomInput, nomInput, passwordInput].forEach(input => {
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
        const email = emailInput.value;
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
        const tel = telephoneInput.value.replace(/\s/g, '');
        if (!tel) return true;
        
        const regex = /^0[1-9]([-.]?[0-9]{2}){4}$/;
        if (!regex.test(tel)) {
            showError('telephone', 'Format: 06 12 34 56 78');
            return false;
        }
        clearError('telephone');
        return true;
    }

    function validatePasswordMatch() {
        const pass1 = passwordInput.value;
        const pass2 = confirmPasswordInput.value;
        
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
        const oldError = input.parentNode.querySelector('.error-message');
        if (oldError) oldError.remove();
        
        const errorEl = document.createElement('small');
        errorEl.className = 'error-message';
        errorEl.textContent = message;
        errorEl.style.cssText = 'color: #ef4444; font-size: 0.8rem; margin-top: 5px; display: block;';
        input.parentNode.appendChild(errorEl);
    }

    function clearError(field) {
        const input = document.getElementById(field);
        if (input) {
            input.classList.remove('error');
            const error = input.parentNode.querySelector('.error-message');
            if (error) error.remove();
        }
    }

    function handleSubmit(e) {
        console.log('📤 Submit intercepté');
        
        // Honeypot
        const honeypot = document.getElementById('honeypot');
        if (honeypot && honeypot.value !== '') {
            e.preventDefault();
            console.log('🚫 Bot détecté');
            return false;
        }

        let valid = true;
        
        // Validation champs requis
        if (!validateRequired('prenom')) valid = false;
        if (!validateRequired('nom')) valid = false;
        if (!validateRequired('email')) valid = false;
        if (!validateRequired('telephone')) valid = false;
        if (!validateRequired('password')) valid = false;
        if (!validateRequired('confirm_password')) valid = false;
        
        // Validation formats
        if (!validateEmail()) valid = false;
        if (!validateTelephone()) valid = false;
        if (!validatePasswordMatch()) valid = false;

        // Force mot de passe
        const strength = calculateStrength(passwordInput.value);
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
        
        // Désactiver bouton
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
}