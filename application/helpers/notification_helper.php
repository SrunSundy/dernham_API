<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require APPPATH .'/libraries/plugin/push_notification/vendor/autoload.php';

use Sly\NotificationPusher\PushManager,
Sly\NotificationPusher\Adapter\Gcm as GcmAdapter,
Sly\NotificationPusher\Adapter\Apns as ApnsAdapter,
Sly\NotificationPusher\Collection\DeviceCollection,
Sly\NotificationPusher\Model\Device,
Sly\NotificationPusher\Model\Message,
Sly\NotificationPusher\Model\Push
;

if ( ! function_exists('push_notification'))
{
  function push_notification($device, $user, $post_id){
    $pushManager = new PushManager(PushManager::ENVIRONMENT_DEV);
    $adapter;
    $message;

    if(strtoupper($device->os_type)==strtoupper('ios')){
        $adapter = new ApnsAdapter(array(
          'certificate' => APPPATH .'/libraries/plugin/push_notification/vendor/ck.pem',
          'passPhrase' => '1234'
        )); 
        $message = new Message($user->user_fullname.' commented on your post.', array(
            'custom' => array('post_id' => $post_id)
        ));

    }else{
        $adapter = new GcmAdapter(array(
         'apiKey' => 'AAAAP1WfdL4:APA91bF7VhToT-I0ybw-yGkxzqjNEVz-NInfaRE2P470aT8VFLbGu6ULyDhzJ4YOVYxzFTucy__UM6a619uzaaQkwgiV9wQ9T9emp7LGqZToUaTk4JHcndjaUE7EA3CpkiRRDns-tZ_q'));
        $message = new Message($user->user_fullname.' commented on your post.', array('post_id' => $post_id));    
    }
    
    // Set the device(s) to push the notification to.
    $devices = new DeviceCollection(array(
        new Device($device->token_id),
    ));

    // Finally, create and add the push to the manager, and push it!
    $push = new Push($adapter, $devices, $message);
    $pushManager->add($push);
    $pushManager->push(); // Returns a collection of notified devices
    
  }
}

