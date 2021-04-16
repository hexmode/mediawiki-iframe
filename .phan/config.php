<?php

$cfg = require __DIR__ . '/../vendor/mediawiki/mediawiki-phan-config/src/config.php';

# Files that would otherwise be ignored:
$cfg['file_list'] = array_merge(
	$cfg['file_list'],
	[
		# Typically top-level .php files
	]
);

# Relative paths to other extensions that this one depends on:
$otherExtensions = [ 'vendor/nichework/tag-builder' ];

$cfg['directory_list'] = array_merge(
	$cfg['directory_list'], $otherExtensions
);

$cfg['exclude_analysis_directory_list'] = array_merge(
	$cfg['exclude_analysis_directory_list'], $otherExtensions
);

# Put messages you want to suppress here.
#$cfg['suppress_issue_types'][] = null;

return $cfg;
