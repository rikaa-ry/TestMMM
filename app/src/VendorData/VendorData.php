<?php 

use SilverStripe\Dev\Debug;
use SilverStripe\Forms\ReadonlyField;
use SilverStripe\ORM\DB;
use SilverStripe\ORM\DataObject;
use SilverStripe\Security\Member;

class VendorData extends DataObject {

    private static $db = [
        'Code' => 'Varchar',
        'Name' => 'Varchar',
        'Address' => 'Varchar',
        'PhoneNo' => 'Varchar',
        'City' => 'Varchar'
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
            $this->generateVendorCode();
        }
    }

    public static function getByKode($code) {
        return DataObject::get_one('VendorData', "Code='VND-$code'");
    }

    public function generateVendorCode() {
        $sql = "SELECT COUNT(ID) FROM VendorData";
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

        $vendor_code = "VND-" . $code;
        $this->Code = $vendor_code;
        $this->write();

        return $vendor_code;
    }

    /**
     * Defines summary fields commonly used in table columns
     * as a quick overview of the data for this dataobject
     * @var array
     */
    private static $summary_fields = [
        'Code' => 'Vendor Code',
        'Name' => 'Vendor Name',
        'Address',
        'PhoneNo' => 'Vendor Phone',
        'City'
    ];

    /**
     * DataObject view permissions
     * @param Member $member
     * @return boolean
     */
    public function canView($member = null)
    {
        return true;
    }

    /**
     * DataObject create permissions
     * @param Member $member
     * @return boolean
     */
    public function canCreate($member = null, $context = [])
    {
        return true;
    }

    /**
     * DataObject edit permissions
     * @param Member $member
     * @return boolean
     */
    public function canEdit($member = null)
    {
        return true;
    }

    /**
     * DataObject delete permissions
     * @param Member $member
     * @return boolean
     */
    public function canDelete($member = null)
    {
        return true;
    }

    /**
     * CMS Fields
     * @return FieldList
     */
    public function getCMSFields()
    {
        $fields = parent::getCMSFields();
        
        if(!$this->ID) {
            $fields->removeByName([
                'Code'
            ]);
        } else {
            $fields->addFieldToTab(
                'Root.Main',
                ReadonlyField::create(
                    'Code',
                    'Vendor Code'
                ),
                'Name'
            );
        }

        return $fields;
    }
}
?>