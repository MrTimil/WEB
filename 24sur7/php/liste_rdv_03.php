<?php
/** @file
 * Liste des rendez-vous d'un utilisateur
 *
 * @author : Frederic Dadeau - frederic.dadeau@univ-fcomte.fr
 */

// Bufferisation des sorties
ob_start();

// Inclusion de la bibliothéque
include('bibli_24sur7.php');

// Récupèration et test du paramètre URL
if (!isset($_GET['IDUser'])
|| !is_numeric($_GET['IDUser'])) {
	fd_redirige('liste_users_02.php');
}

$_GET['IDUser'] = (int) $_GET['IDUser'];

if ($_GET['IDUser'] < 1
|| $_GET['IDUser'] > 999999) {
	fd_redirige('liste_users_02.php');
}

// Début de la page
fd_html_head('Rendez-vous utilisateur','-');

// Connexion à la base de données
fd_bd_connexion();

// Requête de sélection de l'utilisateur
// jointure externe pour retourner un enregistrement même si l'utilisateur n'a pas de rendez-vous
// (cas de l'utilisateur 8)
// Dans ce cas, la requête renvoie un seul enregistrement avec le nom de l'utilisateur dans utiNom et
// tous les autres champs à null
$sql = "SELECT	utiNom, rendezvous.*, categorie.*
		FROM	
        rendezvous INNER JOIN categorie ON catID = rdvIDCategorie
        RIGHT OUTER JOIN utilisateur ON catIDUtilisateur = utiID
		WHERE utiID = {$_GET['IDUser']}
		ORDER BY rdvDate, rdvHeureDebut";

// Exécution de la requête
$R = mysqli_query($GLOBALS['bd'], $sql) or fd_bd_erreur($sql);

$isFirst = TRUE;

// Affichage du résultat de la requête
while ($D = mysqli_fetch_assoc($R)) {
	if ($D['rdvHeureDebut'] == -1) {
		$heure = 'journ&eacute;e enti&egrave;re';
	} else {
		$heure = fd_heure_claire($D['rdvHeureDebut'])
				.' &agrave; '.fd_heure_claire($D['rdvHeureFin']);
	}

	$public = ($D['catPublic'] == 0) ? 'font-style: italic;' : '';

	if ($isFirst) {
		echo '<h2>Utilisateur ', $D['rdvIDUtilisateur'], ' : ',
				htmlentities($D['utiNom'], ENT_QUOTES, 'UTF-8'), '</h2><ul>';
		$isFirst = FALSE;
	}
	// $D['rdvLibelle'] est égal à null si l'utilisateur n'a pas de rendez-vous
    if ($D['rdvLibelle'] != null){
        echo '<li style="', $public, 'background-color: #', htmlentities($D['catCouleurFond'], ENT_QUOTES, 'UTF-8'),
				';border: 1px solid #', htmlentities($D['catCouleurBordure'], ENT_QUOTES, 'UTF-8'),
				';margin: 2px 0">',
				fd_date_claire($D['rdvDate']),
				' - ', $heure,
				' - ', htmlentities($D['rdvLibelle'], ENT_QUOTES, 'UTF-8'), '</li>';
    }
}

// Si aucun utilisateur trouvé
if ($isFirst) {
	echo '<h4>Aucun utilisateur ne correpond à cet identifiant</h4>';
}
else {
    echo '</ul>';
}

// Libère la mémoire associée au résultat $R
mysqli_free_result($R);

// Déconnexion de la base de données
mysqli_close($GLOBALS['bd']);

// fin de la page
echo '</main></body></html>';
?>
