<?php
    
/**
* Projet WEB 
*
* @author : Emile PAQUETTE & Romain BOISSON
*/


// Bufferisation des sorties
ob_start();

// Inclusion de la bibliothéque
include('bibli_24sur7.php');

session_start();

// on récupère les valeurs de l'utilisateur courant grace a la session
$id =$_SESSION['utiID'];
$utiNom = $_SESSION['utiNom'];
$utiMail =  $_SESSION['utiMail'];

// on vérifie que la personne est identifier
bp_estConnecter();

// On est dans un premier affichage de la page.
// => On intialise les zones de saisie.
if(! isset($_POST['btnMaj'])){
    $nbErr = 0;
    $_POST['utiNom'] = $_POST['utiMail'] = '';
    $_POST['txtVerif'] = $_POST['txtPasse'] = '';
}else{
    // On est dans la phase de soumission du formulaire :
    // => vérification des valeurs reçues et modifications utilisateur
    // Si aucune erreur n'est détectée, fdl_modif_utilisateur()
    
    $erreurs = fdl_modif_utilisateur($id,$utiNom,$utiMail);
    $nbErr = count($erreurs);
}

if(isset($GLOBALS['bd'])){
        // Déconnexion de la base de données
        mysqli_close($GLOBALS['bd']);
    }

// connexion a la base 
fd_bd_connexion();

//-----------------------------------------------------
// Affichage de la page
//-----------------------------------------------------
fd_html_head('24sur7 | Paramètres');
fd_html_bandeau("APP_PAGE_PARAMETRES");

echo '<div id="bcContenu">', 
     '<h2>Informations sur votre compte</h2>',
     '<hr>';

// Si il y a des erreurs on les affiche
if ($nbErr > 0) {
	echo '<strong>Les erreurs suivantes ont été détectées</strong>';
	for ($i = 0; $i < $nbErr; $i++) {
		echo '<br>', $erreurs[$i];
	}
}
    
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
        fd_form_input(APP_Z_SUBMIT,'btnMaj', 'Mettre à jour','0','btnNormal'),
         fd_form_input(APP_Z_RESET,'btnReset','Annuler','0','btnNormal')
     ),
    '</table></form>';

//-----------------------------------------------------
// Partie 2
//-----------------------------------------------------

// récupération des infos sur l'utilisateur actuel dans la base 

$SJours = " SELECT * 
            FROM utilisateur
            WHERE utiID='$id'";

$RJours = mysqli_query($GLOBALS['bd'], $SJours) or fd_bd_erreur($SJours);

$DJours = mysqli_fetch_assoc($RJours);

$utiJours = $DJours['utiJours'];

// on passe en binaire les valeurs des checkbox
$jours = decbin("$utiJours");

// on passe ca dans un tableau pour avoir accès a chaque caractère de la chaine
$tabJours=str_split($jours);

// si les checkbox ne sont pas cochés, ont leur met une valeur = 0 (1 si coché)
for ($i = 0; $i < 7 ; $i++ ){
        if (!isset($tabJours[$i])){
            $tabJours[$i] = 0;
        }
    }

// Libère la mémoire associée au résultat $RJours
mysqli_free_result($RJours);

//-----------------------------------------------------
// Affichage de la partie 2
//-----------------------------------------------------

echo '<h2>Options d\'affichage du calendrier</h2><hr>',

     '<form method="POST" action="parametres.php">',
     '<table border="1" cellpadding="4" cellspacing="0">',
