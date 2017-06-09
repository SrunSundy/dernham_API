<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require APPPATH .'/libraries/plugin/push_notification/vendor/autoload.php';

use Sly\NotificationPusher\PushManager,
Sly\NotificationPusher\Adapter\Gcm as GcmAdapter,
Sly\NotificationPusher\Collection\DeviceCollection,
Sly\NotificationPusher\Model\Device,
Sly\NotificationPusher\Model\Message,
Sly\NotificationPusher\Model\Push
;

if ( ! function_exists('push_notification'))
{
	function push_notification($token_id,$user){
		$pushManager = new PushManager(PushManager::ENVIRONMENT_DEV);
		
		// Then declare an adapter.
		$gcmAdapter = new GcmAdapter(array(
				'apiKey' => 'AAAAVFtZTBU:APA91bEznrS4pit-LclbbPUxn-EHKTs1omj-Fx2I1NS3-Zso5t-Oz_ifnmv1prL_av-xMAXVBtl-BFjRJhXumAFtidAD_bio29bevwlLXM_z4q0ijmQUMaV-VaMrBs63RQyr4ALLgtnz',
		));
		
		// Set the device(s) to push the notification to.
		$devices = new DeviceCollection(array(
				new Device($token_id->token_id),
		));
		
		// Then, create the push skel.
		$message = new Message($user->user_fullname.' commented on your post.');
		
		// Finally, create and add the push to the manager, and push it!
		$push = new Push($gcmAdapter, $devices, $message);
		$pushManager->add($push);
		$pushManager->push(); // Returns a collection of notified devices
		
	}
}

