<?php
/** @file
 * Afficher les infos des utilisateurs de 24sur7
 *
 * @author : Frederic Dadeau - frederic.dadeau@univ-fcomte.fr
 */

// Bufferisation des sorties
ob_start();

// Inclusion de la bibliothèque
include('bibli_24sur7.php');

// Début de la page
fd_html_head('Infos utilisateur', '-');

// Connexion à la base de données
fd_bd_connexion();

// Requête de sélection des utilisateurs
$sql = 'SELECT *
		FROM utilisateur
		ORDER BY utiDateInscription DESC';

// Exécution de la requête
$R = mysqli_query($GLOBALS['bd'], $sql) or fd_bd_erreur($sql);

// Boucle de traitement
while ($D = mysqli_fetch_assoc($R)) {
	echo '<h2>Utilisateur ', $D['utiID'], '</h2>',
		'<ul>',
			'<li>Nom : ', htmlentities($D['utiNom'], ENT_QUOTES, 'UTF-8'),'</li>',
			'<li>Mail : ', htmlentities($D['utiMail'], ENT_QUOTES, 'UTF-8'),'</li>',
			'<li>Inscription : ', fd_date_claire($D['utiDateInscription']),'</li>',
			'<li>Jours &agrave; afficher : ', $D['utiJours'],'</li>',
			'<li>Heure d&eacute;but : ', $D['utiHeureMin'],'</li>',
			'<li>Heure fin : ', $D['utiHeureMax'],'</li>',
		'</ul>';
}

// Libère la mémoire associée au résultat $R
mysqli_free_result($R);

// Déconnexion de la base de données
mysqli_close($GLOBALS['bd']);

// fin de la page
echo '</main></body></html>';
?>
