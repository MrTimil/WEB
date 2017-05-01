<?php


// Bufferisation des sorties
ob_start();

// Inclusion de la bibliothéque
include('bibli_24sur7.php');

//-----------------------------------------------------
// Détermination de la phase de traitement :
// 1er affichage ou soumission du formulaire
//-----------------------------------------------------
if (! isset($_POST['btnValider'])) {
    // On n'est dans un premier affichage de la page.
    // => On intialise les zones de saisie.
    $nbErr = 0;
    $_POST['txtNom'] = $_POST['txtMail'] = '';
    $_POST['txtVerif'] = $_POST['txtPasse'] = '';
    $_POST['selDate_a'] = 2000;
    $_POST['selDate_m'] = $_POST['selDate_j'] = 1;

} else {
    // On est dans la phase de soumission du formulaire :
    // => vérification des valeurs reçues et création utilisateur.
    // Si aucune erreur n'est détectée, fdl_add_utilisateur()
    // redirige la page sur la page 'protegee.php'
    $erreurs = fdl_add_utilisateur();
    $nbErr = count($erreurs);
}

if (isset($GLOBALS['bd'])){
    // Déconnexion de la base de données
    mysqli_close($GLOBALS['bd']);
}


//-----------------------------------------------------
// Affichage de la page
//-----------------------------------------------------
fd_html_head('24sur7 | Inscription');
fd_html_bandeau('0');

echo '<div id="bcContenu">
        <p>Pour vous inscrire à <strong>24sur7</strong>, veuillez remplir le formulaire ci-dessous.</p>';

// Si il y a des erreurs on les affiche
if ($nbErr > 0) {
	echo '<strong>Les erreurs suivantes ont été détectées</strong>';
	for ($i = 0; $i < $nbErr; $i++) {
		echo '<br>', $erreurs[$i];
	}
}
    
// Affichage du formulaire
echo '<form method="POST" action="inscription.php">',
		'<table border="1" cellpadding="4" cellspacing="0">',
		fd_form_ligne('Nom', 
            fd_form_input(APP_Z_TEXT,'txtNom', $_POST['txtNom'], 40)),
		fd_form_ligne('Email', 
            fd_form_input(APP_Z_TEXT,'txtMail', $_POST['txtMail'], 40)),
		fd_form_ligne('Mot de passe', 
            fd_form_input(APP_Z_PASS,'txtPasse', '', 40)),
        fd_form_ligne('R&eacute;p&eacute;tez votre mot de passe', 
            fd_form_input(APP_Z_PASS,'txtVerif', '', 40)),
         fd_form_ligne( 
             fd_form_input(APP_Z_SUBMIT,'btnValider', 'S\'inscrire'),
             fd_form_input(APP_Z_RESET,'btnReset','Annuler')
         ),
		'</table></form>';

echo '<p>Déja inscrit ? <a href="./identification.php">Identifiez-vous !</a>';
echo '<p>Vous hésitez à vous inscrire ? Laissez-vous séduire par <a href="./presentation.html">une présentation !</a> des possibilités de 24sur7</p></div>';

fd_html_pied();

ob_end_flush();


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
* créé dans la table utilisateur, une session est ouverte et une
* redirection est effectuée.
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
		
		$ret = mysqli_set_charset($GLOBALS['bd'], "utf8");
        if ($ret == FALSE){
            fd_bd_erreurExit('Erreur lors du chargement du jeu de caractères utf8');
        }

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

	$S = "INSERT INTO utilisateur SET
			utiNom = '$nom',
			utiPasse = '$txtPasse',
			utiMail = '$txtMail',
			utiDateInscription = ".date('jnY').",
			utiJours = 127,
			utiHeureMin = 6,
			utiHeureMax = 22";

	$R = mysqli_query($GLOBALS['bd'], $S) or fd_bd_erreur($S);

	//-----------------------------------------------------
	// Ouverture de la session et redirection vers la page agenda.php
	//-----------------------------------------------------
	session_start();
	$_SESSION['utiID'] = mysqli_insert_id($GLOBALS['bd']);
	$_SESSION['utiNom'] = $txtNom;
	
	// Déconnexion de la base de données
    mysqli_close($GLOBALS['bd']);
	
	header ('location: agenda.php');
	exit();			// EXIT : le script est terminé
}



   

?>