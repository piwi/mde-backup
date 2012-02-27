<?php
/**
 * Application in development ....
 *
 * Command to process multimarkdown version:
 *
 *     ~$ multimarkdown -o MD_syntax.html MD_syntax.md
 */

// ------------------------------
// COMMONS
// ------------------------------

// show errors at least initially
ini_set('display_errors','1'); error_reporting(E_ALL ^ E_NOTICE);

// set a default timezone to avoid PHP5 warnings
$tmz = date_default_timezone_get();
date_default_timezone_set( !empty($tmz) ? $tmz : 'Europe/Paris' );

// ------------------------------
// PROCESS
// ------------------------------

$test_file = 'MD_syntax.md';
$file_content = file_get_contents( $test_file );
$alt_content = '';

if (!empty($_GET) && isset($_GET['type'])) {
	switch($_GET['type']) {

		case 'markdown':
			require 'PHP_Markdown_1.0.1o/markdown.php';
			break;

		case 'markdownextra':
			require 'PHP_Markdown_Extra_1.2.5/markdown.php';
			break;

		case 'multimarkdown':
			$ok = exec('which multimarkdown');
			if (!empty($ok)) {
				$pwd = realpath( dirname(__FILE__) );
				$md_content = exec("cd $pwd && $ok $test_file", $md_content_tbl);
				if (!empty($md_content_tbl)) $md_content = join("\n", $md_content_tbl);
				else trigger_error( "An error occured while processing 'multimarkdown' command !", E_USER_ERROR );
			} else {
				$html = 'MD_syntax.html';
				if (file_exists($html))
					$md_content = file_get_contents($html);
				else
				trigger_error( "Command 'multimarkdown' not found in your system ! (see https://github.com/fletcher/peg-multimarkdown/downloads/)", E_USER_ERROR );
			}
			break;

		case 'fullphpmarkdown':
			require 'Full_PHP_Markdown/markdown.php';
			break;

		case 'minifullphpmarkdown':
			require 'Full_PHP_Markdown/markdown.mini.php';
			break;

		case 'reminders':
			require 'Full_PHP_Markdown/markdown.php';
			$alt_content = file_get_contents( 'Full_PHP_Markdown/markdown_cheat_sheet.html' );
			break;

		default:break;
	}
	if (empty($md_content))
		$md_content = Markdown( $file_content );

} else {
	$md_content = $file_content;
}

// ------------------------------
// VIEW
// ------------------------------

	echo <<<EOT
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<title>test Markdown parser</title>
	<style>
body {
	font-size: 0.8125em; font-family: Lucida Grande, Verdana, Sans-serif; 
	background: #fff; padding: 0; margin: 0; color: #4F5155; }
ul           { padding: 0 0 0 20px; }
h1           { font-size: 160%; }
h2           { font-size: 140%; }
h3           { font-size: 120%; }
div, span, p { padding:0; margin: 0; }
ol, ul       { padding:0; margin: 0; margin-left: 15px; }
li           { padding:0; margin: 0; padding-left: 5px; margin-bottom: 8px; text-indent: 0; }
ul li        { margin-left: 15px; list-style-type: disc; }
ol li        { margin-left: 15px; }
a            { padding:0; margin: 0; text-decoration: none; font-size: inherit;}
img          { border: 0; margin: .2em; }
fieldset     { margin: 12px 1em; width: 96%; }
textarea     { width: 96%; height: 60%; min-height: 400px; margin: 12px 1em; padding: 8px; }
h1           { color: #444; font-weight: bold; margin: 36px 10px; padding: 0;}
h2           { margin: 20px 0 10px 0; padding: 0; font-weight: bold; border-bottom: 1px solid #cccccc; line-height: 1.4em; }
a            { color: #003399; }
a:hover      { color: #7A63AA; }
table        {  }
table th     { padding: 6px; border: 1px dotted #ccc; }
table td     { padding: 6px; border: 1px dotted #ccc; }

p, blockquote, ul, ol, dl, li, table, pre
             { margin: 1em 0; font-size: 14px; }
h1 + p, h2 + p, h3 + p, h4 + p, h5 + p, h6 + p
             { margin-top: 0; text-indent:1em; }

code, cite, pre { font-family: Monaco, Verdana, Sans-serif; background-color: #f9f9f9; border: 1px solid #D0D0D0; color: #002166; font-size: 12px; text-indent:0; }
code            { padding: 0 .6em; display: inline; margin:0; }
cite            { font-size: .9em; display: block; margin: 1em; padding: 0; padding-left: 2em; }
blockquote      { font-size: .9em; display: block; margin: 1em; padding: 0; padding-left: 2em; border: none; border-left: 2px solid #ddd; }
pre             { font-size: 12px; display: block; margin: 1em 0; padding: .6em; overflow:auto; max-height:320px; }
pre code        { border: none; text-indent:0; padding: 0; }

#wrapper     { margin: 0 1em; min-height: 100%; padding: 10px; position: relative; }
	</style>
	<script type="text/javascript"><!--//
function fullphpmdcs_popup(url){
	if (!url) url='markdown_cheat_sheet.html?popup';
	if (url.lastIndexOf("popup")==-1) url += (url.lastIndexOf("?")!=-1) ? '&popup' : '?popup';
	var new_f = window.open(url, 'markdown_cheat_sheet', 
       'directories=0,menubar=0,status=0,location=1,scrollbars=1,resizable=1,fullscreen=0,width=840,height=380,left=120,top=120');
	new_f.focus();
	return false; 
}
	//--></script>
</head>
<body>
	<div id="wrapper">
	<h2>MENU</h2>
	<a href="index.php">original Markdown file</a> | 
	<a href="index.php?type=markdown">PHP Markdown 1.0.1o</a> | 
	<a href="index.php?type=markdownextra">PHP Markdown Extra 1.2.5</a> | 
	<a href="index.php?type=multimarkdown">Multi Markdown</a> | 
	<a href="index.php?type=fullphpmarkdown">Full PHP Markdown</a> | 
	<a href="index.php?type=minifullphpmarkdown">Full PHP Markdown minified (<em>if so ...</em>)</a> | 
	<a href="Full_PHP_Markdown/markdown_cheat_sheet.html" onclick="return fullphpmdcs_popup('Full_PHP_Markdown/markdown_cheat_sheet.html');" title="Markdown syntax cheat sheet (new floated window)" target="_blank">
    Markdown syntax cheat sheet</a> 
	 [ <a href="index.php?type=reminders" title="test the inclusion of the cheat sheet in a full page">test inclusion</a> ]
	<hr />
	{$alt_content}
	{$md_content}
	</div>
</body>
</html>
EOT;
	exit;

// Endfile