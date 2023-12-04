<?php

namespace MediaWiki\Extension\AuthJoomla2023;

use MWDebug;

// see https://www.mediawiki.org/wiki/Manual:Hooks
class AuthJoomlaHooks implements \MediaWiki\Preferences\Hook\GetPreferencesHook {

   // Remove "Change Password" button on preferences page.
   public function onGetPreferences ($user, &$preferences) {
      // MWDebug::log("AuthJoomlaHooks.getPreferences()");
      unset($preferences['password']);
      return true; }

   }
