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

// on récupère la valeur de l'id de l'utilisateur courant grâce à la session
$id = $_SESSION['utiID'];

// on vérifie que la personne est identifier
bp_estConnecter();

if (isset($GLOBALS['bd'])){
    // Déconnexion de la base de données
    mysqli_close($GLOBALS['bd']);
}

// connexion a la base 
fd_bd_connexion();

//-----------------------------------------------------
// Affichage de la page
//-----------------------------------------------------
fd_html_head('24sur7 | Recherche');
fd_html_bandeau('APP_PAGE_RECHERCHE');

echo '<div id="bcContenu">',
     '<form method="POST" action="recherche.php">',
     '<table border="1" cellpadding="4" cellspacing="0">',

fd_form_ligne('Entrez le critère de recherche : ',
              '<INPUT type=APP_Z_TEXT name="recherche" value="" size="40" >
              <INPUT type="submit" id="btnNormal" name="btnRecherche" value="Rechercher"></table>');
    
if(!isset($_POST['btnRecherche'])){
    // si on a déja un motif d'enregistrer, on affiche le tableau des résultats : 
    if(isset($_SESSION['motif'])){        
        afficheRes($_SESSION['motif']);
    }
}else{ // sinon on interroge la base avec le motif saisie
   if(isset($_POST['recherche'])){        
        afficheRes($_POST['recherche']);
    }
}

if(!isset($_POST['btnDesabo'])){
    // affichage normal de la page  
}else{
    // on execute la requete de désabonnement : c'est une requete delete
    $utiID = $_POST['IDUtilisateurDesabo'];
    $Sdelete="DELETE FROM suivi 
              WHERE suiIDSuiveur = '$id'
              AND suiIDSuivi = '$utiID'";
    $Rdelete = mysqli_query($GLOBALS['bd'], $Sdelete) or fd_bd_erreur($Sdelete);
    
     mysqli_free_result($Rdelete);
    
    fd_redirige("recherche.php");
}

if(!isset($_POST['btnAbo'])){
    // affichage normal de la page  
}else{
// on execute la requete d'abonnement : c'est une requete insert
    $utiID = $_POST['IDUtilisateurAbo'];

    $Sajout="INSERT INTO suivi 
         VALUES ('$id','$utiID')";
    $Rajout = mysqli_query($GLOBALS['bd'], $Sajout) or fd_bd_erreur($Sajout);
    
    mysqli_free_result($Rdelete);
     
    fd_redirige("recherche.php");
}

echo '</div>';


//-----------------------------------------------------
// Fin de la page
//-----------------------------------------------------

fd_html_pied();

ob_end_flush();

?>