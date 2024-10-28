<?php
//============================================================+
// File name   : example_061.php
// Begin       : 2010-05-24
// Last Update : 2014-01-25
//
// Description : Example 061 for TCPDF class
//               XHTML + CSS
//
// Author: Nicola Asuni
//
// (c) Copyright:
//               Nicola Asuni
//               Tecnick.com LTD
//               www.tecnick.com
//               info@tecnick.com
//============================================================+

/**
 * Creates an example PDF TEST document using TCPDF
 * @package com.tecnick.tcpdf
 * @abstract TCPDF - Example: XHTML + CSS
 * @author Nicola Asuni
 * @since 2010-05-25
 */

$oid = $_GET['oid'];
$ono = $_GET['ono'];

//echo $oid ."==". $ono;
include_once('../../hbyconnections.php');

if ($conn->connect_errno) {
  echo "Failed to connect to MySQL: " . $conn->connect_error;
  exit();
}

$order = "select hby_orders.*,hby_restaurants.restaurant_name,hby_restaurants.contact_address,
	hby_restaurants.contact_mobile,hby_restaurants.contact_email,users.name as username,users.email,users.mobile,users.country_code,hby_order_shipping_address.address  
	from hby_orders 
	left join hby_restaurants on hby_restaurants.user_id=hby_orders.restaurant_user_id 
	left join hby_order_shipping_address on hby_order_shipping_address.user_id=hby_orders.user_id
	left join users on users.id=hby_orders.user_id where hby_orders.id=".$oid." and hby_orders.order_no='".$ono."' ";
$result_db_orders = mysqli_query($conn, $order) or die($conn->error);
$items = ''; $row = [];  $i=0;
if( mysqli_num_rows($result_db_orders) > 0) {
	while ($orderrow = mysqli_fetch_assoc($result_db_orders)) { 
		$row = $orderrow;
	}
	$order_items = "select hby_order_items.*, hby_products.product_name from hby_order_items 
		left join hby_products on hby_products.id = hby_order_items.product_id where order_id=".$oid;
	$result_db_order_items = mysqli_query($conn, $order_items) or die($conn->error);

	if( mysqli_num_rows($result_db_order_items) > 0) {
		while ($row_items = mysqli_fetch_assoc($result_db_order_items)) { 

			$itemaddons = "select GROUP_CONCAT(hby_attribute_varients.variation_name) as variation_name, GROUP_CONCAT(hby_attribute_varients.alias_variation_name) as alias_variation_name, GROUP_CONCAT(hby_order_items_addons.total_price) as total_price_str, sum(hby_order_items_addons.total_price) as total_price, `hby_attributes`.`attribute_name`, `hby_attributes`.`alias_attribute_name` from `hby_order_items_addons` left join `hby_attribute_varients` on `hby_attribute_varients`.`id` = `hby_order_items_addons`.`addon_id` left join `hby_attributes` on `hby_attributes`.`id` = `hby_attribute_varients`.`attribute_id` where `order_id` = ".$oid." and `product_id` = ".$row_items['product_id']." and `order_item_id` = ".$row_items['id']." and `addon_type` = 'VARIENT' group by `attribute_id`";
			$result_itemaddons = mysqli_query($conn, $itemaddons) or die($conn->error);

			$addons = '';	$addonprice = '';
			if( mysqli_num_rows($result_itemaddons) > 0) {
				while ($row_item_addons = mysqli_fetch_assoc($result_itemaddons)) { 
					$addons .= $row_item_addons['attribute_name'] .":". $row_item_addons['variation_name'];
					$addonprice .= $row_item_addons['total_price'];
				}
			}

			$i++;
			$items .= '<tr>
                            <td style="text-align: left;">'.$i.'</td>
                            <td>'.$row_items['product_name'].'<br/>'.$addons.'</td>
                            <td style="text-align: right;">'.$row_items['price'].'</td>
                            <td style="text-align: right;">'.$row_items['qty'].'</td>
                            <td style="text-align: right;">'.$row_items['total_price'].'<br/>'.$addonprice.'</td>
                        </tr>';
			//echo "<pre>"; print_r($row_items);
		}
	}
}//echo "<pre>"; print_r($row);exit;
// Include the main TCPDF library (search for installation path).
require_once('tcpdf_include.php');

// create new PDF document
$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// set document information
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Hurry Bunny');
$pdf->SetTitle($ono);
$pdf->SetSubject('HBR Invoice'.$ono);
$pdf->SetKeywords('HB, PDF, HB, Orderinvoice, Orderinvoice');
/*
// set default header data
$pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE.' 061', PDF_HEADER_STRING);
*/
// set header and footer fonts
$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

// set default monospaced font
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

// set margins
/*$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);*/
$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

// set auto page breaks
$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

// set image scale factor
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

// set some language-dependent strings (optional)
if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
	require_once(dirname(__FILE__).'/lang/eng.php');
	$pdf->setLanguageArray($l);
}

// ---------------------------------------------------------

// set font
$pdf->SetFont('helvetica', '', 10);

// add a page
$pdf->AddPage();

/* NOTE:
 * *********************************************************
 * You can load external XHTML using :
 *
 * $html = file_get_contents('/path/to/your/file.html');
 *
 * External CSS files will be automatically loaded.
 * Sometimes you need to fix the path of the external CSS.
 * *********************************************************
 */

