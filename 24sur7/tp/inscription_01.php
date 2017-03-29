<?php
/** @file
 * Inscription d'un utilisateur
 *
 * @author : Frederic Dadeau - frederic.dadeau@univ-fcomte.fr
 */

// Bufferisation des sorties
ob_start();

// Inclusion de la bibliothéque
include('bibli_24sur7.php');

// Début de la page
fd_html_head('24sur7 | inscription','-');

echo '<h1>Réception du formulaire d\'inscription utilisateur</h1>';

// Boucle d'affichage des valeurs du tableau super-global $_POST
foreach ($_POST as $Cle => $Valeur) {
	echo 'Zone ', htmlentities($Cle, ENT_COMPAT, 'UTF-8'),
		' = ', htmlentities($Valeur, ENT_COMPAT, 'UTF-8'), '<br>';
}

echo '</main></body></html>';

ob_end_flush();
?>
