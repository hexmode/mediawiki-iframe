<?php

$cfg = require __DIR__ . '/../vendor/mediawiki/mediawiki-phan-config/src/config.php';

# Files that would otherwise be ignored:
$cfg['file_list'] = array_merge(
	$cfg['file_list'],
	[
		# Typically top-level .php files
	]
);

$tagLib = "vendor/nichework/tag-builder";

# Paths (either absolute or relative to the top level of this extension) to other extensions
# that this one depends on:
$otherPaths = [];

$mwDir = getenv( "MW_INSTALL_PATH" );
if ( $mwDir === false ) {
	throw new Exception( "Please set MW_INSTALL_PATH!" );
}
$otherPaths[] = $mwDir;

if ( !file_exists( "$mwDir/$tagLib" ) ) {
	$otherPaths[] = $tagLib;
}

$cfg['directory_list'] = array_merge( $cfg['directory_list'], $otherPaths );

$otherPaths[] = "node_modules";
$extDir = "$mwDir/extensions/" . basename( dirname( __DIR__ ) );
if ( file_exists( $extDir ) ) {
	$otherPaths[] = $extDir;
}
$cfg['exclude_analysis_directory_list'] = array_merge(
	$cfg['exclude_analysis_directory_list'], $otherPaths
);

# Put messages you want to suppress here.
#$cfg['suppress_issue_types'][] = null;

return $cfg;