// define some HTML content with style
$html = '
<!-- EXAMPLE OF CSS STYLE -->
<style>

            #invoice{
                padding: 5px;
            }

            .invoice {
                position: relative;
                background-color: #FFF;
                min-height: 680px;
                padding: 15px
            }

            .invoice header {
                padding: 10px 0;
                margin-bottom: 20px;
                border-bottom: 1px solid #3989c6
            }

            .invoice .company-details {
                text-align: right
            }

            .invoice .company-details .name {
                margin-top: 0;
                margin-bottom: 0
            }

            .invoice .contacts {
                margin-bottom: 0px
            }

            .invoice .invoice-to {
                text-align: left
            }

            .invoice .invoice-to .to {
                margin-top: 0;
                margin-bottom: 0
            }

            .invoice .invoice-details {
                text-align: right
            }

            .invoice .invoice-details .invoice-id {
                margin-top: 0;
                color: #3989c6;
                font-size:12px;
            }

            .invoice main {
                padding-bottom: 10px
            }

            .invoice main .thanks {
                margin-top: 10px;
                font-size: 2em;
                margin-bottom: 50px
            }

            .invoice main .notices {
                padding-left: 6px;
                border-left: 6px solid #3989c6
            }

            .invoice main .notices .notice {
                font-size: 1.2em
            }

            .invoice table {
                width: 100%;
                border-collapse: collapse;
                border-spacing: 0;
                margin-bottom: 20px
            }

            .invoice table td,.invoice table th {
                padding: 15px;
                background: #eee;
                border-bottom: 1px solid #fff
            }

            .invoice table th {
                white-space: nowrap;
                font-weight: 400;
                font-size: 16px
            }

            .invoice table td h3 {
                margin: 0;
                font-weight: 400;
                color: #3989c6;
                font-size: 1.2em
            }

            .invoice table .qty,.invoice table .total,.invoice table .unit {
                text-align: right;
                font-size: 1.2em
            }

            .invoice table .no {
                color: #fff;
                font-size: 1.6em;
                background: #3989c6
            }

            .invoice table .unit {
                background: #ddd
            }

            .invoice table .total {
                background: #3989c6;
                color: #fff
            }

            .invoice table tbody tr:last-child td {
                border: none
            }

            .invoice table tfoot td {
                background: 0 0;
                border-bottom: none;
                white-space: nowrap;
                text-align: right;
                padding: 10px 20px;
                font-size: 1.2em;
                border-top: 1px solid #aaa
            }

            .invoice table tfoot tr:first-child td {
                border-top: none
            }

            .invoice table tfoot tr:last-child td {
                color: #3989c6;
                font-size: 1.4em;
                border-top: 1px solid #3989c6
            }

            .invoice table tfoot tr td:first-child {
                border: none
            }

            .invoice footer {
                width: 100%;
                text-align: center;
                color: #777;
                border-top: 1px solid #aaa;
                padding: 8px 0
            }

            @media print {
                .invoice {
                    font-size: 11px!important;
                    overflow: hidden!important
                }

                .invoice footer {
                    position: absolute;
                    bottom: 10px;
                    page-break-after: always
                }

                .invoice>div:last-child {
                    page-break-before: always
                }
            }
        </style>
		<div id="invoice">

            <div class="invoice overflow-auto">
                <div >	
                    <div class=" company-details">
                        <h2 class="name">'.$row['restaurant_name'].'</h2>
                        <div>'.$row['contact_address'].'</div>
                        <div>'.$row['contact_mobile'].'</div>
                        <div>'.$row['contact_email'].'</div>
                    </div>
                        
                    
                    <main>
                        <div class="row contacts">
                            <div class="col invoice-to">
                                <div class="text-gray-light">INVOICE TO:</div>
                                <h2 class="to" style="font-size:20px;">'.$row['mobile'].'</h2>
                                <div class="address">'.$row['address'].'</div>
                                <div class="address">'.$row['email'].'</div>
                            </div>
                            <div class="col invoice-details">
                                <h1 class="invoice-id">Ref No: '.$row['invoice_no'].'</h1>
                                <h1 class="invoice-id">Order No: '.$row['order_no'].'</h1>

                                <div class="invoice-id">Order Date: '.$row['delivery_date'].'</div>
                                <div class="invoice-id">Order Status: '.$row['order_status'].'</div>
                            </div>
                        </div>
                        <table border="0" cellspacing="0" cellpadding="0">

                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th class="text-left">Description</th>
                                    <th  style="text-align: right;">Price </th>
                                    <th style="text-align: right;">Qty</th>
                                    <th style="text-align: right;">Total </th>
                                </tr>
                            </thead>
                            <tbody>'.$items.'</tbody>
                            <tfoot>
                            	<tr><th colspan="6" style="text-align: right;">&nbsp;</th></tr>
                                <tr><th colspan="4" style="text-align: right;">Sub Total: </th><th align="right">'.$row['sub_total'].'</th></tr>
                                <tr><th colspan="4" style="text-align: right;">Discount: </th><th align="right">'.$row['coupon_amount'].'</th></tr>
                                <tr><th colspan="4" style="text-align: right;">Delivery Charges: </th><th align="right">'.$row['shipping_amount'].'</th></tr>
                                <tr><th colspan="4" style="text-align: right;">Net Total: </th><th align="right">'.$row['net_total'].'</th></tr>
                            </tfoot>
                                
                        </table>
      
                    </main>

                    <div class="thanks">Thank you!</div>

                    <footer>
                        Invoice was created on a computer and is valid without the signature and seal.
                    </footer>
                </div>
                <!--DO NOT DELETE THIS div. IT is responsible for showing footer always at the bottom-->
                <div></div>
            </div>
        </div>';

// output the HTML content
$pdf->writeHTML($html, true, false, true, false, '');

// reset pointer to the last page
$pdf->lastPage();

// ---------------------------------------------------------

//Close and output PDF document
$pdf->Output($ono.'.pdf', 'I');

//============================================================+
// END OF FILE
//============================================================+
