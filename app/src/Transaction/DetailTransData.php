<?php

use SilverStripe\Forms\DropdownField;
use SilverStripe\ORM\DataObject;
use SilverStripe\Forms\ReadonlyField;
use SilverStripe\View\Requirements;
use SilverStripe\Forms\LiteralField;

class DetailTransData extends DataObject{  
  private static $db = [
    'Quantity' => 'Int',
    'ItemCode' => 'Varchar',
    'Price' => 'Double'
  ];
  
  private static $has_one = [
    'Header' => HeaderTransData::class
  ];

  private static $summary_fields = [
    'HeaderID' => 'Header ID',
    'ItemCode' => 'Item Code',
    'getItemName' => 'Item Name',
    'getItemPrice' => 'Item Price',
    'Quantity' => 'QTY',
    'getSubtotal' => 'Subtotal'
  ];

  public function getItemName() {
    $itemdata = ItemData::get()->filter([
      'Code' => $this->ItemCode
    ])->first();

    if($itemdata) {
      return $itemdata->Name;
    }

    return '-';
  }

  public function getItemPrice() {
    $itemdata = ItemData::get()->filter([
      'Code' => $this->ItemCode
    ])->first();

    if($itemdata) {
      return 'Rp ' . $itemdata->Price;
    }

    return '-';
  }

  public function getSubtotal() {
    return 'Rp ' . $this->Price;
  }

  public function onBeforeWrite()
    {
        parent::onBeforeWrite();

        $itmcode = isset($_REQUEST['itemcode']) ? $_REQUEST['itemcode'] : '';
        $this->ItemCode = $itmcode;
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
      'HeaderID'
    ]);

    $itemdata = ItemData::get()->filter([
      'Code' => $this->ItemCode
    ])->first();

    $itemname = '';
    $itemprice = '';
    if($itemdata) {
      $itemname = $itemdata->Name;
      $itemprice = $itemdata->Price;
    }

    if(!$this->ID) {
      $fields->removeByName([
          'ItemCode',
      ]);
      $fields->addFieldToTab(
          'Root.Main',
          LiteralField::create(
              "Customer", 
              '<div class="row">
                  <div class="col-md-6">
                      <label for="itemcode" class="form-label">Item Code <span class="text-danger">*</span></label>
                      <select class="form-control select2" id="item-code" name="itemcode" required></select>
                  </div>
              </div>
              <hr>
              <div class="row">
                  <div class="col-md-6">
                      <label for="itemname" class="form-label">Item Name</label>
                      <input type="text" class="form-control" id="itemname" value="-" disabled>
                  </div>
              </div>
              <hr>
              <div class="row">
                  <div class="col-md-6">
                      <label for="itemprice" class="form-label">Item Price</label>
                      <input type="text" class="form-control" id="itemprice" value="-" disabled>
                  </div>
              </div>
              <hr>'
          ),
          'Quantity'
      );
  } else {
    $fields->addFieldToTab(
      'Root.Main',
      DropdownField::create(
        'ItemCode',
        'Item Code',
        ItemData::get()->map('Code', 'Code')
      )->setEmptyString('-- Select an Item --'),
      'Quantity'
    );
  }

    

    if($this->ID) {
      $fields->insertAfter(ReadonlyField::create(
          'ItemName',
          'Item Name',
          $itemname
      ), 'ItemCode');

      $fields->insertBefore(ReadonlyField::create(
        'ItemPrice',
        'Item Price',
        $itemprice
    ), 'Quantity');
    }

    return $fields;
  }
}
?>