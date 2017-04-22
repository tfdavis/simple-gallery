<?php
/** 
 *  This is the controller for my photo gallery system.
 * 
 * 
 * 
 * 
 */

/******************************************************************************/
/* Requires and Globals                                                       */

require_once getcwd() . '/lib/vendor/autoload.php';

include_once getcwd() . '/gallery_conf.php';

$templates_dir = getcwd() . '/views';
$cache_dir     = getcwd() . '/cache';


/******************************************************************************/
/* The Controller-y type thing.                                               */

$loader = new Twig_Loader_Filesystem($templates_dir);
$twig = new Twig_Environment($loader, array(
    'cache' => $cache_dir,
));

$the_path = $_SERVER['REQUEST_URI'];

$data = Array();
if ($the_path == '/') {
	print 'welcome.';
	exit;
} elseif (in_array($the_path, array_keys($galleries))) {
	echo $twig->render('gallery.html', 
		array( 'gallery' => $galleries[$the_path] )
	);
	exit;
} else {
	print "Photo Gallery does not exist at that URL";
	exit;
}


?>
