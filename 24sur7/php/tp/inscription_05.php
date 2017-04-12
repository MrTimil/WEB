<?php
/**
* TP 5 : Inscription d'un utilisateur
*
* @author : Frederic Dadeau (frederic.dadeau@univ-fcomte.fr)
*/

// Bufferisation des sorties
ob_start();

// Inclusion de la bibliothéque
include('bibli_24sur7.php');

// Début de la page
fd_html_head('24sur7 | inscription','-');

echo '<h1>Réception du formulaire d\'inscription utilisateur</h1>';

// Vérif saisie et ajout utilisateur si OK
$erreurs = fdl_add_utilisateur();

// Traitement résultat de la vérification
$nbErr = count($erreurs);
if ($nbErr == 0) {
	echo 'Le nouvel utilisateur a bien été enregistré<br>',
			'Il a le numéro ', mysqli_insert_id($GLOBALS['bd']);
} else {
	echo '<strong>Les erreurs suivantes ont été détectées :</strong>';
	for ($i = 0; $i < $nbErr; $i++) {
		echo '<br>', $erreurs[$i];
	}
}

echo '</main></body></html>';

if (isset($GLOBALS['bd'])){
    // Déconnexion de la base de données
    mysqli_close($GLOBALS['bd']);
}
ob_end_flush();

//=================== FIN DU SCRIPT =============================

//_______________________________________________________________
//
//		FONCTIONS LOCALES
//_______________________________________________________________

/**
* Validation de la saisie et création d'un nouvel utilisateur.
*
* Les zones reçues du formulaires de saisie sont vérifiées. Si
* des erreurs sont détectées elles sont renvoyées sous la forme
* d'un tableau. Si il n'y a pas d'erreurs, un enregistrement est
* créé dans la table utilisateur.
*
* @global array		$_POST		zones de saisie du formulaire
*
* @return array 	Tableau des erreurs détectées
*/
function fdl_add_utilisateur() {
	//-----------------------------------------------------
	// Vérification des zones
	//-----------------------------------------------------
	$erreurs = array();

	// Vérification du nom
	$txtNom = trim($_POST['txtNom']);
	$long = mb_strlen($txtNom, 'UTF-8');
	if ($long < 4
	|| $long > 30)
	{
		$erreurs[] = 'Le nom doit avoir de 4 à 30 caractères';
	}

	// Vérification du mail
	$txtMail = trim($_POST['txtMail']);
	if ($txtMail == '') {
		$erreurs[] = 'L\'adresse mail est obligatoire';
	} elseif (mb_strpos($txtMail, '@', 0, 'UTF-8') === FALSE
			|| mb_strpos($txtMail, '.', 0, 'UTF-8') === FALSE)
	{
		$erreurs[] = 'L\'adresse mail n\'est pas valide';
	} else {
		// Vérification que le mail n'existe pas dans la BD
		fd_bd_connexion();

		$mail = mysqli_real_escape_string($GLOBALS['bd'], $txtMail);

		$S = "SELECT	count(*)
				FROM	utilisateur
				WHERE	utiMail = '$mail'";

		$R = mysqli_query($GLOBALS['bd'], $S) or fd_bd_erreur($S);

		$D = mysqli_fetch_row($R);

		if ($D[0] > 0) {
			$erreurs[] = 'Cette adresse mail est déjà inscrite.';
		}
		// Libère la mémoire associée au résultat $R
        mysqli_free_result($R);
	}

	// Vérification du mot de passe
	$txtPasse = trim($_POST['txtPasse']);
	$long = mb_strlen($txtPasse, 'UTF-8');
	if ($long < 4
	|| $long > 20)
	{
		$erreurs[] = 'Le mot de passe doit avoir de 4 à 20 caractères';
	}

	$txtVerif = trim($_POST['txtVerif']);
	if ($txtPasse != $txtVerif) {
		$erreurs[] = 'Le mot de passe est différent dans les 2 zones';
	}

	// Vérification de la date
	$selJour = (int) $_POST['selDate_j'];
	$selMois = (int) $_POST['selDate_m'];
	$selAnnee = (int) $_POST['selDate_a'];
	if (! checkdate($selMois, $selJour, $selAnnee)) {
		$erreurs[] = 'La date n\'est pas valide';
	} else {
		$amj = ($selAnnee * 10000) + ($selMois * 100) + $selJour;
		if ( $amj != date('Ymd')) {
			$erreurs[] = 'La date doit être celle du jour';
		}
	}

	// Si il y a des erreurs, la fonction renvoie le tableau d'erreurs
	if (count($erreurs) > 0) {
		return $erreurs;		// RETURN : des erreurs ont été détectées
	}

	//-----------------------------------------------------
	// Insertion d'un nouvel utilisateur dans la base de données
	//-----------------------------------------------------
	$txtPasse = mysqli_real_escape_string($GLOBALS['bd'], md5($txtPasse));
	$nom = mysqli_real_escape_string($GLOBALS['bd'], $txtNom);
	$txtMail = mysqli_real_escape_string($GLOBALS['bd'], $txtMail);
	$utiDateInscription = date('Ymd');

	$S = "INSERT INTO utilisateur SET
			utiNom = '$nom',
			utiPasse = '$txtPasse',
			utiMail = '$txtMail',
			utiDateInscription = $utiDateInscription,
			utiJours = 127,
			utiHeureMin = 6,
			utiHeureMax = 22";

	$R = mysqli_query($GLOBALS['bd'], $S) or fd_bd_erreur($S);

	return $erreurs;		// RETURN : un tableau vide = pas d'erreur
}
?>
