<?php

use SilverStripe\ORM\DB;
use SilverStripe\ORM\DataObject;
use SilverStripe\Security\Member;
use SilverStripe\Forms\ReadonlyField;

class UserData extends Member
{
    private static $db = [
        'Code' => 'Varchar',
        'Status' => 'Varchar'
    ];

    /**
     * Event handler called before writing to the database.
     * 
     * @uses DataExtension->onAfterWrite()
     */
    public function onBeforeWrite()
    {
        parent::onBeforeWrite();
        if(!$this->Code) {
            $this->generateUserCode();
        }

        foreach ($this->DirectGroups() as $data) {
            $this->Status = $data->Title;
        }
    }

    public static function getByKode($code) {
        return DataObject::get_one('UserData', "Code='USR-$code'");
    }

    public function generateUserCode() {
        $sql = "SELECT COUNT(ID) FROM UserData";
        $result = DB::query($sql);
        $number = (int) $result->value();
        if($number == 0){
            $number += 1;
        }
        $code = str_pad($number, 6, "0", STR_PAD_LEFT);
        while (self::getByKode($code)) {
            $number++;
            $code = str_pad($number, 6, "0", STR_PAD_LEFT);
        }

        $user_code = "USR-" . $code;
        $this->Code = $user_code;
        $this->write();

        return $user_code;
    }

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();

        // Readonly field
        $fields->makeFieldReadonly('Code');

        $fields->removeByName([
            'URLSegment',
            // Hide from extend Member
            'Locale',
            'FailedLoginCount',
            'Permissions',
            'LoginSessions'
        ]);

        if(!$this->ID) {
            $fields->removeByName([
                'Status'
            ]);
        } else {
            $fields->removeByName([
                'DirectGroups'
            ]);
            $fields->addFieldToTab(
                'Root.Main',
                ReadonlyField::create(
                    'Status',
                    'Status'
                )
            );
        }

        return $fields;
    }

}
