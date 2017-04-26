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

// TODO - error check the config file to make sure / and /auth are not used 
// as paths.

$loader = new Twig_Loader_Filesystem($templates_dir);
$twig = new Twig_Environment($loader, array(
    'cache' => $cache_dir,
));

$the_path = $_SERVER['REQUEST_URI'];

session_start();

$data = Array();
if ($the_path == '/') {
	echo $twig->render('welcome.html', 
		array( 
			'title' => $gallery['title'],
			'index_thumbs' => get_index_thumbs()
		)
	);
} elseif ($the_path == '/auth') {
	$req_path = $_POST['reqpath'];
	// TODO - sanitize these input variables: pw, reqpath
	if ($_POST['pw'] == $galleries[$req_path]['password']) {
		authorize($req_path);
		header("Location: $req_path"); exit();
	} else {
		echo $twig->render('authorize.html' ,
			array( 
				'gallery'        => $galleries[$the_path],
				'requested_path' => $req_path,
				'message'        => 'Incorrect Password.  Try again.'
			)
		);
	}
} elseif (in_array($the_path, array_keys($galleries))) {
	if (isset($galleries[$the_path]['password']) && !is_authorized($the_path) ) {
		echo $twig->render('authorize.html' ,
			array( 
				'gallery'        => $galleries[$the_path],
				'requested_path' => $the_path
			)
		);
	}  else {
		echo $twig->render('gallery.html', 
			array( 'gallery' => $galleries[$the_path] )
		);
	}
} elseif ($the_path == '/reset') {
	unset($_SESSION['authorized_paths']);
	print "reset done";

} else {
	print "Photo Gallery does not exist at that URL";
	exit;
}


/******************************************************************************/
/* Functions                                                                  */

function get_index_thumbs() {
	global $galleries;
	$thumbs = Array();
	foreach ($galleries as $path => $gallery) {
		if (isset($gallery['index_thumb'])) {
			array_push($thumbs, Array(
				'gallery_thumb' => $gallery['asset_dir'] . '/' . $gallery['index_thumb'],
				'gallery_path'  => $path
			));
		}
	}
	return($thumbs);
}

function debug() {
	print "<pre style=\"color:#f00;\">";
	print_r($_SESSION);
	print "</pre>";
}

function authorize($path) {
	if (isset($_SESSION['authorized_paths']) || !is_array($_SESSION['authorized_paths']) ) {
		$_SESSION['authorized_paths'] = Array();
	}
	array_push($_SESSION['authorized_paths'], $path);
	return true;
}

function is_authorized($path) {
	if (! isset($_SESSION['authorized_paths'])) {
		return false;
	} else {
		return in_array($path, $_SESSION['authorized_paths']);
	}
}

?>
