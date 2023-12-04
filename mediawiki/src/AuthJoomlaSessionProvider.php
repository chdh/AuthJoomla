<?php

declare(strict_types=1);
namespace MediaWiki\Extension\AuthJoomla2023;

use MediaWiki\Session\SessionInfo;
use MediaWiki\Session\UserInfo;
use MediaWiki\User\UserRigorOptions;
use MediaWiki\Session\ImmutableSessionProviderWithCookie;
use WebRequest;
use MWDebug;
use MediaWiki\Extension\AuthJoomla2023\JoomlaInt;

class AuthJoomlaSessionProvider extends ImmutableSessionProviderWithCookie {

// public function __construct ($params = []) {
//    parent::__construct($params);
//    MWDebug::log("AuthJoomlaSessionProvider constructor");
//    }

   // Disable "Log out" link.
   public function canChangeUser() {
      // MWDebug::log("AuthJoomlaSessionProvider.canChangeUser()");
      return false; }

   // see https://www.mediawiki.org/wiki/Manual:SessionManager_and_AuthManager/SessionProvider_examples
   public function provideSessionInfo (WebRequest $request) {
      // MWDebug::log("AuthJoomlaSessionProvider.provideSessionInfo()");
      $joomlaUserName = JoomlaInt::getJoomlaUserName($request);
      // MWDebug::log("joomlaUserName: $joomlaUserName");
      if (!$joomlaUserName) {
         return null; }
      $wikiUserName = $this->userNameUtils->getCanonical($joomlaUserName, UserRigorOptions::RIGOR_CREATABLE);
      if (!$wikiUserName) {
         wfDebug("Invalid MediaWiki user name \"$joomlaUserName\".");
         return null; }
      // MWDebug::log("wikiUserName: $wikiUserName");
      $userInfo = UserInfo::newFromName($wikiUserName, true);
      if ($this->sessionCookieName === null) {
         $id = $this->hashToSessionId($wikiUserName);
         $persisted = false;
         $forceUse = true; }
       else {
         $id = $this->getSessionIdFromCookie($request);
         $persisted = $id !== null;
         $forceUse = false; }
      return new SessionInfo(SessionInfo::MAX_PRIORITY, [
         'provider' => $this,
         'id' => $id,
         'userInfo' => $userInfo,
         'persisted' => $persisted,
         'forceUse' => $forceUse ]); }

}
