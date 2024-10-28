<?php 

function push_notification($user_id, $fcmMsg, $conn)    {
    $fcmid = '';
    $userqry = "SELECT  fcm_id from users where id=".$user_id;
    $result_users1 = mysqli_query($conn, $userqry);
    if( mysqli_num_rows($result_users1) > 0) {
        while ($row = mysqli_fetch_array($result_users1)) { 
          $fcmid = $row['fcm_id'];
        }
    }
 

    DB::table('em_notifications')->insert([
      'user_id'=>$user_id,
      'fcm_id'=>$fcmid,
      'title'=>$fcmMsg['fcm']['notification']['title'],
      'message'=>$fcmMsg['fcm']['notification']['body'],
      'created_at'=>date('Y-m-d H:i:s'),
      'notify_date'=>date('Y-m-d H:i:s'),
    ]);
      

      $title = $fcmMsg['fcm']['notification']['body'];
      $message = $fcmMsg['fcm']['notification']['title'];

      $fcmMsg['fcm']['notification']['body'] = $message;
      $fcmMsg['fcm']['notification']['title'] = $title;
      $fcmMsg['fcm']['notification']['sound'] = "default";
      $fcmMsg['fcm']['notification']['color'] = "#203E78";

      $fcmMsgSend = $fcmMsg['fcm']['notification'];

      pushSendUserNotification($fcmid, $message, $title, $fcmMsgSend);
 
    return true;
  }



function pushSendUserNotification($fcmid, $message, $title, $fcmMsg=[])   {

    if (!defined('FIREBASE_KEY')) {
        define('FIREBASE_KEY', 'AAAAtYCx0h0:APA91bFKOeat5Dt4YtcPVRa58G06ZNl997HnjzhUJ0rWa-tNi383-vQrowjrMxYGg6ft5XBP9M32yAbhFYoXUgvvS5o4v6-LpF4ynYuv0KuDCgCNwwHp2kZMOCh4Vly1n4ikemCMBGAb');
    }

    $type = isset($fcmMsg["fcm"]["notification"]["type"]) ? $fcmMsg["fcm"]["notification"]["type"] : "100";
    $order_id = isset($fcmMsg["fcm"]["notification"]["order_id"]) ? $fcmMsg["fcm"]["notification"]["order_id"] : "0";

    $fcmMsg['fcm']['notification']['body'] = $message;
    $fcmMsg['fcm']['notification']['title'] = $title;
    $fcmMsg['fcm']['notification']['sound'] = "default";
    $fcmMsg['fcm']['notification']['color'] = "#203E78";
    $fcmMsg['fcm']['notification']['type'] = $type;
    $fcmMsg['fcm']['notification']['order_id'] = $order_id;

    $fcmMsgSend = $fcmMsg['fcm']['notification'];


    if ($fcmid) {
        /*$fcmMsg = array(
            'title' => $title,
            'body' => $message,
            'sound' => "default",
            'color' => "#203E78",
            'type' => $type,
            'order_id' => $order_id
        );  echo "<pre>"; print_r($fcmMsgSend); exit;*/
        $fcmMsg = $fcmMsgSend;

        $fcmFields = array(
            'to' => $fcmid,
            'priority' => 'high',
            'notification' => $fcmMsg,
            'data' => $fcmMsg
        );

        $headers = array(
            'Authorization: key=' . FIREBASE_KEY,
            'Content-Type: application/json'
        );

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fcmFields));
        $result = curl_exec($ch);
        curl_close($ch);

    }
    return true;
  }
 

?>