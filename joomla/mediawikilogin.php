<?php
/**
 * File: mediawikilogin.php
 * Op basis van: docuwiki bridge
 * Version: 2.4
 * Author docuwiki bridge: Tim Hewitt
 * Aangepast door: Harold Prins
 * Email: info@haroldprins.nl
 * Website: http://www.haroldprins.nl
 * Plugin for automatic Mediawiki login with Joomla.
 * Requires the Joomla authorization module for Mediawiki
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );


jimport('joomla.plugin.plugin');

class plgUserMediawikiLogin extends JPlugin {

    // function plgUserMediawikiLogin(& $subject, $config)
    function __construct(& $subject, $config)
    {
        parent::__construct($subject, $config);
    }


    function onUserBeforeSave($user, $isnew, $new)  {
        $execution = $this->params->get('mediawiki_username', false);
        if($execution  && $new) {
            $regex = $this->params->get('mediawiki_usernameregex', '/^[a-zA-Z0-9- ]+$/');
            $message = JText::_($this->params->get('mediawiki_usernameerrormessage', ''));
            if ( preg_match ($regex, $new['username'] )) {
                return true;
            } else {
                throw new \Exception($message);
                return false;
            }
        }
        return true;
    }

    function onUserLogin($user, $options = array())
    {

        jimport('joomla.user.helper');

        $instance = $this->_getUser($user, $options);

        // If _getUser returned an error, then pass it back.
        if (JError::isError($instance)) {
            return false;
        }

        // If the user is blocked, redirect with an error
        if ($instance->get('block') == 1) {
            JError::raiseWarning('SOME_ERROR_CODE', JText::_('JERROR_NOLOGIN_BLOCKED'));
            return false;
        }

        $success = true;

        // If we are logged in to administrator back end, just exit
        $app = JFactory::getApplication();
        if ($app->isAdmin()) {
            return ($success);
        }

        // actual Mediawiki directory for the cookie
        $mediawiki_rel = $this->params->get('mediawiki_cpath');
        $mediawiki_dom = $this->params->get('mediawiki_cdomain');

        // Create the login cookie
        $mediawiki_cookie = 'MW'.md5('Joomla');

        $username = $user['username'];
        $userid = intval(JUserHelper::getUserId($username));
        $loggeduser = JFactory::getUser($userid); //Bug using different caps username fixed line 68 and 69, thanks to Duran
        $username = $loggeduser->username;

        $salt = $this->params->get('mediawiki_salt');
        $username_match = MD5($username . $salt);

        $cookie = base64_encode("$username|$username_match");
        $mainframe = JFactory::getApplication();
        // $time = time() + (60 * $mainframe->getCfg('lifetime')); //in te stellen via configuration.php
        // patch chdh 2013-10-31:
        $time = mktime(0, 0, 0, 12, 31, 2029);

        $success = setcookie($mediawiki_cookie,$cookie,$time,$mediawiki_rel,$mediawiki_dom);

        return $success;
    }

    function onUserLogout($user, $options = array())
    {

        $success = true;

        // If we are logged in to administrator back end, just exit
        $app = JFactory::getApplication();
        if ($app->isAdmin()) {
            return ($success);
        }

        // Delete the login cookie
        $mediawiki_cookie = 'MW'.md5('Joomla');
            $time = time() - 3600; //one hour ago


            // delete the user's identifying cookies
            $mediawiki_cprefix = $this->params->get('mediawiki_cprefix');
            $mediawiki_cpath = $this->params->get('mediawiki_cpath');
            $mediawiki_cdomain = $this->params->get('mediawiki_cdomain');

            setcookie( $mediawiki_cookie,0,$time,$mediawiki_cpath, $mediawiki_cdomain);
        setcookie( $mediawiki_cprefix . '_session', '', $time, $mediawiki_cpath, $mediawiki_cdomain);
        setcookie( $mediawiki_cprefix . 'UserName', '', $time, $mediawiki_cpath, $mediawiki_cdomain);
        setcookie( $mediawiki_cprefix . 'UserID',   '', $time, $mediawiki_cpath, $mediawiki_cdomain);
        setcookie( $mediawiki_cprefix . 'Token',    '', $time, $mediawiki_cpath, $mediawiki_cdomain);

        // Remember when the user logged out, to prevent seeing cached pages
        $ts_now = gmdate( 'YmdHis', time() ); // emulates wfTimestampNow()
        setcookie( $mediawiki_cprefix . 'LoggedOut', $ts_now, time() + 86400, $mediawiki_cpath, $mediawiki_cdomain);

        return $success;
    }

    /**
     * This method will return a user object
     *
     * If options['autoregister'] is true, if the user doesn't exist yet he will be created
     *
     * @param   array   $user       Holds the user data.
     * @param   array   $options    Array holding options (remember, autoregister, group).
     *
     * @return  object  A JUser object
     * @since   1.5
     */
    protected function &_getUser($user, $options = array())
    {
        $instance = JUser::getInstance();
        if ($id = intval(JUserHelper::getUserId($user['username'])))  {
            $instance->load($id);
            return $instance;
        }

        //TODO : move this out of the plugin
        jimport('joomla.application.component.helper');
        $config = JComponentHelper::getParams('com_users');
        // Default to Registered.
        $defaultUserGroup = $config->get('new_usertype', 2);

        $acl = JFactory::getACL();

        $instance->set('id'         , 0);
        $instance->set('name'           , $user['fullname']);
        $instance->set('username'       , $user['username']);
        $instance->set('password_clear' , $user['password_clear']);
        $instance->set('email'          , $user['email']);  // Result should contain an email (check)
        $instance->set('usertype'       , 'deprecated');
        $instance->set('groups'     , array($defaultUserGroup));

        //If autoregister is set let's register the user
        $autoregister = isset($options['autoregister']) ? $options['autoregister'] :  $this->params->get('autoregister', 1);

        if ($autoregister) {
            if (!$instance->save()) {
                return JError::raiseWarning('SOME_ERROR_CODE', $instance->getError());
            }
        }
        else {
            // No existing user and autoregister off, this is a temporary user.
            $instance->set('tmp_user', true);
        }

        return $instance;
    }

}
