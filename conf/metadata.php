<?php
/**
 * Options for the authenvvars plugin
 *
 * @author Christian Hoffmann <christian@lehrer-hoffmann.de>
 */

$meta['useridvar']   = array('string', '_cautionList' => array('plugin____authhttp____userregex' => 'danger'));
$meta['usernamevar'] = array('string');
$meta['emailvar']    = array('string');
$meta['groupsvar']   = array('string');
$meta['groupformat'] = array('multichoice', '_choices' => array('json', 'csv'));
$meta['groupattr']   = array('string');
$meta['groupsep']    = array('string');

$meta['login_url']   = array('string');
$meta['logout_url']  = array('string');
$meta['return_key']  = array('string');
