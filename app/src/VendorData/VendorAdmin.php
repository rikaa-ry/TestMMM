<?php

use SilverStripe\Security\Member;
use SilverStripe\Admin\ModelAdmin;

/**
 * Description
 *
 * @package silverstripe
 * @subpackage mysite
 */
class VendorAdmin extends ModelAdmin
{
    /**
     * Managed data objects for CMS
     * @var array
     */
    private static $managed_models = [
        VendorData::class
    ];

    /**
     * URL Path for CMS
     * @var string
     */
    private static $url_segment = 'vendor';

    /**
     * Menu title for Left and Main CMS
     * @var string
     */
    private static $menu_title = 'Vendor';

    private static $menu_icon_class = 'font-icon-torso';
}
