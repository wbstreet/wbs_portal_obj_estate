<?php
/**
 *
 * @category        module
 * @package         wbs_portal_obj_estate
 * @author          Konstantin Polyakov
 * @license         http://www.gnu.org/licenses/gpl.html
 * @platform        WebsiteBaker 2.10.0
 * @requirements    PHP 5.2.2 and higher
 *
 */

if(!defined('WB_PATH')) {
        require_once(dirname(dirname(__FILE__)).'/framework/globalExceptionHandler.php');
        throw new IllegalFileException();
}

include(__DIR__.'/lib.class.portal_obj_estate.php');
$clsModPortalObjEstate = new ModPortalObjEstate(null, null);
$r = $clsModPortalObjEstate->uninstall();
if ($r !== true) {
    $admin->print_error($r);
}

?>