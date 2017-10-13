<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\UserDeviceToken;
use Aws\Sns\Exception\SnsException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class UserDeviceTokenController extends Controller
{
    public function getDeviceToken(Request $request)
    {
        $input = $request->only('platform', 'device_token');
        try {
            $deviceToken = UserDeviceToken::whereDeviceToken($input['device_token'])->first();
            if ($deviceToken == null) {
                $platformApplicationArn = '';
                if (isset($input['platform']) && $input['platform'] == 'android') {
                    $platformApplicationArn = env('ANDROID_APPLICATION_ARN');
                } else if (isset($input['platform']) && $input['platform'] == 'ios') {
                    $platformApplicationArn = env('IOS_APPLICATION_ARN');
                }
                $client = App::make('aws')->createClient('sns');
                $result = $client->createPlatformEndpoint(array(
                    'PlatformApplicationArn' => $platformApplicationArn,
                    'Token' => $input['device_token'],
                ));
                $endPointArn = isset($result['EndpointArn']) ? $result['EndpointArn'] : '';
                $deviceToken = new UserDeviceToken();
                $deviceToken->platform = $input['platform'];
                $deviceToken->device_token = $input['device_token'];
                $deviceToken->arn = $endPointArn;
            }
            $deviceToken->save();
        } catch (SnsException $e) {
            return response()->json(['error' => "Unexpected Error"], 500);
        }
        return response()->json(["status" => "Device token processed"], 200);
    }

    public function sendNotification() {
        $notificationTitle = "this is a title";
        $notificationMessage = "this is a message";
        $data = [
            "type" => "Manual Notification" // You can add your custom contents here 
        ];
        $userDeviceTokens = UserDeviceToken::get();
        foreach ($userDeviceTokens as $userDeviceToken) {
            $deviceToken = $userDeviceToken->device_token;
            $endPointArn = array("EndpointArn" => $userDeviceToken->arn);

            $sns = App::make('aws')->createClient('sns');
            $endpointAtt = $sns->getEndpointAttributes($endPointArn);
            if ($endpointAtt != 'failed' && $endpointAtt['Attributes']['Enabled'] != 'false') {
                if ($userDeviceToken->platform == 'android') {
                    $fcmPayload = json_encode(
                        [
                            "notification" =>
                                [
                                    "title" => $notificationTitle,
                                    "body" => $notificationMessage,
                                    "sound" => 'default'
                                ],
                            "data" => $data // data key is used for sending content through notification.
                        ]
                    );
    
                    $message = json_encode(["default" => $notificationMessage, "GCM" => $fcmPayload]);
                    $sns->publish([
                        'TargetArn' => $userDeviceToken->arn,
                        'Message' => $message,
                        'MessageStructure' => 'json'
                    ]);
                }
            }
        }
    }
}