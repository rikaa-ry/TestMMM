<?php

use SilverStripe\Security\Member;
use SilverStripe\Admin\ModelAdmin;

/**
 * Description
 *
 * @package silverstripe
 * @subpackage mysite
 */
class PurchaseAdmin extends ModelAdmin
{
    /**
     * Managed data objects for CMS
     * @var array
     */
    private static $managed_models = [
        DetailTransData::class
    ];

    /**
     * URL Path for CMS
     * @var string
     */
    private static $url_segment = 'purchase';

    /**
     * Menu title for Left and Main CMS
     * @var string
     */
    private static $menu_title = 'Purchase';

    private static $menu_icon_class = 'font-icon-checklist';
}
