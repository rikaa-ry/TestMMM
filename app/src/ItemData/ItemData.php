<?php 

use SilverStripe\ORM\DB;
use SilverStripe\ORM\DataObject;
use SilverStripe\Forms\ReadonlyField;
use SilverStripe\Forms\RequiredFields;

class ItemData extends DataObject {

    private static $db = [
        'Code' => 'Varchar',
        'Name' => 'Varchar',
        'Price' => 'Double'
    ];

    /**
     * Defines summary fields commonly used in table columns
     * as a quick overview of the data for this dataobject
     * @var array
     */
    private static $summary_fields = [
        'Code',
        'Name',
        'getItemPrice' => 'Price'
    ];

    public function getItemPrice() {
        return 'Rp ' . $this->Price;
    }

    /**
     * Event handler called before writing to the database.
     * 
     * @uses DataExtension->onAfterWrite()
     */
    public function onBeforeWrite()
    {
        parent::onBeforeWrite();
        if(!$this->Code) {
            $this->generateItemCode();
        }
        
    }

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

    public static function getByKode($code) {
        return DataObject::get_one('ItemData', "Code='ITM-$code'");
    }

    public function generateItemCode() {
        $sql = "SELECT COUNT(ID) FROM ItemData";
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

        $item_code = "ITM-" . $code;
        $this->Code = $item_code;
        $this->write();

        return $item_code;
    }

    public function getCMSValidator()
    {
        return new RequiredFields([
            'Name', 'Price'
        ]);
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
                    'Item Code'
                ),
                'Name'
            );
        }

        return $fields;
    }
}
?>