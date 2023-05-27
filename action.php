<?php
/**
 * DokuWiki envvars authentication plugin
 * https://www.dokuwiki.org/plugin:authenvvars
 *
 * This is authenvvars action plugin which
 * a.) skips the 'login' action as it does not make sense with HTTP
 *     authentication.
 *
 * @license GPL 3 http://www.gnu.org/licenses/gpl-3.0.html
 * @author Christian Hoffmann <christian@lehrer-hoffmann.de>
 */

// must be run within Dokuwiki
if(!defined('DOKU_INC')) die();

class action_plugin_authenvvars extends DokuWiki_Action_Plugin {
  
  function register(Doku_Event_Handler $controller){
    global $conf;
    // Don't register if not enabled
    if ($conf['authtype'] != 'authenvvars') return;
    $controller->register_hook('ACTION_ACT_PREPROCESS', 'BEFORE', $this, 'skip_login_action', NULL);
  }
  
  /**
   * Event handler to skip the 'login' action
   */
  function skip_login_action(&$event, $param) {
    /* Some actions handled in inc/actions.php:act_dispatch() result in $ACT
       being modified to 'login', eg. 'register'. */
    if($event->data == 'login') {
        //$event->preventDefault();
        $url = $this->getConf('login_url');
        if (!empty($url)) {
          $return_key = $this->getConf('return_key');
          $query_string = $return_key ? '?'.$return_key.'='.DOKU_URL.wl($ID) : '';
          header("Location: $url{$query_string}");
          exit();
        }
        /* With HTTP authentication, there is no sense in showing a login form,
           so we directly redirect to a 'show' action instead. By using
           act_redirect() instead of modifying $event->data, we make sure
           DokuWiki's entire auth logic can work, which is eg. required so that
           after a user's registration he gets logged in automatically. */
        send_redirect($ID, '', 'show');
    }
  }
}
