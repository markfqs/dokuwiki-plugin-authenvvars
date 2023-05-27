<?php
/**
 * Default settings for the authenvvars plugin
 *
 * @author Christian Hoffmann <christian@lehrer-hoffmann.de>
 */

$conf['useridvar']   = 'REMOTE_USER';
$conf['usernamevar'] = 'REMOTE_USERNAME';
$conf['emailvar']    = 'REMOTE_EMAIL';
$conf['groupsvar']   = 'REMOTE_GROUPS';
$conf['groupformat'] = 'json';
$conf['groupattr']   = '';
$conf['groupsep']    = ';';

$conf['login_url']   = '';
$conf['logout_url']  = '';
$conf['return_key']  = '';
