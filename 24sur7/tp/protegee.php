<?php
/**
* TP 5 : Page protégée
*
* @author : Frederic Dadeau (frederic.dadeau@univ-fcomte.fr)
*/
// Bufferisation des sorties
ob_start();

session_start();

// Inclusion de la bibliothéque
include('bibli_24sur7.php');

// Vérification que la session est active
fd_verifie_session();

// Si on est encore là, c'est que l'utilisateur est bien authentifié.
fd_html_head('Session OK | 24sur7','-');

echo '<h1>Utilisateur authentifié</h1>',
	'<h3>Nom : ', htmlentities($_SESSION['utiNom'], ENT_QUOTES, 'UTF-8'),
	'<br>ID : ', $_SESSION['utiID'],
	'</h3><p><a href="deconnexion.php">Se d&eacute;connecter</a></p></main></body></html>';
?>
