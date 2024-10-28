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

$iid = $_GET['iid'];
$ino = $_GET['ino'];

//echo $iid ."==". $ino; exit;
include_once('../../hbyconnections.php');

if ($conn->connect_errno) {
  echo "Failed to connect to MySQL: " . $conn->connect_error;
  exit();
}

$invoice = "select * from hby_restaurant_invoice where id=".$iid." and invoice_number='".$ino."' ";
$result_db_invoice = mysqli_query($conn, $invoice) or die($conn->error);

$items = ''; $data = [];  $i=0;
if( mysqli_num_rows($result_db_invoice) > 0) {
	while ($invoicerow = mysqli_fetch_assoc($result_db_invoice)) { 
		$data = $invoicerow;
        $sel_restaurant = "select hby_restaurants.*, users.name as company_name from hby_restaurants left join users on users.id = hby_restaurants.user_id
                     where restaurant_status = 'ACTIVE' and  hby_restaurants.user_id = ". $data['restaurant_user_id'];

        $result_db_rest = mysqli_query($conn, $sel_restaurant) or die($conn->error);
        if( mysqli_num_rows($result_db_rest) > 0) {
            while ($storerow = mysqli_fetch_assoc($result_db_rest)) { 
                $sel_restaurant = $storerow;
            }
        }
	} 
} //echo "<pre>"; print_r($sel_restaurant);exit;
// Include the main TCPDF library (search for installation path).
require_once('tcpdf_include.php');

// create new PDF document
$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// set document information
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Hurry Bunny');
$pdf->SetTitle($ino);
$pdf->SetSubject('HBR Restaurant Invoice'.$ino);
$pdf->SetKeywords('HB, PDF, HB, Restaurantinvoice, Restaurantinvoice');

$img_url =  $_SERVER['HTTP_HOST'].'/hurrybunny'.'/public/image/logo.jpg';

$dir = __dir__."/../../public/uploads/invoices/";

//echo PDF_HEADER_LOGO; exit;
// set default header data
//$pdf->SetHeaderData('logo.jpg', PDF_HEADER_LOGO_WIDTH, $ino, 'Restaurantinvoice');

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
$pdf->SetFont('aealarabiya', '', 10);
$pdf->Image($img_url.'/public/image/logo.webp', 15, 140, 75, 113, 'JPG', $img_url, '', true, 150, '', false, false, 1, false, false, false);

// add a page
$pdf->AddPage();

//$pdf->ImageSVG($file='images/tux.svg', $x=30, $y=100, $w='', $h=100, $link='', $align='', $palign='', $border=0, $fitonpage=false);

//$pdf->Image($file='images/hurry_user_icon.png', $x=15, $y=12, $w='30', $h=35, $link='', $align='', $palign='', $border=0, $fitonpage=false);

$pdf->Image($file='images/hb.png', $x=15, $y=10, $w='30', $h=35, $link='', $align='', $palign='', $border=0, $fitonpage=false);


