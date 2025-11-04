document.addEventListener('DOMContentLoaded', function () {
    const burgerMenu = document.getElementById('burgerMenu');
    const sidebar = document.getElementById('sidebar');
    const navLinks = document.querySelectorAll('.nav-link');
    const currentPath = window.location.pathname;
    
    // Initialiser l'état du body en fonction de la sidebar
    if (sidebar.classList.contains('collapsed')) {
        document.body.classList.add('sidebar-collapsed');
    }

    burgerMenu.addEventListener('click', function (event) {
        event.stopPropagation();
        sidebar.classList.toggle('collapsed');
        
        // Ajouter/retirer la classe au body pour gérer le header
        document.body.classList.toggle('sidebar-collapsed');

        // Close all submenus when collapsing the sidebar
        if (sidebar.classList.contains('collapsed')) {
            document.querySelectorAll('.submenu.active').forEach(activeSubmenu => {
                activeSubmenu.classList.remove('active');
            });
            document.querySelectorAll('.nav-link.active').forEach(activeLink => {
                activeLink.classList.remove('active');
            });
        }
    });

    // Fonction pour ouvrir automatiquement le sous-menu contenant la page active
    function openActiveSubmenu() {
        let foundMatch = false;
        const allSubmenuLinks = document.querySelectorAll('.submenu a');
        
        // D'abord, chercher une correspondance exacte
        allSubmenuLinks.forEach(link => {
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
                }
            }
        });
        
        // Si aucune correspondance exacte, chercher une correspondance partielle (pour les sous-pages)
        if (!foundMatch) {
            allSubmenuLinks.forEach(link => {
                const href = link.getAttribute('href');
                if (href && href !== '#' && href !== '' && href !== '/') {
                    // L'URL actuelle commence par le href (ex: /stocks/inventaire commence par /stocks)
                    if (currentPath.startsWith(href) && currentPath.length > href.length) {
                        const submenu = link.closest('.submenu');
                        const parentNavItem = submenu.closest('.nav-item');
                        const parentNavLink = parentNavItem.querySelector('.nav-link');
                        
                        submenu.classList.add('active');
                        parentNavLink.classList.add('active');
                        link.classList.add('active-page');
                        foundMatch = true;
                    }
                }
            });
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

                // Si sidebar est pliée, on l'ouvre temporairement
                if (sidebar.classList.contains('collapsed')) {
                    sidebar.classList.remove('collapsed');
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