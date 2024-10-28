<?php
//============================================================+
// File name   : bookinvoice.php
// Begin       : 2009-03-20
// Last Update : 2013-05-14
//
// Description : Example 048 for TCPDF class
//               HTML tables and table headers
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
 * @abstract TCPDF - Example: HTML tables and table headers
 * @author Nicola Asuni
 * @since 2009-03-20
 */

// Include the main TCPDF library (search for installation path).
require_once('tcpdf_include.php');

include_once('../../em_connections.php');

if ($conn->connect_errno) {
  echo "Failed to connect to MySQL: " . $conn->connect_error;
  exit();
}

$booking_id = $_GET['id'];
$booking_code = $_GET['code'];

$settings = "select contact_address from em_admin_settings where id=1";
$result_db_settings = mysqli_query($conn, $settings) or die($conn->error);
$datasettings = [];
if( mysqli_num_rows($result_db_settings) > 0) {
    while ($settingsrow = mysqli_fetch_assoc($result_db_settings)) { 
        $datasettings = $settingsrow;
    }
} 

$contact_address = isset($datasettings['contact_address']) ? $datasettings['contact_address'] : '';

$booking = "select * from em_booking where id=".$booking_id." and ref_no='".$booking_code."' ";
$result_db_booking = mysqli_query($conn, $booking) or die($conn->error);
$data = [];
if( mysqli_num_rows($result_db_booking) > 0) {
    while ($bookingrow = mysqli_fetch_assoc($result_db_booking)) { 
        $data = $bookingrow;

        $booking_items = "select em_booking_subservices.*, em_sub_service.name as sub_service_name, em_sub_cat_services.name as service_name from em_booking_subservices left join em_sub_service on em_sub_service.id=em_booking_subservices.sub_service_id left join em_sub_cat_services on em_sub_cat_services.id=em_booking_subservices.service_id where booking_id=".$booking_id;
        $result_db_booking_items = mysqli_query($conn, $booking_items) or die($conn->error);
         
        if( mysqli_num_rows($result_db_booking_items) > 0) {
            while ($bookingitems = mysqli_fetch_assoc($result_db_booking_items)) { 
                $data['bookingitems'][] = $bookingitems;
            }
        }

        $booking_fees = "select * from em_booking_additional_fees where booking_id=".$booking_id;
        $result_db_booking_fees = mysqli_query($conn, $booking_fees) or die($conn->error);
         
        if( mysqli_num_rows($result_db_booking_fees) > 0) {
            while ($bookingfees = mysqli_fetch_assoc($result_db_booking_fees)) { 
                $data['bookingfees'][] = $bookingfees;
            }
        }


        $user_address  = "select users_address.*, users.mobile from users_address left join users on users.id=users_address.user_id 
             where user_id=".$data['user_id']." and users_address.id=".$data['user_address_id'] ;
        $result_db_user_address = mysqli_query($conn, $user_address) or die($conn->error);
         
        if( mysqli_num_rows($result_db_user_address) > 0) {
            while ($useraddress = mysqli_fetch_assoc($result_db_user_address)) { 
                $data['useraddress'] = $useraddress;
            }
        }

        $slot  = "select * from em_slots where id=".$data['job_slot'];
        $result_db_slot = mysqli_query($conn, $slot) or die($conn->error);
         
        if( mysqli_num_rows($result_db_slot) > 0) {
            while ($slotdetails = mysqli_fetch_assoc($result_db_slot)) { 
                $data['slotdetails'] = $slotdetails;
            }
        }
    }
} 

//echo "<pre>"; print_r($data); exit;
// create new PDF document
$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// set document information
$pdf->SetCreator(PDF_CREATOR); 
$pdf->SetTitle('AMOUR INVOICE '.$booking_code);
$pdf->SetSubject('AMOUR INVOICE '.$booking_code);
$pdf->SetKeywords('AMOUR, PDF, example, test, guide');
 
// set header and footer fonts
$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

// set default monospaced font
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

// set margins
$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
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
$img_url =  $_SERVER['HTTP_HOST'].'/aarofix';  

// set font
$pdf->SetFont('helvetica', 'B', 20);
 // add a page
$pdf->AddPage();
 
$pdf->SetFont('helvetica', '', 8);

$pdf->Image($file='images/logo.png', $x=15, $y=18, $w='30', $h=30, $link='', $align='', $palign='', $border=0, $fitonpage=false);
/*$pdf->Image($img_url.'/public/image/logo.png', 15, 140, 75, 113, 'PNG', $img_url, '', true, 150, '', false, false, 1, false, false, false);
*/// -----------------------------------------------------------------------------


