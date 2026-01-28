document.addEventListener('DOMContentLoaded', function () {
    const burgerMenu = document.getElementById('burgerMenu');
    const sidebar = document.getElementById('sidebar');
    const navLinks = document.querySelectorAll('.nav-link');
    const currentPath = window.location.pathname;

    // Au chargement, s'assurer que la sidebar est fermée
    sidebar.classList.remove('open');
    burgerMenu.classList.remove('active');
    burgerMenu.setAttribute('aria-expanded', 'false');

    // Clic sur le burger menu pour ouvrir/fermer la sidebar
    burgerMenu.addEventListener('click', function (event) {
        event.stopPropagation();
        sidebar.classList.toggle('open');

        // Mettre à jour aria-expanded + état visuel du burger
        const isOpen = sidebar.classList.contains('open');
        burgerMenu.setAttribute('aria-expanded', isOpen);
        burgerMenu.classList.toggle('active', isOpen);

        // Fermer tous les sous-menus quand on ferme la sidebar
        if (!isOpen) {
            document.querySelectorAll('.submenu.active').forEach(activeSubmenu => {
                activeSubmenu.classList.remove('active');
            });
            document.querySelectorAll('.nav-link.active').forEach(activeLink => {
                activeLink.classList.remove('active');
            });
        }
    });

    // Fermer la sidebar si on clique en dehors
    document.addEventListener('click', function(event) {
        const isClickInsideSidebar = sidebar.contains(event.target);
        const isClickOnBurger = burgerMenu.contains(event.target);
        
        if (!isClickInsideSidebar && !isClickOnBurger && sidebar.classList.contains('open')) {
            sidebar.classList.remove('open');
            burgerMenu.classList.remove('active');
            burgerMenu.setAttribute('aria-expanded', 'false');
        }
    });

    // Fonction pour ouvrir automatiquement le sous-menu contenant la page active
    function openActiveSubmenu() {
        let foundMatch = false;
        const allSubmenuLinks = document.querySelectorAll('.submenu a');
        
        // Chercher une correspondance exacte uniquement
        for (let i = 0; i < allSubmenuLinks.length; i++) {
            const link = allSubmenuLinks[i];
            const href = link.getAttribute('href');
            if (href && href !== '#' && href !== '') {
                // Correspondance exacte avec l'URL actuelle
                if (currentPath === href || currentPath === href + '/') {
                    const submenu = link.closest('.submenu');
                    const parentNavItem = submenu.closest('.nav-item');
                    const parentNavLink = parentNavItem.querySelector('.nav-link');
                    
                    submenu.classList.add('active');
                    parentNavLink.classList.add('active');
                    link.classList.add('active-page');
                    foundMatch = true;
                    break; // S'arrêter dès qu'on trouve une correspondance
                }
            }
        }
        
        // Si aucune correspondance exacte, chercher le meilleur match partiel (le plus long)
        if (!foundMatch) {
            let bestMatch = null;
            let bestMatchLength = 0;
            
            for (let i = 0; i < allSubmenuLinks.length; i++) {
                const link = allSubmenuLinks[i];
                const href = link.getAttribute('href');
                if (href && href !== '#' && href !== '' && href !== '/') {
                    // L'URL actuelle commence par le href
                    if (currentPath.startsWith(href) && href.length > bestMatchLength) {
                        bestMatch = link;
                        bestMatchLength = href.length;
                    }
                }
            }
            
            if (bestMatch) {
                const submenu = bestMatch.closest('.submenu');
                const parentNavItem = submenu.closest('.nav-item');
                const parentNavLink = parentNavItem.querySelector('.nav-link');
                
                submenu.classList.add('active');
                parentNavLink.classList.add('active');
                bestMatch.classList.add('active-page');
                foundMatch = true;
            }
        }
        
        // Si toujours aucune correspondance, vérifier les liens principaux
        if (!foundMatch) {
            navLinks.forEach(link => {
                const href = link.getAttribute('href');
                if (href && href !== '#' && !link.classList.contains('has-submenu')) {
                    if (currentPath === href || currentPath === href + '/') {
                        link.classList.add('active');
                    }
                }
            });
        }
    }

    // Gestion des sous-menus avec animation de la flèche
    navLinks.forEach(link => {
        const submenu = link.parentElement.querySelector('.submenu');
        if (submenu) {
            link.classList.add('has-submenu');
            const arrow = document.createElement('span');
            arrow.classList.add('arrow');
            link.appendChild(arrow);

            link.addEventListener('click', function (event) {
                event.preventDefault();

                // Ouvrir la sidebar si elle n'est pas déjà ouverte
                if (!sidebar.classList.contains('open')) {
                    sidebar.classList.add('open');
                    burgerMenu.classList.add('active');
                    burgerMenu.setAttribute('aria-expanded', 'true');
                }

                const isActive = submenu.classList.contains('active');

                // Désactiver tous les autres sous-menus
                document.querySelectorAll('.submenu.active').forEach(activeSubmenu => {
                    if (activeSubmenu !== submenu) {
                        activeSubmenu.classList.remove('active');
                    }
                });
                document.querySelectorAll('.nav-link.active').forEach(activeLink => {
                    if (activeLink !== link && !activeLink.classList.contains('has-submenu')) {
                        activeLink.classList.remove('active');
                    }
                });

                // Activer/désactiver le sous-menu cliqué
                if (!isActive) {
                    submenu.classList.add('active');
                    link.classList.add('active');
                } else {
                    submenu.classList.remove('active');
                    link.classList.remove('active');
                }
            });
        } else {
            link.addEventListener('click', function (event) {
                // Ne pas fermer les sous-menus quand on clique sur un lien sans sous-menu
                document.querySelectorAll('.nav-link.active').forEach(activeLink => {
                    if (!activeLink.classList.contains('has-submenu')) {
                        activeLink.classList.remove('active');
                    }
                });
            });
        }
    });
    
    // Ouvrir automatiquement le sous-menu de la page active au chargement
    openActiveSubmenu();
});