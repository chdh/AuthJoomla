<?php

defined('_JEXEC') or die;

use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\User\User;
use Joomla\CMS\User\UserHelper;

class PlgUserMediawikiLogin extends CMSPlugin {

   protected $app;

   function onUserLogin (/*JAuthenticationResponse*/ $userAuthResponse, $options) {

      if (!$this->app->isClient('site')) {
         return true; }

      $userId = (int)UserHelper::getUserId($userAuthResponse['username']);
      if (!$userId) {
         return true; }
      $user = User::getInstance();
      if (!$user->load($userId)) {
         return true; }
      $username = $user->username;

      $mediawikiDomain = $this->params->get('mediawiki_cdomain');
      $cookieName = 'MW'.md5('Joomla');
      $key = $this->params->get('mediawiki_salt');
      $checksum = MD5($username . $key);
      $cookie = base64_encode("$username|$checksum");
      $time = mktime(0, 0, 0, 12, 31, date('Y') + 10);
      setcookie($cookieName, $cookie, $time, '/', $mediawikiDomain);

      return true; }

   function onUserLogout ($credentials) {

      if (!$this->app->isClient('site')) {
         return true; }

      $mediawikiDomain = $this->params->get('mediawiki_cdomain');
      $cookieName = 'MW'.md5('Joomla');
      setcookie($cookieName, '', 1, '/', $mediawikiDomain);  // delete the cookie

      return true; }

   }
