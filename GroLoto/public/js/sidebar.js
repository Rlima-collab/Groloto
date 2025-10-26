document.addEventListener('DOMContentLoaded', function () {
    const burgerMenu = document.getElementById('burgerMenu');
    const sidebar = document.getElementById('sidebar');
    const navLinks = document.querySelectorAll('.nav-link');

    burgerMenu.addEventListener('click', function (event) {
        event.stopPropagation();
        sidebar.classList.toggle('collapsed');

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
                    activeSubmenu.classList.remove('active');
                });
                document.querySelectorAll('.nav-link.active').forEach(activeLink => {
                    activeLink.classList.remove('active');
                });

                // Activer/désactiver le sous-menu cliqué
                if (!isActive) {
                    submenu.classList.add('active');
                    link.classList.add('active');
                }
            });
        } else {
            link.addEventListener('click', function (event) {
                document.querySelectorAll('.submenu.active').forEach(activeSubmenu => {
                    activeSubmenu.classList.remove('active');
                });
                document.querySelectorAll('.nav-link.active').forEach(activeLink => {
                    activeLink.classList.remove('active');
                });
            });
        }
    });
});