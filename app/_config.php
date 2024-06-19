<?php

use SilverStripe\Security\PasswordValidator;
use SilverStripe\Security\Member;
use SilverStripe\Admin\CMSMenu;
use SilverStripe\CampaignAdmin\CampaignAdmin;
use SilverStripe\AssetAdmin\Controller\AssetAdmin;
use SilverStripe\Reports\ReportAdmin;
use SilverStripe\VersionedAdmin\ArchiveAdmin;

// remove PasswordValidator for SilverStripe 5.0
$validator = PasswordValidator::create();
// Settings are registered via Injector configuration - see passwords.yml in framework
Member::set_password_validator($validator);

// Hide CMS Menu
CMSMenu::remove_menu_class(CampaignAdmin::class);
CMSMenu::remove_menu_class(AssetAdmin::class);
CMSMenu::remove_menu_class(ReportAdmin::class);
CMSMenu::remove_menu_class(ArchiveAdmin::class);

// Set timezone
date_default_timezone_set("Asia/Jakarta");