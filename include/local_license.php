<?php
class LocalLicense {
    /* Minimal local license model that treats any provided key as valid.
       Stores a lightweight response object similar to the previous model. */

    public static function CheckLicense($license_key, &$message = "", &$responseObj = null, $app_version = "", $admin_email = "") {
        $license_key = trim((string)$license_key);
        if ($license_key === "") {
            $message = "Empty license key";
            return false;
        }
        $obj = new stdClass();
        $obj->is_valid = true;
        $obj->expire_date = 'no expiry';
        $obj->support_end = 'no limit';
        $obj->license_title = 'Local License';
        $obj->license_key = $license_key;
        $responseObj = $obj;
        $message = 'License activated locally';
        return true;
    }

    public static function RemoveLicenseKey(&$message = "", $version = "") {
        // Nothing to do remotely; caller should clear DB state.
        $message = 'Local license deactivated';
        return true;
    }
}
