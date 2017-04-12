<?php
    // Bufferisation des sorties
ob_start();

// Inclusion de la bibliothéque
include('bibli_24sur7.php');
session_start();



// on récupère les valeurs de l'utilisateur courant grace a la session
$id=$_SESSION['utiID'];
$utiNom = $_SESSION['utiNom'];
$utiMail =  $_SESSION['utiMail'];
//print_r($_SESSION);
//echo $id;
if (!isset($_SESSION['utiID'])){
    header("Location: ./identification.php");
	exit();
}

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
    $erreurs = fdl_modif_utilisateur();
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
fd_html_bandeau("APP_PAGE_PARAMETRES");
fd_bd_connexion();



echo '<div id="bcContenu">', 
     '<h2>Informations sur votre compte</h2>',
     '<hr>';
    
//-----------------------------------------------------
// Partie 1
//-----------------------------------------------------

echo '<form method="POST" action="parametres.php">',
    '<table border="1" cellpadding="4" cellspacing="0">',
    fd_form_ligne('Nom', 
        fd_form_input(APP_Z_TEXT,'utiNom', $utiNom, 40)),
    fd_form_ligne('Email', 
        fd_form_input(APP_Z_TEXT,'utiMail', $utiMail, 40)),
    fd_form_ligne('Mot de passe', 
        fd_form_input(APP_Z_PASS,'txtPasse', '', 30)),
    fd_form_ligne('R&eacute;p&eacute;tez votre mot de passe', 
        fd_form_input(APP_Z_PASS,'txtVerif', '', 30)),
     fd_form_ligne( 
         fd_form_input(APP_Z_SUBMIT,'btnValider', 'Mettre à jour'),
         fd_form_input(APP_Z_RESET,'btnReset','Annuler')
     ),
    '</table></form>';

// Vérification des valeurs saisie 
/*
nom utilisateur non vide, mail correct 
    deux mdp identique 
    on ne modifie le mdp seulement si les deux champs sont rempli, sinon on modifie le nom et le mail 
  */      



//-----------------------------------------------------
// Partie 2
//-----------------------------------------------------

echo '<h2>Options d\'affichage du calendrier</h2><hr>';

echo '<form method="POST" action="parametres.php">',
    '<table border="1" cellpadding="4" cellspacing="0">',
