<?php

use SilverStripe\Dev\Debug;
use SilverStripe\ORM\DB;
use SilverStripe\ORM\DataObject;
use SilverStripe\Security\Member;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\ORM\ArrayList;

/**
 * Description
 *
 * @package silverstripe
 * @subpackage mysite
 */
class HelperPage extends Page
{
    private static $defaults = array(
        'ShowInMenus' => false
    );

    public function requireDefaultRecords() {
        if (!DataObject::get_one('HelperPage')) {
            $page = new HelperPage();
            $page->Title = 'Helper Page';
            $page->URLSegment = 'helper-page';
            $page->Status = 'Published';
            $page->write();
            $page->publish('Stage', 'Live');
            $page->flushCache();
            DB::alteration_message('HelperPage created on page tree', 'created');
        }
        parent::requireDefaultRecords();
    }
}

/**
 * Description
 *
 * @package silverstripe
 * @subpackage mysite
 */
class HelperPageController extends PageController
{
    /**
     * Defines methods that can be called directly
     * @var array
     */
    private static $allowed_actions = [
        'getcustomer' => true,
        'getitem' => true,
        'salesreport' => true
    ];

    public function getcustomer(HTTPRequest $request) {
        $arr = [];

        $where = "ID > 0";

        $keyword = $request->getVar('q');
        if($keyword){
            $where .= " AND CustomerData.Name LIKE '%$keyword%'";
        }

        $customerdata = CustomerData::get()->where($where);

        foreach ($customerdata as $data) {
            $arr[] = [
                'Code' => $data->Code,
                'Name' => $data->Name
            ];
        }

        return json_encode($arr);
    }

    public function getitem(HTTPRequest $request) {
        $arr = [];

        $where = "ID > 0";

        $keyword = $request->getVar('q');
        if($keyword){
            $where .= " AND CustomerData.Name LIKE '%$keyword%'";
        }

        $itemdata = ItemData::get()->where($where);
        foreach ($itemdata as $data) {
            $arr[] = [
                'Code' => $data->Code,
                'Name' => $data->Name,
                'Price' => $data->Price
            ];
        }

        return json_encode($arr);
    }

    public function salesreport() {
        $id = isset($_REQUEST['HeaderID']) ? $_REQUEST['HeaderID'] : 0;

        $header = HeaderTransData::get()->byID($id);

        if(!$header) {
            return 'Trans Data not found!';
        }

        $headerarr = new ArrayList();
        $detailarr = new ArrayList();
        foreach ($header as $key => $value) {
            foreach ($header->DetailTrans() as $d) {
                $detailarr->push([
                    'HeaderID' => $d->HeaderID,
                    'ItemCode' => $d->ItemCode,
                    'ItemName' => ItemData::get()->filter('Code', $d->ItemCode)->first()->Name,
                    'Qty' => $d->Quantity,
                    'Price' => $d->Price
                ]);
            }

            $headerarr->push([
                'CustomerCode' => $value->CustomerCode,
                'CustomerName' => CustomerData::get()->filter('Code', $value->CustomerCode)->first()->Name,
                'Date' => $value->Date,
                'Total' => $value->Total,
                'Detail' => $detailarr
            ]);
        }

        return $this->customise([
            'Header' => $headerarr
        ])->renderWith(array(
            'Layout\SalesReport'
        ));
    }
}