<?php

/*
MIT License

Copyright (c) 2023 Christian d'Heureuse, chdh@inventec.ch

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.
*/

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