fd_form_ligne('Jours affichés','<INPUT type="checkbox" name="day1" value="1"> Lundi
    <INPUT type="checkbox" name="day2" value="2"> Mardi
    <INPUT type="checkbox" name="day3" value="3"> Mercredi'),
fd_form_ligne('','<INPUT type="checkbox" name="day4" value="4"> Jeudi
    <INPUT type="checkbox" name="day5" value="5"> Vendredi
    <INPUT type="checkbox" name="day6" value="6"> Samedi'),
fd_form_ligne('','<INPUT type="checkbox" name="day7" value="7"> Dimanche'),
    
fd_form_ligne('Heure minimale','<SELECT name="hMin">
<OPTION>1:00
<OPTION>2:00
<OPTION>3:00
<OPTION>4:00
<OPTION>5:00
<OPTION selected >6:00 
<OPTION>7:00
<OPTION>8:00
<OPTION>9:00
<OPTION>10:00
<OPTION>11:00
<OPTION>12:00
<OPTION>13:00
<OPTION>14:00
<OPTION>15:00
<OPTION>16:00
<OPTION>17:00
<OPTION>18:00
<OPTION>19:00
<OPTION>20:00
<OPTION>21:00
<OPTION>22:00
<OPTION>23:00
</SELECT>'),
fd_form_ligne('Heure maximale','<SELECT name="hMax">
<OPTION>1:00
<OPTION>2:00
<OPTION>3:00
<OPTION>4:00
<OPTION>5:00
<OPTION>6:00 
<OPTION>7:00
<OPTION>8:00
<OPTION>9:00
<OPTION>10:00
<OPTION>11:00
<OPTION>12:00
<OPTION>13:00
<OPTION>14:00
<OPTION>15:00
<OPTION>16:00
<OPTION>17:00
<OPTION>18:00
<OPTION>19:00
<OPTION>20:00
<OPTION>21:00
<OPTION  selected >22:00
<OPTION>23:00
</SELECT>'),

fd_form_ligne( 
         fd_form_input(APP_Z_SUBMIT,'btnValider', 'Mettre à jour'),
         fd_form_input(APP_Z_RESET,'btnReset','Annuler')
    );

//-----------------------------------------------------
// Partie 3
//-----------------------------------------------------

echo '</table></form>', '<h2>Vos catégories</h2><hr>';


echo '</div>';
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
function fdl_modif_utilisateur() {
	//-----------------------------------------------------
	// Vérification des zones
	//-----------------------------------------------------
	$erreurs = array();

	// Vérification du nom
	$txtNom = trim($_POST['txtNom']);
	$long = mb_strlen($txtNom, 'UTF-8');
	if ($long > 0){
		$erreurs[] = 'Le nom doit avoir plus de 1 caractères';
	}

	// Vérification du mail
	$txtMail = trim($_POST['txtMail']);
	if ($txtMail == ''){
		$erreurs[] = 'L\'adresse mail est obligatoire';
	}elseif (mb_strpos($txtMail, '@', 0, 'UTF-8') === FALSE
			|| mb_strpos($txtMail, '.', 0, 'UTF-8') === FALSE){
		$erreurs[] = 'L\'adresse mail n\'est pas valide';
	}else{
		// Vérification que le mail n'existe pas dans la BD
		fd_bd_connexion();
		
		$ret = mysqli_set_charset($GLOBALS['bd'], "utf8");
        if ($ret == FALSE){
            fd_bd_erreurExit('Erreur lors du chargement du jeu de caractères utf8');
        }

		$mail = mysqli_real_escape_string($GLOBALS['bd'], $txtMail);

		$S = "SELECT	*
				FROM	utilisateur
				WHERE	utiMail = '$mail'
                AND     utiNom ='$txtNom'";

		$R = mysqli_query($GLOBALS['bd'], $S) or fd_bd_erreur($S);

		$D = mysqli_fetch_row($R);

		if (count($D) != 0) {
			$erreurs[] = 'Le couple nom/ mot de passe existe déja dans la base, il n\'y a pas de modification a effectué';
		}
		// Libère la mémoire associée au résultat $R
        mysqli_free_result($R);
	}

	// Vérification du mot de passe*
    $txtPasse = trim($_POST['txtPasse']);
    if(isset($txtPasse)){
        $long = mb_strlen($txtPasse, 'UTF-8');
        if ($long < 4
        || $long > 20){
            $erreurs[] = 'Le mot de passe doit avoir de 4 à 20 caractères';
        }

        $txtVerif = trim($_POST['txtVerif']);
        if ($txtPasse != $txtVerif) {
            $erreurs[] = 'Le mot de passe est différent dans les 2 zones';
        }
        $modifPassword=1;
    }
	
    
	
	// Si il y a des erreurs, la fonction renvoie le tableau d'erreurs
	if (count($erreurs) > 0) {
		return $erreurs;		// RETURN : des erreurs ont été détectées
	}

	//-----------------------------------------------------
	// Modification de l'utilisateur dans la base de données
	//-----------------------------------------------------
	$txtPasse = mysqli_real_escape_string($GLOBALS['bd'], md5($txtPasse));
	$nom = mysqli_real_escape_string($GLOBALS['bd'], $txtNom);
	$txtMail = mysqli_real_escape_string($GLOBALS['bd'], $txtMail);

	if($modifPassword ==1){
         $S = "UPDATE `utilisateur` 
          SET 
            utiNom= '$nom',
            utiMail= '$txtMail',
            utiPasse`='$txtPasse' 
          WHERE utiID = '$id'";
    }else{
         $S = "UPDATE `utilisateur` 
          SET 
            utiNom= '$nom',
            utiMail= '$txtMail',
          WHERE utiID = '$id'";   
    }
    
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