$img_url =  $_SERVER['HTTP_HOST'].'/hurrybunny';  

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
                margin-bottom: 20px; 
            }

            .invoice table td,.invoice table th {
                padding: 15px;
                background: #eee;
                border-bottom: 1px solid #ccc
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
                    <tr style=" /* border-bottom: 4px double #f69209 !important; */ ">
                        <td style="padding: 20px; "> </td>
                        <td style="padding: 20px; " colspan="3">
                            <div style=" background-color: #ffffff; padding: 2%; font-size: large; text-align:right;">
                                مؤسسة الأرنب السريع للتجارة  <br/>
                                ش:  برج الاداد حي المنتزه الدمام  <br/>
                                س ت: 2051213799   <br/>
                                VAT 300376131700003  <br/>
                            </div>
                        </td> 
                    </tr>
                    <tr>
                        <td style="border-bottom: 1px solid #f69209;" colspan="4"></td>
                    </tr>
                    <tr style=" border-bottom:none; ">
                        <td  style="padding: 20px; border:none; border-collapse: collapse; width: 30%">INVOICE NUMBER :</td>
                        <td style="text-align:right;" colspan="2">'.$ino.' </td> 
                        <td style="padding: 20px;  border:none; border-collapse: collapse; text-align:right;">رقم الفاتورة </td>
                    </tr>
                    <tr style=" border-bottom:none; ">
                        <td  style="padding: 20px; border:none; border-collapse: collapse; width: 30%">DATE : </td>
                        <td  style="text-align:right;" colspan="2"> <input type="hidden" name="date" id="date" value="">'.date('Y-m-d H:i:s').' </td> 
                        <td style="padding: 20px;  border:none; border-collapse: collapse; text-align:right;">التاريخ</td>
                    </tr>
                    <tr style=" border-bottom:none; ">
                        <td style="padding: 20px; border:none; border-collapse: collapse;">CUSTOMER ID :</td>
                        <td  style="text-align:right;" colspan="2"> <input type="hidden" name="store_code" id="store_code" value="">'.$sel_restaurant['code'].'</td>
                        <td style="padding: 20px;  border:none; border-collapse: collapse; text-align:right;">كو دالعميل</td>
                    </tr>
                    <tr style="    border-bottom:none; ">
                        <td style="padding: 20px; border:none; border-collapse: collapse;">COMPANY NAME :</td>
                        <td style="text-align:right;" colspan="2">
                        <input type="hidden" name="company_name" id="company_name" value="">'.$sel_restaurant['company_name'].' 
                        </td>
                        <td style="padding: 20px;  border:none; border-collapse: collapse; text-align:right;">اسم العميل</td>
                    </tr>
                    <tr style="    border-bottom:none; ">
                        <td style="padding: 20px; border:none; border-collapse: collapse;">RESTAURANT NAME :</td>
                        <td style="text-align:right;" colspan="2"> <input type="hidden" name="restaurant_name" id="restaurant_name" value="">'.$sel_restaurant['restaurant_name'].'  </td>
                        <td style="padding: 20px;  border:none; border-collapse: collapse; text-align:right;">اسم المطعم </td>
                    </tr>
                    <tr style="    border-bottom:none; ">
                        <td  style="padding: 20px; border:none; border-collapse: collapse;">ADDRESS :</td>
                        <td style="text-align:right;" colspan="2"> <input type="hidden" name="contact_address" id="contact_address" value="">'.$sel_restaurant['contact_address'].' </td> 
                        <td style="padding: 20px;  border:none; border-collapse: collapse; text-align:right;">العنوان </td>
                    </tr>
                    <tr style=" border-bottom:none; ">
                        <td style="padding: 20px; border:none; border-collapse: collapse;">VAT NUMBER :</td>
                        <td style="text-align:right;" colspan="2"> <input type="hidden" name="gst_tax_reg_number" id="gst_tax_reg_number" value="">'.$sel_restaurant['gst_tax_reg_number'].' </td>
                        <td style="padding: 20px; border:none; border-collapse: collapse; text-align:right;">الرقم الضريبي </td>
                    </tr>
                    <tr style=" border-bottom:none; ">
                        <td style="padding: 20px; border:none; border-collapse: collapse;">PERIOD :</td>
                        <td style="text-align:right;" colspan="2"><input type="hidden" name="period" id="period" value="">'.$data['invoice_from'].' - '.$data['invoice_to'].'</td>
                        <td style="padding: 20px; border:none; border-collapse: collapse; text-align:right;">الفترة </td>
                    </tr>

                    <tr style=" border-bottom:none;background-color: #f1d40b;font-weight: bold;">
                        <td style="padding: 20px; border:none; border-collapse: collapse; width: 40%">DESCRIPTION /البيان</td>
                        <td style="padding: 20px; border:none; border-collapse: collapse;">AMOUNT/القيمة</td>
                        <td style="padding: 20px; border:none; border-collapse: collapse;">PERCENTAGE(%)/النسبة</td>
                        <td style="padding: 20px; border:none; border-collapse: collapse;">COMMISSION/العمولة</td>
                    </tr>

                    <tr style="    border-bottom:none; ">
                        <td style="padding: 20px; border:none; border-collapse: collapse;">SALES TAXES EXCLUDED  اجمالي المبيعات بدون الضريبه</td>
                        <td  style=" border:none; text-align: right; padding-right: 1%;">'.number_format($data['sales_taxes_excluded_amount'],2).'</td>
                        <td  style=" border:none; text-align: right; padding-right: 1%;">'.$data['sales_taxes_excluded_percentage'].'%</td>
                        <td  style=" border:none; text-align: right; padding-right: 1%;">
                        <label>'.number_format($data['sales_taxes_excluded_commission'],2).'</label></td>
                    </tr>

                    <tr style="    border-bottom:none;   background-color:#fbf393; ">
                        <td style="padding: 20px; border:none; border-collapse: collapse;">ONLINE PAYMENTS TOTAL اجمالي مبعيات اونلاين</td>
                        <td  style=" border:none; text-align: right; padding-right: 1%;">'.number_format($data['online_payments_total_amount'],2).'</td>
                        <td  style=" border:none; text-align: right; padding-right: 1%;">'.$data['online_payments_total_percentage'].'%</td>
                        <td  style=" border:none; text-align: right; padding-right: 1%;">
                        <label>'.number_format($data['online_payments_total_commission'],2).'</label></td>
                    </tr>

                    <tr style="    border-bottom:none; ">
                        <td style="padding: 20px; border:none; border-collapse: collapse;">PAYMENT COLLECTED BY HB DELIVERY BOYS المبلغ المستلم عن طريق السائق</td>
                        <td  style=" border:none; text-align: right; padding-right: 1%;">
                        <label id="payments_collected_by_hb_driver_amount_label">'.$data['payments_collected_by_hb_driver_amount'].'</label>
                            </td>
                        <td  style=" border:none; text-align: right; padding-right: 1%;">'.$data['payments_collected_by_hb_driver_percentage'].'%</td>
                        <td  style=" border:none; text-align: right; padding-right: 1%;">
                        <label id="payments_collected_by_hb_driver_label">'.number_format($data['payments_collected_by_hb_driver_commission'],2).'</label></td>
                    </tr>

                    <tr style="    border-bottom:none;  background-color:#fbf393; ">
                        <td style="padding: 20px; border:none; border-collapse: collapse;">Refund orders اجمالي الطلبات المرتجعه</td>
                        <td  style=" border:none; text-align: right; padding-right: 1%;">'.number_format($data['refund_orders_amount'],2).'</td>
                        <td  style=" border:none; text-align: right; padding-right: 1%;">'.$data['refund_orders_percentage'].'%</td>
                        <td  style=" border:none; text-align: right; padding-right: 1%;"> <label>'.number_format($data['refund_orders_commission'],2).'</label></td>
                    </tr>

                    <tr style="    border-bottom:none; ">
                        <td style="padding: 20px; border:none; border-collapse: collapse;">Customer compensation  تعويضات العملاء</td>
                        <td  style=" border:none; text-align: right; padding-right: 1%;"> '.number_format($data['customer_compensation_amount'],2).'</td>
                        <td  style=" border:none; text-align: right; padding-right: 1%;"><label>'.$data['customer_compensation_percentage'].'%</label>
                        </td>
                        <td  style=" border:none; text-align: right; padding-right: 1%;">
                        <label id="customer_compensation_label">'.number_format($data['customer_compensation_commission'],2).'</label></td>
                    </tr>

                    <tr style="    border-bottom:none;   background-color:#fbf393; ">
                        <td style="padding: 20px; border:none; border-collapse: collapse;">INVOICE VALUE  قيمة الفاتورة</td>
                        <td  style=" border:none; text-align: right; padding-right: 1%;"> </td>
                        <td  style=" border:none; text-align: right; padding-right: 1%;"> 
                        <label> </label>
                        </td>
                        <td  style=" border:none; text-align: right; padding-right: 1%;"> 
                        <label id="invoice_commission_label">'.number_format($data['invoice_commission'],2).'</label></td>
                    </tr>

                    <tr style="    border-bottom:none; ">
                        <td style="padding: 20px; border:none; border-collapse: collapse;">VAT الضريبة</td>
                        <td  style=" border:none; text-align: right; padding-right: 1%;"> </td>
                        <td  style=" border:none; text-align: right; padding-right: 1%;"> 
                        <label>'.$data['vat_percentage'].'%</label>
                        </td>
                        <td  style=" border:none; text-align: right; padding-right: 1%;"> 
                        <label id="vat_commission_label">'.number_format($data['vat_commission'],2).'</label></td>
                    </tr>

                    <tr style="    border-bottom:none;   background-color:#fbf393; ">
                        <td style="padding: 20px; border:none; border-collapse: collapse;">TOTAL INVOICE VALUE  اجمالي قيمة الفاتورة مع الضريبة</td>
                        <td  style=" border:none; text-align: right; padding-right: 1%;"> </td>
                        <td  style=" border:none; text-align: right; padding-right: 1%;"> 
                        <label> </label>
                        </td>
                        <td  style=" border:none; text-align: right; padding-right: 1%;"> 
                        <label id="total_invoice_label">SAR '.number_format($data['total_invoice_commission'],2).'</label></td>
                    </tr>

                    <tr style="    border-bottom:none;">
                        <td style="padding: 20px; border:none; border-collapse: collapse;">PAID ONLINE  اجمالي مبعيات اونلاين</td>
                        <td  style=" border:none; text-align: right; padding-right: 1%;"> </td>
                        <td  style=" border:none; text-align: right; padding-right: 1%;"><label> </label>
                        </td>
                        <td  style=" border:none; text-align: right; padding-right: 1%;"> 
                        <label>SAR '.number_format($data['paid_online_commission'],2).'</label></td>
                    </tr>

                    <tr style="    border-bottom:none;   background-color:#fbf393; ">
                        <td style="padding: 20px; border:none; border-collapse: collapse;">PAYMENT COLLECTED BY DELIVERY BOYS المبلغ المستلم عن طريق السائق</td>
                        <td  style=" border:none; text-align: right; padding-right: 1%;"> </td>
                        <td  style=" border:none; text-align: right; padding-right: 1%;"><label> </label>
                        </td>
                        <td  style=" border:none; text-align: right; padding-right: 1%;"> 
                        <label>SAR '.number_format($data['payments_collected_by_driver_commission'],2).'</label>
                        </td>
                    </tr>

                    <tr style="    border-bottom:none; background-color: #f1d40b;font-weight: bold; ">
                        <td style="padding: 20px; border:none; border-collapse: collapse;">PAYOUT BALANCE  المبلغ المستحق</td>
                        <td  style=" border:none; text-align: right; padding-right: 1%;"> </td>
                        <td  style=" border:none; text-align: right; padding-right: 1%;"><label> </label>
                        </td>
                        <td  style=" border:none; text-align: right; padding-right: 1%;"> 
                        <label id="payout_balance_label">SAR '.number_format($data['payout_balance_commission'],2).'</label>
                        </td>
                    </tr>

                    <tr>
                        <td colspan="4"> <b><u>INVOICE POLICIES</u></b> </td> 
                    </tr>

                    <tr style=" ">
                        <td  colspan="4" >Clients have authorities to contact our finance department with in a week of invoice issued date , other wise it will be consider as confirmed invoice.<br/>
                        Invoices should be paid with in 10 days of issued date to continue hurrybunny services.<br/>
                        Inoice and order details will be displayed on restaurnat  portal to review.
                        </td>
                    </tr>

                    <tr>
                        <td colspan="4" style="text-align:right;"> <b><u>سياسات الفواتير</u></b> </td> 
                    </tr> 

                    <tr >
                        <td colspan="4" style="text-align:right;"> في حال وجود اعتراض على الفاتورة يتم التواصل مع قسم المحاسبة خلال اسبوع من تاريخ اصدار  الفاتورة،خلاف ذلك سيتم اعتماد الفاتورة.<br/>
                        يتم سداد الفاتورة خلال 10 ايام من تاريخ الاصدار للاستمرار مع خدمات هاري بني. <br/>
                        يمكن مراجعه الفاتورة وتفاصيل الطلبات عن طريق الحساب الخاص بكم بمنصة هاري بني
                        </td> 
                    </tr>
                </tbody> 
            </table>
        </div>';
//echo $html;  exit;
// output the HTML content
$pdf->writeHTML($html, true, false, true, false, '');

// reset pointer to the last page
$pdf->lastPage();

// ---------------------------------------------------------

//Close and output PDF document
$pdf->Output($dir.$ino.'.pdf', 'F');

//============================================================+
// END OF FILE
//============================================================+