$html = '
    <style>
    #invoice{
                padding: 30px;
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
                margin-bottom: 20px
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
                font-size:16px;
            }

            .invoice main {
                padding-bottom: 50px
            }

            .invoice main .thanks {
                margin-top: -100px;
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
            <table style="width: 90%; border:none; border-collapse: collapse;" id="exportcontentexcel">
                <tbody>
                    <tr>
                        <td style="padding: 20px; "> </td>
                        <td style="padding: 20px; " colspan="3">

                                <h2 class="name">
                                    Hello Amour
                                </h2>
                                <div>'.$contact_address.'</div> 
                        </td> 
                    </tr>
                    <tr>
                        <td style="border-bottom: 1px solid #f69209;" colspan="4"></td>
                    </tr>   
                    <tr>
                        <td colspan="2">
                            <div class="text-gray-light">INVOICE TO:</div>
                            <h4 class="to" style="font-size:20px;">'.$data['useraddress']['user_name'].'</h4>
                            <div class="address">'.$data['useraddress']['address'].'</div>
                            <div class="address">'.$data['useraddress']['city'].','.$data['useraddress']['pin_code'].','.$data['useraddress']['country'].'</div>
                            <div>'.$data['useraddress']['mobile'].'</div> 
                        </td>
                        <td colspan="2" style="text-align:right;"> 
                            <div class="col invoice-details">
                                <h1 class="invoice-id">Ref No: '.$data['ref_no'].'</h1> 

                                <div class="invoice-id">Date: '.$data['job_date'].' </div>
                                <div class="invoice-id">Slot: '.$data['slotdetails']['slot_name'].' </div>
                                
                            </div>
                        </td>
                    </tr>   
                    <tr>
                        <td colspan="4"></td>
                    </tr> 
                    <tr>
                        <td colspan="4"> 
                            <table cellpadding="7px" border-collapse="collapse" class="table table-bordered table-hover" border="1">
                                <tr>
                                    <th>Service</th>
                                    <th>Sub Service</th>
                                    <th>Qty</th>
                                    <th>Price</th>
                                    <th>Amount</th>
                                </tr>'; 
                            $itemtotal = 0;
                            if(isset($data['bookingitems']) &&  count($data['bookingitems'])>0) {
                                foreach($data['bookingitems'] as $k=>$v1){
                                   
                                    $html .= '<tr>
                                        <td>'.$v1['service_name'].'</td>
                                        <td>'.$v1['sub_service_name'].'</td>
                                        <td style="text-align:right;">'.$v1['qty'].'</td>
                                        <td style="text-align:right;">'.$v1['price'].'</td>
                                        <td style="text-align:right;">'.$v1['amount'].'</td>
                                    </tr>';
                                    $itemtotal += $v1['amount'];
                                }
                            }
                                
        $html .=                '<tr>
                                    <th colspan="4" style="text-align: right;"> Sub Total </th>
                                    <th style="text-align:right; font-weight:bold;">'.$data['sub_total'].'</th>
                                </tr>
                                <tr>
                                    <th colspan="4" style="text-align: right;"> Tax</th> 
                                    <th style="text-align:right; font-weight:bold;">'.$data['tax_total'].'</th> 
                                </tr>';

                                $feestotal = 0;
                                if(!empty($data['bookingfees'])) {
        $html .=                '<tr><th colspan="4"> Fees</th> <th></th> </tr>';
                                
                                foreach($data['bookingfees'] as $k=>$v1) {
                                   
        $html .=                '<tr>
                                    <td colspan="4">'.$v1['fees_name'].'</td>
                                    <td style="text-align:right;">'.$v1['fees_value'].'</td>
                                </tr>';
                                $feestotal += $v1['fees_value'];
                                }
        $html .=                '<tr><th colspan="4" style="text-align: right;"> Additional Fees</th> 
                                    <th style="text-align:right; font-weight:bold;">'.$feestotal.'</th> 
                                </tr>';
                                }

        $html .=                '<tr><th colspan="4" style="text-align: right;"> Additional Charge<br/>
                                '.$data['additional_charge_text'].'</th> 
                                <th style="text-align:right; font-weight:bold;">'.$data['additional_charge'].'</th> </tr>';

        $html .=                '<tr><th colspan="4" style="text-align: right;"> Total</th> 
                                    <th style="text-align:right; font-weight:bold;">'.$data['total_amount'].'</th> </tr>';

        $html .=            '</table>
                        </td>
                    </tr>
                    <tr>
                        <td style="border-bottom: 1px solid #f69209;" colspan="4"></td>
                    </tr>   
                    <tr>
                        <td colspan="4"> Thank you</td>
                    </tr>   
                </tbody>
            </table>
        </div>

 ';
//echo $html;  exit;
// output the HTML content
$pdf->writeHTML($html, true, false, true, false, '');

// reset pointer to the last page
$pdf->lastPage();


// -----------------------------------------------------------------------------

//Close and output PDF document
$pdf->Output('Invoice.pdf', 'I');

//============================================================+
// END OF FILE
//============================================================+
