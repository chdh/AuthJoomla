<?php

// declare(strict_types=1);
namespace MediaWiki\Extension\AuthJoomla2023;

use MediaWiki\Auth\AbstractPasswordPrimaryAuthenticationProvider;
use MediaWiki\Auth\AuthenticationRequest;
use MediaWiki\Auth\AuthenticationResponse;
use MediaWiki\Auth\AuthManager;
use MediaWiki\Auth\PasswordAuthenticationRequest;
use MediaWiki\Auth\RememberMeAuthenticationRequest;
use MediaWiki\Auth\UserDataAuthenticationRequest;
use MediaWiki\MediaWikiServices;
use MediaWiki\Session\UserInfo;
use StatusValue;
use MWDebug;
use MediaWiki\Extension\AuthJoomla2023\JoomlaInt;

class AuthJoomlaAuthenticationProvider extends AbstractPasswordPrimaryAuthenticationProvider {

   public function __construct ($params = []) {
      parent::__construct($params);
      // MWDebug::log("AuthJoomlaAuthenticationProvider constructor");
      }

   // Automatically create an account when asked to log in a Joomla user that does not exist in MediaWiki.
   public function accountCreationType() {
      // MWDebug::log("AuthJoomlaAuthenticationProvider.accountCreationType()");
      return self::TYPE_CREATE; }

   public function beginPrimaryAccountCreation ($user, $creator, array $reqs) {
      // MWDebug::log("AuthJoomlaAuthenticationProvider.beginPrimaryAccountCreation()");
      return AuthenticationResponse::newFail(); }

   public function beginPrimaryAuthentication (array $reqs) {
      // MWDebug::log("AuthJoomlaAuthenticationProvider.beginPrimaryAuthentication()");
      return AuthenticationResponse::newFail(); }

   public function providerAllowsAuthenticationDataChange (AuthenticationRequest $req, $checkData = true) {
      // MWDebug::log("AuthJoomlaAuthenticationProvider.providerAllowsAuthenticationDataChange()");
      return StatusValue::newGood('ignored'); }

   public function providerAllowsPropertyChange ($property) {
      // MWDebug::log("AuthJoomlaAuthenticationProvider.providerAllowsPropertyChange()");
      return false; }

   public function providerChangeAuthenticationData (AuthenticationRequest $req) {
      // MWDebug::log("AuthJoomlaAuthenticationProvider.providerChangeAuthenticationData()");
      /* ignore */ }

   public function testUserExists ($username, $flags = User::READ_NORMAL) {
      // MWDebug::log("AuthJoomlaAuthenticationProvider.testUserExists()");
      return false; }

   // Parameter $autocreate is named $source in AuthManager.php. It is false when called from canCreateAccount() and set when called from autoCreateUser().
   public function testUserForCreation ($user, $autocreate, array $options = []) {
      // MWDebug::log("AuthJoomlaAuthenticationProvider.testUserForCreation() autocreate=$autocreate, creating={$options['creating']}, username={$user->getName()}");
      if (!$autocreate || !$options['creating']) {         // no auto-creation, only test
         return StatusValue::newGood();; }
      $JoomlaUser = JoomlaInt::getJoomlaUserData();
      if (!$JoomlaUser) {
         throw new \Exception("Passed Joomla user for auto-creation in MediaWiki not found in Joomla database."); }
      // MWDebug::log("User name: {$JoomlaUser['name']}");
      // MWDebug::log("User email: {$JoomlaUser['email']}");
      $user->setRealName($JoomlaUser['name']);
      $user->setEmail($JoomlaUser['email']);
      return StatusValue::newGood(); }

   }
