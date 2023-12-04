<?php

declare(strict_types=1);
namespace MediaWiki\Extension\AuthJoomla2023;

use MWDebug;
use WebRequest;
use mysqli;

// Joomla interface for MediaWiki bridge plugin.
class JoomlaInt {

   private static bool       $initDone = false;
   private static string     $joomlaUserName;
   private static mysqli     $joomlaDb;

   public static function init (WebRequest $request) : void {
      if (self::$initDone) {
         return; }
      self::$joomlaUserName = self::decodeJoomlaCookie($request);
      self::$initDone = true; }

   // Returns the Joomla user ID or ''.
   private static function decodeJoomlaCookie (WebRequest $request) : string {
      $joomlaCookieName = 'MW' . md5('Joomla');
      $joomlaCookie = $request->getCookie($joomlaCookieName, '');
      // MWDebug::log("Joomla cookie: \"$joomlaCookie\"");
      if (!$joomlaCookie) {
         return ''; }
      $expl = explode("|", base64_decode($joomlaCookie));
      if (count($expl) != 2) {
         return ''; }
      $key = $GLOBALS['wgAuthJoomla_security_key'];
      if (!$key) {
         throw new \Exception('$wgAuthJoomla_security_key is undefined.'); }
      $userName = trim($expl[0]);
      if (!$userName) {
         return ''; }
      $checksum1 = $expl[1];
      $checksum2 = MD5($userName . $key);
      if ($checksum1 !== $checksum2) {
         wfDebug('Joomla cookie checksum error.');
         return ''; }
      return $userName; }

   // Returns the Joomla user ID or ''.
   public static function getJoomlaUserName (WebRequest $request) : string {
      self::init($request);
      return self::$joomlaUserName; }

   private static function openJoomlaDb() : void {
      if (isset(self::$joomlaDb)) {
         return; }
      $dbHost     = $GLOBALS['wgAuthJoomla_MySQL_Host'];
      $dbUser     = $GLOBALS['wgAuthJoomla_MySQL_Username'];
      $dbPassword = $GLOBALS['wgAuthJoomla_MySQL_Password'];
      $dbName     = $GLOBALS['wgAuthJoomla_MySQL_Database'];
      if (!$dbHost || !$dbUser || !$dbPassword || !$dbName) {
         throw new \Exception('Missing or incomplete Joomla database configuration parameters.'); }
      $mysqli = mysqli_init();
      if (!$mysqli) {
         throw new \Exception("Unable to create MySQL database connection object."); }
      $ok = $mysqli->real_connect($dbHost, $dbUser, $dbPassword, $dbName);
      if (!$ok) {
         throw new \Exception("Unable to connect to Joomla database."); }
      self::$joomlaDb = $mysqli; }

   public static function getJoomlaUserData() : array|null {
      if (!self::$initDone) {
         throw new \Exception("JoomlaInt module not initialized."); }
      self::openJoomlaDb();
      $tableNamePrefix = $GLOBALS['wgAuthJoomla_TablePrefix'];
      $usersTableName = $tableNamePrefix . 'users';
      $joomlaUserNameEsc = self::$joomlaDb->real_escape_string(self::$joomlaUserName);
      $sql = "select * from `$usersTableName` where username = '$joomlaUserNameEsc'";
      $res = self::$joomlaDb->query($sql);
      if (!$res) {
         throw new \Exception("Joomla database user query failed."); }
      $row = $res->fetch_assoc();
      if (!$row) {
         return null; }                                    // Joomla user not found
      return $row; }

   }
