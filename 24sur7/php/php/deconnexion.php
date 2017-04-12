<?php
/**
* TP 4 : Déconnexion d'un utilisateur
*
* @author : Frederic Dadeau (frederic.dadeau@univ-fcomte.fr)
*/
require('bibli_24sur7.php');
//ob_start();
// Lancement de la session
session_start();
// Appel à la fonction de déconnexion et de redirection
fd_exit_session();
?>