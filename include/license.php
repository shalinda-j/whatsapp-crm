<?php
// Deprecated external licensor shim. Delegate to LocalLicense to keep compatibility.
require_once __DIR__ . '/local_license.php';

class WhatsAppCRMBase {
    public static function CheckLicense($purchase_key, &$error = "", &$responseObj = null, $app_version = "", $admin_email = "") {
        return LocalLicense::CheckLicense($purchase_key, $error, $responseObj, $app_version, $admin_email);
    }
    public static function RemoveLicenseKey(&$message = "", $version = "") {
        return LocalLicense::RemoveLicenseKey($message, $version);
    }
    public static function GetRegisterInfo() {
        // Not used in local model; return null.
        return null;
    }
}