fd_form_ligne('Jours affichés','
    <INPUT type="checkbox" name="day1" value="1" '.cocheCase($tabJours[0]).' > Lundi
    <INPUT type="checkbox" name="day2" value="1" '.cocheCase($tabJours[1]).' > Mardi
    <INPUT type="checkbox" name="day3" value="1" '.cocheCase($tabJours[2]).' > Mercredi'),
fd_form_ligne('','
    <INPUT type="checkbox" name="day4" value="1" '.cocheCase($tabJours[3]).' > Jeudi
    <INPUT type="checkbox" name="day5" value="1" '.cocheCase($tabJours[4]).' > Vendredi
    <INPUT type="checkbox" name="day6" value="1" '.cocheCase($tabJours[5]).' > Samedi'),
fd_form_ligne('','
    <INPUT type="checkbox" name="day7" value="1" '.cocheCase($tabJours[6]).' > Dimanche'),

 fd_form_ligne('Heure minimale',afficheHoraireMin($id,$DJours['utiHeureMin'])),
 fd_form_ligne('Heure maximale',afficheHoraireMax($id,$DJours['utiHeureMax'])),
     
fd_form_ligne( 
     fd_form_input(APP_Z_SUBMIT,'btnMaj2', 'Mettre à jour','0','btnNormal'),
     fd_form_input(APP_Z_RESET,'btnReset2','Annuler','0','btnNormal')),
'</table></form>';

//-----------------------------------------------------
// Modifications partie 2
//-----------------------------------------------------

if (!isset($_POST['btnMaj2'])){
    //affichage normal de la page avec les valeur contenu dans la base
}else{
  // on récupère les valeurs des checkboxs
    for ($j = 0; $j < 7 ; $j++ ){
        if (!isset($_POST['day'.$j])){
            $_POST['day'.$j] = 0;
        }
    }
    
    $chaine= $_POST['day1'].$_POST['day2'].$_POST['day3'].$_POST['day4'].$_POST['day5'].$_POST['day6'].$_POST['day7'];

    $chaineBinaire = bindec($chaine); // on met les informations en binaire et on met a jour dans la base 
    
    // mise a jour dans la base selon les informations saisies
    updateCase($id,$chaineBinaire,(int)$_POST['hMin'],(int)$_POST['hMax']);
}

//-----------------------------------------------------
// Partie 3
//-----------------------------------------------------

echo '<h2>Vos catégories</h2><hr>'; 

// requete de selection des catégories 
$S = "SELECT *
      FROM categorie
      WHERE catIDUtilisateur = $id"; 

$R = mysqli_query($GLOBALS['bd'], $S) or fd_bd_erreur($S);

$_SESSION['countCategorie']=0;

while($D = mysqli_fetch_assoc($R)){
    // on compte le nombre de catégorie pour ne pas autoriser la suppression si il n'en reste qu'une 
    $_SESSION['countCategorie']++;
    echo '<form method="POST" action="parametres.php">',
    '<table border="1" cellpadding="4" cellspacing="0">';
    
    // permet d'ajouter "checked" si les cases sont cochés, un chaine vide sinon
    if($D['catPublic'] == 1){
        $public="checked";
    }else{
        $public="";
    }
    
    // permet de passer le numéro de la catégorie a modifier
    echo '<INPUT type="hidden" name="catID" value='.$D['catID'].'>'; 
    
    echo fd_form_ligne(
        'Nom : ',
       '<INPUT type="APP_Z_TEXT" name="catNom" value='.$D['catNom'].' size="15" >
        Fond : 
        <INPUT type="APP_Z_TEXT" name="catCouleurFond" value='.$D['catCouleurFond'].' size="15" >
        Bordure : 
        <INPUT type="APP_Z_TEXT" name="catCouleurBordure" value='.$D['catCouleurBordure'].' size="15" >

        <INPUT type="checkbox" name="catPublic" value="1" '.$public.'> Public

        <span style=
            padding:4px;font-family:arial;border-color:#'.$D['catCouleurBordure'].';border-style:solid;border-width:3px;width:50px;height:20px;color:black;background-color:#'.$D['catCouleurFond'].';>
            Aperçu
        </span>

        <INPUT type="submit" id="btnImgSave" name="btnMaj3" value="">
        <INPUT type="submit" id="btnImgStop" name="btnDelete3" value="">'),
    
        '</table></form>';
}

mysqli_free_result($R);

echo          
fd_form_ligne('<h3>Nouvelle catégorie :</h3>',''),

'<form method="POST" action="parametres.php">',
'<table border="1" cellpadding="4" cellspacing="0">',

fd_form_ligne('Nom : ',
'<INPUT type=APP_Z_TEXT name="catNomNew" value="" size="15" >
 Fond : 
 <INPUT type=APP_Z_TEXT name="catCouleurFondNew" value="" size="15" >
 Bordure : 
 <INPUT type=APP_Z_TEXT name="catCouleurBordureNew" value="" size="15" >
 <INPUT type="checkbox"  name="catPublicNew" value="1"> Public
 <INPUT type="submit" id="btnNormal" name="btnAjout" value="Ajouter">');

echo '</table></form></div>';

//-----------------------------------------------------
// Modification d'une catégorie
//-----------------------------------------------------

if (!isset($_POST['btnMaj3'])){
    //affichage normal de la page avec les valeur contenu dans la base
}else{
    // l'utilisateur demande la modification d'une catégorie, on appelle la fonction modiCat pour faire les modifications
    
    if (!isset($_POST['catPublic'])){
        $_POST['catPublic']=0;
    }
    
  modifCat($id,$_POST['catID'],$_POST['catNom'],$_POST['catCouleurFond'],$_POST['catCouleurBordure'],$_POST['catPublic']);
}

//-----------------------------------------------------
// suppression d'une catégorie
//-----------------------------------------------------

if(!isset($_POST['btnDelete3'])){
    // affichage normal de la page  
}else{
    // l'utilisateur demande la suppression d'une catégorie, on récupère la catégorie qu'il veut supprimer (son identifiant ) et on envoi ça a la fonction de suppression
    // on vérifie qu'il reste au moins deux catégorie, si une seule restante, on affiche un message d'erreur
    
    if($_SESSION['countCategorie']==1){ // l'utilisateur ne possède plus qu'une catégorie
       echo '<div id="bcContenuErreur"> Impossible de supprimer la catégorie, une catégorie est nécessaire, pour supprimer celle-ci, creer d\'abord une autre catégorie</div>';
    }else{
       echo '<div id="bcContenuErreur">
            <form method="POST" action="parametres.php">
            <table border="1" cellpadding="4" cellspacing="0">',
        fd_form_ligne('Supprimer la catégorie "'.$_POST['catNom'].'" et les rendez-vous associés ?',
                        '<INPUT type="submit" id="btnNormal" name="btnConfirmation" value="Confirmer">'),
            '</table>
             </form>
             </div>';
        $_SESSION['catID']=$_POST['catID']; // on stocke la valeur de catID dans une variable de session pour pouvoir y avoir accès lors de l'affichage de la page paramètres suite a l'appui du bouton de confirmation de suppression d'une catégorie
    }
}

if(!isset($_POST['btnConfirmation'])){
     // affichage normal de la page 
}else{
    deleteCat($id,$_SESSION['catID']);
}

//-----------------------------------------------------
// ajout d'une catégorie
//-----------------------------------------------------

if(!isset($_POST['btnAjout'])){
    // affichage normal de la page
}else{    
     if (!isset($_POST['catPublicNew'])){
        $_POST['catPublicNew']=0;
    }
    ajoutCat($id,$_POST['catNomNew'],$_POST['catCouleurFondNew'],$_POST['catCouleurBordureNew'],$_POST['catPublicNew']);
}

//-----------------------------------------------------
// Fin de la page
//-----------------------------------------------------

fd_html_pied();

ob_end_flush();


//_______________________________________________________________
//
//		FONCTIONS LOCALES
//_______________________________________________________________

/**
* Validation de la saisie et modification de l'utilisateur.
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
function fdl_modif_utilisateur($id,$utiNom,$utiMail){
	//-----------------------------------------------------
	// Vérification des zones
	//-----------------------------------------------------
	$erreurs = array();

	// Vérification du nom
	$txtNom = trim($_POST['utiNom']);
	$long = mb_strlen($txtNom, 'UTF-8');
	if ($long < 1
    ){
		$erreurs[] = 'Le nom doit faire plus d\'un caractère';
	}

	// Vérification du mail
	$txtMail = trim($_POST['utiMail']);
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
        
        
        // Vérification du mot de passe*
    $txtPasse = trim($_POST['txtPasse']);
    $password = mb_strlen($txtPasse, 'UTF-8');
    $modifPassword=0;
    if($password >0){
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
        
        
		if (count($D) != 0 && $modifPassword==0) {
			$erreurs[] = 'Le couple nom/ mot de passe existe déja dans la base. Aucune modification n\'a été effectué';
		}
		// Libère la mémoire associée au résultat $R
        mysqli_free_result($R);
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
            utiNom = '$nom',
            utiMail = '$txtMail',
            utiPasse ='$txtPasse' 
          WHERE utiID = '$id' ";
    }else{
         $S = "UPDATE `utilisateur` 
          SET 
            utiNom = '$nom',
            utiMail = '$txtMail'
            WHERE utiID = '$id'";   
    }
    
	$R = mysqli_query($GLOBALS['bd'], $S) or fd_bd_erreur($S);
    
    

	//-----------------------------------------------------
	// Ouverture de la session et redirection vers la page parametres.php
	//-----------------------------------------------------
	session_start();
	
	// on a modifier dans la base, on modifie les infos de session maitenant : 
    $_SESSION['utiNom']=$txtNom;
    $_SESSION['utiMail']=$txtMail;
	// Déconnexion de la base de données
    mysqli_close($GLOBALS['bd']);
	
	header ('location: parametres.php');
	exit();			// EXIT : le script est terminé
}
?>