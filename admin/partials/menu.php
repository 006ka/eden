<?php
// Vérifier si la page active est définie, sinon utiliser 'dashboard'
$current_page = basename($_SERVER['PHP_SELF'], '.php');
$current_page = $current_page === 'index' ? 'login' : $current_page;
?>

<nav class="admin-nav">
    <ul>
        <li class="<?php echo ($current_page === 'admin' || $current_page === 'index') ? 'active' : ''; ?>">
            <a href="admin.php">
                <i class="fas fa-tachometer-alt"></i> Tableau de bord
            </a>
        </li>
        <li class="<?php echo ((isset($_GET['section']) && $_GET['section'] === 'enseignements') || $current_page === 'enseignements') ? 'active' : ''; ?>">
            <a href="?section=enseignements">
                <i class="fas fa-book"></i> Enseignements
                <span class="badge"><?php echo isset($stats['total_enseignements']) ? $stats['total_enseignements'] : '0'; ?></span>
            </a>
        </li>
        <li class="<?php echo ($current_page === 'retreats') ? 'active' : ''; ?>">
            <a href="#" id="retreats-link">
                <i class="fas fa-mountain"></i> Retraites
                <span class="badge"><?php echo $stats['total_retreats'] ?? '0'; ?></span>
                <i class="fas fa-chevron-down"></i>
            </a>
            <ul class="submenu">
                <li><a href="#">Liste des retraites</a></li>
                <li><a href="#">Nouvelle retraite</a></li>
                <li><a href="setup_schedules.php">Horaires</a></li>
            </ul>
        </li>
        <li class="<?php echo ($current_page === 'gallery') ? 'active' : ''; ?>">
            <a href="#" id="gallery-link">
                <i class="fas fa-images"></i> Galerie
                <span class="badge"><?php echo $stats['total_gallery'] ?? '0'; ?></span>
            </a>
        </li>
        <li class="<?php echo ($current_page === 'inscriptions') ? 'active' : ''; ?>">
            <a href="#" id="inscriptions-link">
                <i class="fas fa-clipboard-list"></i> Inscriptions
                <span class="badge"><?php echo $stats['total_inscriptions'] ?? '0'; ?></span>
            </a>
        </li>
        <li class="<?php echo ($current_page === 'contacts') ? 'active' : ''; ?>">
            <a href="#">
                <i class="fas fa-envelope"></i> Contacts
                <span class="badge"><?php echo $stats['total_contacts'] ?? '0'; ?></span>
            </a>
        </li>
        <li class="<?php echo ($current_page === 'testimonials') ? 'active' : ''; ?>">
            <a href="setup_testimonials.php">
                <i class="fas fa-quote-left"></i> Témoignages
            </a>
        </li>
        <li class="menu-divider"></li>
        <li>
            <a href="#" id="account-link">
                <i class="fas fa-user-cog"></i> Mon compte
            </a>
        </li>
        <li>
            <a href="logout.php">
                <i class="fas fa-sign-out-alt"></i> Déconnexion
            </a>
        </li>
    </ul>
</nav>

<style>
.admin-nav {
    background: #2c3e50;
    color: #ecf0f1;
    width: 250px;
    height: 100%;
    position: fixed;
    left: 0;
    top: 0;
    padding: 20px 0;
    overflow-y: auto;
    box-shadow: 2px 0 5px rgba(0,0,0,0.1);
}

.admin-nav ul {
    list-style: none;
    padding: 0;
    margin: 0;
}

.admin-nav li {
    position: relative;
}

.admin-nav a {
    display: flex;
    align-items: center;
    padding: 12px 20px;
    color: #ecf0f1;
    text-decoration: none;
    transition: all 0.3s ease;
    position: relative;
}

.admin-nav a:hover,
.admin-nav li.active > a {
    background: #34495e;
    color: #fff;
}

.admin-nav i {
    width: 24px;
    margin-right: 10px;
    text-align: center;
}

.admin-nav .badge {
    margin-left: auto;
    background: #e74c3c;
    color: white;
    border-radius: 10px;
    padding: 2px 8px;
    font-size: 12px;
}

.admin-nav .submenu {
    display: none;
    background: #34495e;
    padding-left: 20px;
}

.admin-nav li:hover > .submenu {
    display: block;
}

.admin-nav .submenu a {
    padding: 8px 20px 8px 40px;
    font-size: 0.9em;
}

.menu-divider {
    height: 1px;
    background: rgba(255,255,255,0.1);
    margin: 10px 0;
}

/* Responsive */
@media (max-width: 992px) {
    .admin-nav {
        width: 70px;
        overflow: hidden;
    }
    
    .admin-nav a span:not(.badge),
    .admin-nav .submenu {
        display: none;
    }
    
    .admin-nav:hover {
        width: 250px;
    }
    
    .admin-nav:hover a span:not(.badge),
    .admin-nav:hover .submenu {
        display: inline;
    }
    
    .admin-nav .submenu {
        position: static;
        width: auto;
        box-shadow: none;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Gestion des sous-menus
    const menuItems = document.querySelectorAll('.admin-nav > ul > li');
    
    menuItems.forEach(item => {
        if (item.querySelector('.submenu')) {
            item.addEventListener('click', function(e) {
                if (window.innerWidth <= 992) {
                    e.preventDefault();
                    this.classList.toggle('open');
                }
            });
        }
    });
});
</script>
