<?php
// CyberLAB Configuration and Dynamic Base URL Calculation
$project_root = __DIR__;
$script_file = $_SERVER['SCRIPT_FILENAME'];
$relative_script = str_replace($project_root, '', $script_file);
$script_name = $_SERVER['SCRIPT_NAME'];

// Calculate the base directory of the project relative to the web server root
$base_url = substr($script_name, 0, strlen($script_name) - strlen($relative_script));
$base_url = rtrim($base_url, '/\\');
?>
