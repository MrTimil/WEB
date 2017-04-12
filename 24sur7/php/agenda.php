<?php
/** @file
 * Page d'accueil de l'application 24sur7
 *
 * @author : Frederic Dadeau - frederic.dadeau@univ-fcomte.fr
 */

include('bibli_24sur7.php');	// Inclusion de la bibliothéque

session_start();
fd_html_head('24sur7 | Agenda');

fd_html_bandeau(APP_PAGE_AGENDA);

echo '<section id="bcContenu">',
		'<aside id="bcGauche">';

$jourAff=0;
$moisAff=0;
$anneeAff=0;
if(isset($_GET['jour'])){
    $jourAff=$_GET['jour'];
}
if(isset($_GET['mois'])){
    $moisAff=$_GET['mois'];
}
if(isset($_GET['annee'])){
    $anneeAff=$_GET['annee'];
}


fd_html_calendrier($jourAff,$moisAff,$anneeAff);
$firstDay = $jourAff - ($jourAff % 6); // trouver une vrai formule 
echo		'<section id="categories">',
				'Ici : bloc catégories pour afficher les catégories de rendez-vous',
			'</section>',
		'</aside>',
		'<section id="bcCentre">',
			bp_html_semainier($firstDay),
		'</section>',
	'</section>';

fd_html_pied();
?>
