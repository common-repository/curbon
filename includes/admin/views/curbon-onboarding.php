<?php
/**
 * Loading Onboarding Screens
 * This file is responsible for accomplishing onboarding
 * php version 7.4

 * @category Curbon
 * @package  Curbon
 * @author   Curbon <michael@curbon.io>
 * @license  https://www.gnu.org/licences/gpl-2.0.txt GNU/GPLv
 * @version  GIT: @1.0.0@
 * @link     https://curbon.io/
 */

if (isset($_GET['page']) && $_GET['page'] = "curbon-dashboard") {
    if (isset($_GET['step'])) {
        $active_step = sanitize_text_field($_GET['step']);
    } else {
        $active_step = "1";
    }
}

$active_step_file 
    = CURBON_PLUGIN_PATH . 
        'includes/admin/views/onboarding/onboarding-step-'.$active_step.'.php';
if (file_exists($active_step_file)) {
    include_once $active_step_file;
}
?>