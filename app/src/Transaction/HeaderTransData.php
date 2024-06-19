<?php 

use SilverStripe\Dev\Debug;
use SilverStripe\Forms\DropdownField;
use SilverStripe\ORM\DB;
use SilverStripe\ORM\DataObject;
use SilverStripe\Forms\RequiredFields;
use SilverStripe\Forms\ReadonlyField;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\LiteralField;
use SilverStripe\View\Requirements;
use SilverStripe\Forms\GridField\GridFieldConfig_RecordEditor;

class HeaderTransData extends DataObject {

    private static $db = [
        'CustomerCode' => 'Varchar',
        'InvoiceNumber' => 'Varchar',
        'Date' => 'Date',
        'Total' => 'Double'
    ];

    private static $has_many = [
        'DetailTrans' => DetailTransData::class
    ];

    /**
     * Defines summary fields commonly used in table columns
     * as a quick overview of the data for this dataobject
     * @var array
     */
    private static $summary_fields = [
        'InvoiceNumber' => 'Invoice Number',
        'CustomerCode' => 'Customer Code',
        'Date',
        'getTotalPrice' => 'Total'
    ];

    public function getTotalPrice() {
        return 'Rp ' . $this->Total;
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

    /**
     * Event handler called before writing to the database.
     * 
     * @uses DataExtension->onAfterWrite()
     */
    public function onBeforeWrite()
    {
        parent::onBeforeWrite();
        if (!$this->InvoiceNumber) {
            $this->generateInvoiceNumber();
        }

        $cstcode = isset($_REQUEST['customercode']) ? $_REQUEST['customercode'] : '';
        $this->CustomerCode = $cstcode;

        if(!$this->Date) {
            $this->Date = date('Y-m-d');
        }
    }

    public static function getByKode($code) {
        return DataObject::get_one('HeaderTransData', "InvoiceNumber='INV-$code'");
    }

    public function getCMSValidator()
    {
        return new RequiredFields([
            'Total'
        ]);
    }

    public function generateInvoiceNumber() {
        $sql = "SELECT COUNT(ID) FROM HeaderTransData";
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

        $inv_number = "INV-" . $code;
        $this->InvoiceNumber = $inv_number;
        $this->write();

        return $inv_number;
    }

    /**
     * CMS Fields
     * @return FieldList
     */
    public function getCMSFields()
    {
        Requirements::css('https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css');
        Requirements::css('https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css');
        Requirements::css('https://cdnjs.cloudflare.com/ajax/libs/select2-bootstrap-theme/0.1.0-beta.10/select2-bootstrap.min.css');
        Requirements::javascript('https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js');
        Requirements::javascript('themes\simple\javascript\orderscript.js');

        $fields = parent::getCMSFields();
        $fields->removeByName([
            'DetailTrans',
        ]);

        $custdata = CustomerData::get()->filter([
            'Code' => $this->CustomerCode
        ])->first();

        $custname = '';
        if($custdata) {
            $custname = $custdata->Name;
        }
        
        if(!$this->ID) {
            $fields->removeByName([
                'CustomerCode',
            ]);
            $fields->addFieldToTab(
                'Root.Main',
                LiteralField::create(
                    "Customer", 
                    '<div class="row">
                        <div class="col-md-6">
                            <label for="customercode" class="form-label">Customer Code <span class="text-danger">*</span></label>
                            <select class="form-control select2" id="customer-code" name="customercode" required></select>
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-md-6">
                            <label for="customername" class="form-label">Customer Name</label>
                            <input type="text" class="form-control" id="customername" value="-" disabled>
                        </div>
                    </div>
                    <hr>'
                ),
                'Date'
            );
        }

        // $fields->addFieldToTab(
        //     'Root.Main',
        //     DropdownField::create(
        //         'CustomerCode',
        //         'Customer Code',
        //         CustomerData::get()->map('Code', 'Code')
        //     )->setEmptyString('-- Select Customer --'),
        //     'Date'
        // );

        if(!$this->ID) {
            $fields->removeByName([
                'InvoiceNumber'
            ]);
        } else {
            $fields->addFieldToTab(
                'Root.Main',
                ReadonlyField::create(
                    'InvoiceNumber',
                    'Invoice Number'
                ),
                'CustomerCode'
            );

            $fields->insertBefore(ReadonlyField::create(
                'CustomerName',
                'Customer Name',
                $custname
            ), 'Date');
        }

        if($this->ID) {
            $config_sec = GridFieldConfig_RecordEditor::create();
            $fields->addFieldsToTab(
                'Root.Detail',
                [
                    GridField::create(
                        'DetailTrans',
                        'Detail Transaction Data',
                        $this->DetailTrans(),
                        $config_sec
                    )
                ]
            );

            $url = 'helper-page/salesreport?HeaderID=' . $this->ID;
            $fields->addFieldToTab(
                'Root.Sales Report',
                LiteralField::create("ReportSales", 
                "<div style='width:100%; height:700px'>
                    <iframe style='width:100%; height:700px;overflow:auto;border:none' src='$url'></iframe>
                </div>")
            );

            $statistikField = LiteralField::create("StatistikInfo", 
            "<div style='width:100%; height:700px'>
                <iframe style='width:100%; height:700px;overflow:auto;border:none' src='$url'></iframe>
            </div>");


        }

        return $fields;
    }
}
?>