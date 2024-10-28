<?php   ini_set('display_errors', 1);error_reporting(E_ALL);
include_once('em_connections.php');

$date = date('Y-m-d H:i:s');

$expired_bookings = "SELECT em_confirm_booking.id, booking_id, empl_id, notify_to_time, em_booking.ref_no, users.fcm_id, users.name FROM `em_confirm_booking` 
	left join em_booking on em_booking.id =  em_confirm_booking.booking_id
	LEFT JOIN users on users.id = em_confirm_booking.empl_id 
	WHERE alert_status = 1 And accept_status = 0 and notify_to_time<'".$date."' ORDER BY em_booking.id ASC";

$result = mysqli_query($conn, $expired_bookings);

if( mysqli_num_rows($result) > 0) {
	while ($row = mysqli_fetch_array($result)) { 
		$id = $row['id'];
		$empl_id = $row['empl_id'];
		$ref_no = $row['ref_no'];
		$booking_id = $row['booking_id'];

		$update = "update em_confirm_booking set accept_status=3, booking_status='EXPIRED', updated_at='".date('Y-m-d H:i:s')."' where  id=".$id; 
		mysqli_query($conn, $update);

		$fcmMsg = array("fcm" => array("notification" => array(
            "title" => "Booking Expired - Amour",
            "body" => "Booking #" .$ref_no. "  Expired on Amour",
            "type" => 8,
            "booking_id" => $booking_id
        )));
		push_notification($empl_id, $fcmMsg, $conn);
	}
}