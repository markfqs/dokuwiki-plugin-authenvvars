<?php
/**
 * DokuWiki envvars plugin
 * https://www.dokuwiki.org/plugin:authenvvars
 *
 * @license GPL 3 http://www.gnu.org/licenses/gpl-3.0.html
 * @author Christian Hoffmann <christian@lehrer-hoffmann.de>
 */

// must be run within Dokuwiki
if(!defined('DOKU_INC')) die();

class auth_plugin_authenvvars extends DokuWiki_Auth_Plugin {

  /**
   * Constructor.
   */
  public function __construct() {
    # global $conf;
    
    parent::__construct();
    
    /* No support for logout in this auth plugin. */
    $this->cando['logout'] = false;
    /* This plugins uses it's own authentication. */
    $this->cando['external'] = true;
    
    $this->success = false;
    
    /* Load the config */
    $this->loadConfig();
    
    try {
      $this->userinfo = array();
      $this->userinfo['userid'] = $_SERVER[$this->getConf('useridvar')];
      if( empty($this->userinfo['userid']) ) {
        throw new Exception( "userid empty. Please give correct envvar in useridvar." );        
      }
      $this->userinfo['name']   = $_SERVER[$this->getConf('usernamevar')];
      $this->userinfo['mail']   = $_SERVER[$this->getConf('emailvar')];
      $this->userinfo['grps']   = $this->createGrouparray($this->getConf('groupsvar'),$this->getConf('groupattr'));
    }
    catch( Exception $e ) {
      msg( $e->getMessage() );
      return;
    }
    
    $this->success = true;
  }

  public function trustExternal($user, $pass, $sticky=false) {
    global $USERINFO;
    
    /* $user ignored */
    $myuser = $this->userinfo['userid'];
    
    $USERINFO['name'] = $this->userinfo['name'];
    $USERINFO['mail'] = $this->userinfo['mail'];
    $USERINFO['grps'] = $this->userinfo['grps'];
    
    $_SERVER['REMOTE_USER']                = $myuser;
    $_SESSION[DOKU_COOKIE]['auth']['user'] = $myuser;
    $_SESSION[DOKU_COOKIE]['auth']['info'] = $USERINFO;
    
    return true;
  }

  private function createGrouparray( $groupsvar, $groupattr ) {
    $grouparr = array();
    if( empty($groupsvar) ) {
      return $grouparr;
    }
    
    foreach( json_decode($_SERVER[$groupsvar],true) as $key=>$value ) {
      if( is_array( $value) ) {
        if( ! array_key_exists( $groupattr, $value ) ) {
          throw new Exception( $groupattr." is not an attribute." );
        }
        $grpvalue = $value[$groupattr];
      }
      else {
        $grpvalue = $value;
      }
      
      if( ! is_string( $grpvalue) ) {
        throw new Exception( "Groupvalue is not a string." );
      }
      $grouparr[] = $grpvalue;
    }
    return $grouparr;
  }
}
