<?php
// ============================================================
// LOGOUT - logout.php
// Winawasak ang session ng user at nagre-redirect sa login page
// ============================================================

session_start();
session_destroy();
header('Location: login.php');
exit;
