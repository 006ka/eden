<?php
$base = (strpos($_SERVER['SCRIPT_NAME'], '/public/') !== false) ? '../' : '';
?>
<footer class="site-footer">
    <div class="container">
        <p>© 2025 EDEN — Tous droits réservés.</p>
        <p>Verset du jour : <span id="verset"></span></p>
    </div>
</footer>

<script src="<?php echo $base; ?>assets/js/site.js"></script>
<script src="<?php echo $base; ?>assets/js/script.js"></script>
