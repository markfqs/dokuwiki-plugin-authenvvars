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
    
    /* Logout if we have a URL to do so */
    $this->cando['logout'] = empty($this->getConf('logout_url')) ? false : true;
    /* This plugin uses it's own authentication. */
    $this->cando['external'] = true;

    $this->success = false;
    
    /* Load the config */
    $this->loadConfig();
    
    try {
      if( empty($this->getConf('useridvar')) ) {
        throw new Exception( "useridvar not configured" );
      }
      $this->userinfo = array();
      $this->userinfo['userid'] = $_SERVER[$this->getConf('useridvar')];
      $this->userinfo['name']   = $_SERVER[$this->getConf('usernamevar')];
      $this->userinfo['mail']   = $_SERVER[$this->getConf('emailvar')];
      $this->userinfo['grps']   = $this->createGrouparray();
    }
    catch( Exception $e ) {
      msg( $e->getMessage() );
      return;
    }
    
    $this->success = true;
  }

  public function trustExternal($user, $pass, $sticky=false) {
    global $USERINFO;
    $sticky ? $sticky = true : $sticky = false; //sanity check

    /* $user ignored */
    $myuser = $this->userinfo['userid'];
    if( empty($myuser)) {
      return false;
    }
    
    $USERINFO['name'] = $this->userinfo['name'];
    $USERINFO['mail'] = $this->userinfo['mail'];
    $USERINFO['grps'] = $this->userinfo['grps'];
    
    $_SERVER['REMOTE_USER']                = $myuser;
    $_SESSION[DOKU_COOKIE]['auth']['user'] = $myuser;
    $_SESSION[DOKU_COOKIE]['auth']['info'] = $USERINFO;

    return true;
  }

  /**
   * Log off the current user [ OPTIONAL ]
   *
   * Is run in addition to the ususal logoff method. Should
   * only be needed when trustExternal is implemented.
   *
   * @see     auth_logoff()
   * @author  Andreas Gohr <andi@splitbrain.org>
  */
  public function logOff()
  {
    // redirect to logoff
    $url = $this->getConf('logout_url');
    if (!empty($url)) {
      $return_key = $this->getConf('return_key');
      $query_string = $return_key ? '?'.$return_key.'='.DOKU_URL.wl($ID) : '';
      header("Location: $url{$query_string}");
      exit();
    }
  }


  private function createGrouparray() {
    $groupformat = $this->getConf('groupformat');

    if( $groupformat == 'csv' ) {
      return $this->createGrouparrayCsv();
    }
    return $this->createGrouparrayJson();
  }
  
  private function createGrouparrayCsv() {
    $groupsvar = $this->getConf('groupsvar');
    $groupsep = $this->getConf('groupsep');
    $grouparr = array();
    $grouparr[] = 'user';
    if( empty($groupsvar) ) {
      return $grouparr;
    }
    
    foreach( explode( $groupsep, $_SERVER[$groupsvar]) as $value ) {
      $grouparr[]=$value;
    }
    /* error_log( $this->userinfo['name'].': '.json_encode($grouparr) ); */
    return $grouparr;
  }
  
  private function createGrouparrayJson() {
    $groupsvar = $this->getConf('groupsvar');
    $groupattr = $this->getConf('groupattr');
    $grouparr = array();
    $grouparr[] = 'user';
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
    /* error_log( $this->userinfo['name'].': '.json_encode($grouparr) ); */
    return $grouparr;
  }
}
