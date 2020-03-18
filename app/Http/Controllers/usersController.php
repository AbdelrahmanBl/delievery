<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User_Devices;
use App\User;
use App\UserRequest;
use App\UserRequestPayment;
use App\UserRequestRating;
use App\UserWallet;
use App\ProviderWallet;
use App\Provider;
use App\Card;
use App\Complaint;
use App\Location;
use Cookie;
use DB;
use Auth;
use PushNotification;
use Paytabs;

class usersController extends Controller
{
	public function return($result)
	{
		return [
    		'error_flag' => 0,
    		'message' => 'success',
    		'result'=> $result
    	];
	}
	public function returnError($result)
	{
		return [
    		'error_flag'    => 1,
            'message' => $result,
            'result'  => NULL,
    	];
	}
	public function invalidOTP($where)
	{
		return DB::table('user_devices')->where($where)->update([
            'valid'   => 0
        ]);
	}
	public function last_login($token)
	{
		DB::table('users')->where('remember_token',$token)->update([
			'last_login' => date("Y-m-d H:i:s"),
		]);
	}
    public function sendFCM($to,$title,$body)
    {
        $push = PushNotification::setService('fcm')
                        ->setMessage([
                             'notification' => [
                                     'title'=>$title,
                                     'body'=>$body,
                                     'sound' => 'default'
                                     ],
                             'data' => [
                                     'extraPayLoad1' => 'value1'
                                     ]
                             ])
                        ->setApiKey(env('FCM_SERVER_KEY'))
                        ->setDevicesToken($to)//Array
                        ->send()
                        ->getFeedback(); 
    }
    public function generate_otp($mobile)
    {
        $where = array(
            'mobile'   => $mobile,
            'valid'    => 1
        );
        $CHK_OTP = DB::table('user_devices')->where($where)->first();
        if($CHK_OTP)
            $OTP = (string)$CHK_OTP->otp; 
        else {
            /*$break = false;
            while($break != true){
                $OTP = mt_rand(1000, 9999);
                $where = array(
                'otp'    => $OTP,
                'valid'  => 1
            );
                if(!DB::table('user_devices')->where($where)->first())
                    $break = true;
            }*/
            $OTP = mt_rand(1000, 9999);
            $User_Device = new user_devices([
                'mobile' => $mobile,
                'otp'    => $OTP,
            ]);
            $User_Device->save();
        }
       
      return (string)$OTP;
    }
    public function sent_otp(Request $req)
    {
    	$req->validate([
    		'mobile' => 'required|numeric',
    	]);
        $mobile = $req->input('mobile');
    	$OTP = $this->generate_otp($mobile);
    	return $this->return([
    		'otp'   => $OTP
    	]);
    }
    public function verify_otp(Request $req)
    {
    	$req->validate([
    		'otp'     => 'required|digits:4|exists:user_devices',
            'mobile'  => 'required|exists:user_devices'
    	]);
        $OTP     = $req->input('otp');
        $mobile  = $req->input('mobile');
        $where = array(
            'otp'    =>  $OTP,
            'mobile' =>  $mobile,
            'valid'  =>  1
        );
    	$User = DB::table('user_devices')->where($where)->first();
        if($User){
    	$UserData = DB::table('users')->where('mobile',$User->mobile)->first();
    	$this->invalidOTP($where);
        if($UserData){
    	 	Auth::loginUsingId($UserData->id,true);
    		$User = Auth::User();
    		$this->last_login($User->remember_token);
    	    return $this->return([
    		'register'  => true, 
    		'data'    => $User,
    	    ]);
    	}    
    	else{
    		return $this->return([
    		'register'  => false,
    		'mobile'    => $User->mobile,
    		]);
          }  
        }
        return $this->returnError('please Go To sent Otp');
    }
    public function signup(Request $req)
    {
    	$req->validate([
    		'first_name' 	=> 'required|string|max:15',
    		'last_name'		=> 'required|string|max:15',
    		'email'			=> 'email|unique:users',
    		'gender'		=> 'required|in:M,F,U',
    		'mobile'		=> 'required|numeric',
    		'device_type'	=> 'required|in:android,ios,Web',
    	]);

    	$first_name      = $req->input('first_name');
    	$last_name		 = $req->input('last_name');
    	$email           = $req->input('email'); //optional
    	$gender          = $req->input('gender');
    	$mobile          = $req->input('mobile');
    	$device_type     = $req->input('device_type');

    	$UserData = DB::table('users')->where('mobile',$mobile)->first();
    	if($UserData){
    	Auth::loginUsingId($UserData->id,true);
    	$User = Auth::User();	
    	$this->last_login($User->remember_token);
        $register = true;
      }
      else{
      	$User = new User([
			'first_name'      => $first_name ,
			'last_name'		  => $last_name ,
			'email'			  => $email ,
			'gender'		  => $gender ,
			'mobile'		  => $mobile ,
			'device_type'     => $device_type ,		
    	]);
    	$User->save();
    	Auth::loginUsingId($User->id,true);
    	$User = Auth::User();
    	$this->last_login($User->remember_token);
        $register = false;
      }
      return $this->return([
    		'register'  => $register, 
    		'data'      => $User,
    	]);
    }
    public function get_profile(Request $req)
    {
    	$user_id = $req->input('user_id');
    	$User  = DB::table('users')->where('id',$user_id)->first();
    	if($User)
        return $this->return([
    		'register'  => true, 
    		'data'      => $User,
    		]);
        else
            return $this->returnError('User Not Found');
    }
    public function update_profile(Request $req)
    {
        $req->validate([
            'first_name'    => 'string|max:15',
            'last_name'     => 'string|max:15',
            'email'         => 'email|unique:users',
            'gender'        => 'in:M,F,U',
            'mobile'        => 'numeric',
            'device_type'   => 'in:android,ios,Web',
        ]);
        $token = $req->header('remember-token');
        $User = DB::table('users')->where('remember_token',$token);
        $User->update($req->all());
        return $this->return([
            'register' => true,
            'data'     => $User->first(),
        ]);
    }
    public function get_services(Request $req)
    {
        $req->validate([
            'latitude'       => 'required|numeric',
            'longitude'      => 'required|numeric'
        ]);   
        $latitude  = $req->input('latitude');
        $longitude = $req->input('longitude');

        $smartWhere = array(
            ['status'      ,      1],
            ['latitude'    ,'>',  0],
            ['longitude'   ,'>',  0]
        );
        $smartService = DB::table('service_types')->where($smartWhere)->selectRaw('*,(
            (
                (
                    acos(
                        sin(( '.$latitude.' * pi() / 180))
                        *
                        sin(( `latitude` * pi() / 180)) + cos(( '.$latitude.' * pi() /180 ))
                        *
                        cos(( `latitude` * pi() / 180)) * cos((( '.$longitude.' - `longitude`) * pi()/180)))
                ) * 180/pi()
            ) * 60 * 1.1515
        ) as distance')->get()->where('distance','<=',500);

        $where = array(
            'status'      =>  1,
            'latitude'    =>  0,
            'longitude'   =>  0
        );
        $Services  = DB::table('service_types')->where($where)->get();
        return $this->return([
            'smartServices'  => $smartService,
            'services'   => $Services
        ]);
    }
    public function check_promocode(Request $req)
    {
        $req->validate([
            'promo_code'  => 'required|exists:promocodes'
        ]);
        $promo_code = $req->input('promo_code');
        $promo = DB::table('promocodes')->where('promo_code',$promo_code)->first();
        if($promo && $promo->status  == 'APPROVED'){
            if($promo->expiration < date('Y-m-d') ){
                DB::table('promocodes')->update([
                    'status'     =>  'EXPIRED'
                ]);
                return $this->returnError('promocode is EXPIRED');
            }
           return $this->return([$promo]); 
        }
        return $this->returnError('promocode is EXPIRED');
    }
    public function add_request(Request $req)
    {
        $req->validate([
            'travel_time'      => 'required|numeric',
            'is_best_price'    => 'required|in:YES,NO',
            'user_id'          => 'required|exists:users,id',
            'payment_mode'     => 'required|in:CASH,VISA,MADA,SUBSCRIBED',
            'distance'         => 'required|numeric',
            //'s_name'           => 'required|string',
            's_address'        => 'required|string|max:100',
            's_latitude'       => 'required|numeric',
            's_longitude'      => 'required|numeric',
            //'d_name'           => 'required|string',
            'd_address'        => 'required|string|max:100',
            'd_latitude'       => 'required|numeric',
            'd_longitude'      => 'required|numeric',
            'is_scheduled'     => 'required|in:YES,NO',
            'service_type_id'  => 'required|exists:service_types,id',
            //'schedule_at'      => 'string',
            'promocode_id'     => 'numeric|exists:promocodes,id' 
        ]);

        $travel_time      =  $req->input('travel_time');
        $is_best_price    =  $req->input('is_best_price');
        $user_id          =  $req->input('user_id');
        $payment_mode     =  $req->input('payment_mode');
        $distance         =  (double)$req->input('distance');
        $s_name           =  $req->input('s_name');
        $s_address        =  $req->input('s_address');
        $s_latitude       =  (double)$req->input('s_latitude');
        $s_longitude      =  (double)$req->input('s_longitude');
        $d_name           =  $req->input('d_name');
        $d_address        =  $req->input('d_address');
        $d_latitude       =  (double)$req->input('d_latitude');
        $d_longitude      =  (double)$req->input('d_longitude');
        $is_scheduled     =  $req->input('is_scheduled');
        $service_type_id  =  $req->input('service_type_id');
        $promocode_id     =  $req->input('promocode_id');
        
        if($promocode_id && DB::table('promocodes')->where(array('id'=>$promocode_id,'status'=>'EXPIRED'))->first())
            return $this->returnError('promocode is EXPIRED');

        ($is_scheduled == 'YES')?$schedule_at = $req->input('schedule_at'):$schedule_at      =  NULL;

        ($is_scheduled == 'YES')? $status = 'SCHEDULED' : $status = 'SEARCHING' ;
        $booking_id = $user_id + time();

        //Add To Locations Table
        $place = Location::where(array('user_id'=>$user_id,'address'=>$d_address))->first();
        if(!$place){
        $Location     = new Location([
            'user_id'       => $user_id,
            'name'          => NULL,
            'address'       => $d_address,
            'latitude'      => $d_latitude,
            'longitude'     => $d_longitude,
            'type'          => NULL,
        ]);
        $Location->save();
        }
        $User_Request = new UserRequest([
            'booking_id'        => $booking_id ,
            'user_id'           => $user_id ,
            'provider_id'       => 0,
            'payment_mode'      => $payment_mode ,
            'distance'          => $distance ,
            's_address'         => $s_address ,
            's_latitude'        => $s_latitude ,
            's_longitude'       => $s_longitude ,
            'd_address'         => $d_address ,
            'd_latitude'        => $d_latitude ,
            'd_longitude'       => $d_longitude ,
            'is_scheduled'      => $is_scheduled ,
            'status'            => $status ,
            'service_type_id'   => $service_type_id,
            'schedule_at'       => $schedule_at,
            'promocode_id'      => $promocode_id,
            'is_best_price'     => $is_best_price,
            'travel_time'       => $travel_time,
        ]);
        $User_Request->save();

        $arr  = array();
        $providers = Provider::where('city_code','')->selectRaw('social_unique_id,(
            (
                (
                    acos(
                        sin(( '.$s_latitude.' * pi() / 180))
                        *
                        sin(( `latitude` * pi() / 180)) + cos(( '.$s_latitude.' * pi() /180 ))
                        *
                        cos(( `latitude` * pi() / 180)) * cos((( '.$s_longitude.' - `longitude`) * pi()/180)))
                ) * 180/pi()
            ) * 60 * 1.1515
        ) as distance')->limit(15)->get()->where('distance','<=',6);

        foreach ($providers as $row) {
            $arr[] = $row->social_unique_id;
        }

        $to    = $arr;
        $title = 'ADD Request';
        $body  = $status;
        $this->sendFCM($to,$title,$body);
        return $this->return([
            'booking_id'  => $User_Request->booking_id
        ]);
    }
    public function update_request(Request $req)
    {
        $req->validate([
            'booking_id'   =>  'required|exists:user_requests',
            'status'       =>  'required|in:ACCEPTED,STARTED,ARRIVED,PICKEDUP,DROPPED,COMPLETED',
        ]);
        $booking_id  =  $req->input('booking_id');
        $status      =  $req->input('status');
        DB::table('user_requests')->where('booking_id',$booking_id)->update([
            'status'       =>  $status,
        ]);
        $Trip  = UserRequest::where('booking_id',$booking_id)->first();
        if(!$Trip->provider_id)
            return $this->returnError('provider_id is 0');
        $to    = [DB::table('providers')->where('id',$Trip->provider_id)->first()->social_unique_id];
        $title = 'ADD Request';
        $body  = $Trip->status;
        $this->sendFCM($to,$title,$body);
        return $this->return([
            'status'  => $Trip->status
        ]);
    }
    public function cancel_request(Request $req)
    {
        $req->validate([
            'booking_id'     =>  'required|exists:user_requests',
            'cancel_reason'  =>  'required|in:NO_SHOW,CHANGE_MIND,LATE,ISFAR,OTHER',
        ]);
        $booking_id     =  $req->input('booking_id');
        $cancel_reason  =  $req->input('cancel_reason');
        DB::table('user_requests')->where('booking_id',$booking_id)->update([
            'status'        =>  'CANCELLED',
            'cancelled_by'  =>  'USER',
            'cancel_reason' => $cancel_reason ,
        ]);
        return $this->return([]);
    }
    public function register_device(Request $req)
    {
        $req->validate([
            'user_id'           =>  'required|exists:users,id',
            'social_unique_id'  =>  'required',
        ]);
        $user_id             = $req->input('user_id');
        $social_unique_id    = $req->input('social_unique_id');

        DB::table('users')->where('id',$user_id)->update([
            'social_unique_id'  => $social_unique_id
        ]);
        return $this->return([]);
    }
    public function check_status(Request $req)
    {
        $req->validate([
            'user_id'   =>  'required|exists:users,id'
        ]);
        $user_id = $req->input('user_id');
        $status = ['SEARCHING','ACCEPTED','STARTED','ARRIVED','PICKEDUP','DROPPED'];
        $provider_service = NULL;
        $promocode = NULL;
        $CHKStatus    = UserRequest::where('user_id',$user_id)->whereIn('status',$status)->first();
        if(!$CHKStatus)
            return $this->return('No Trip Available') ;
        $provider     = DB::table('providers')->where('id',$CHKStatus->provider_id)->first();
        if($provider)
        $provider_service = DB::table('provider_services')->where('provider_id',$provider->id)->first();
        $service_type = DB::table('service_types')->where('id',$CHKStatus->service_type_id)->first();
        if($CHKStatus->promocode)
            $promocode = DB::table('promocodes')->where()->first();$CHKStatus->promocode;
            if($CHKStatus->status == 'DROPPED')
            return $this->return([
                'invoice'      => UserRequestPayment::where('request_id',$CHKStatus->id)->first()
            ]);    
            return $this->return([
                /*'status'      =>  $CHKStatus->status,
                'trip'        =>  $CHKStatus ,
                'first_name'  =>  $provider->first_name,
                'last_name'   =>  $provider->last_name,
                'status'      =>  $provider->status*/
                'provider'          => $provider,
                'provider_service'  => $provider_service,
                'service_type'      => $service_type,
                'request'           => $CHKStatus
            ]);
         

    }
    public function history_trips(Request $req)
    {
        $req->validate([
            'user_id'   =>  'required|exists:user_requests'
        ]);
        $user_id   = $req->input('user_id');
        $Trip = DB::table('user_requests')->where('user_requests.user_id',$user_id)->where('paid',1)->join('service_types', 'user_requests.service_type_id', '=', 'service_types.id')->join('user_request_payments', 'user_requests.id', '=', 'user_request_payments.request_id')->select('user_requests.status','user_requests.started_at as from','user_requests.finished_at as to','user_requests.created_at as date','user_requests.booking_id','user_request_payments.total as amount','service_types.name as service_type')->get();
        if($Trip)
        return $this->return([
            'Trips'     =>  $Trip
        ]);
        else
            return $this->returnError('Error cntact Admin');
    }
    public function cancels_trips(Request $req)
    {
        $req->validate([
            'user_id'   =>  'required|exists:user_requests'
        ]);
        $user_id   = $req->input('user_id');
        $where = array(
            'user_id'    => $user_id,
            'status'     => 'CANCELLED'
        );
        $Trip = DB::table('user_requests')->where($where)->get();
        if(count($Trip) > 0)
        return $this->return([
            'Trips'     =>  $Trip
        ]);
        else
            return $this->return([
            'Trips'     =>  NULL
        ]);
    }
    public function upcoming_trips(Request $req)
    {
        $req->validate([
            'user_id'   =>  'required|exists:user_requests'
        ]);
        $user_id   = $req->input('user_id');
        $where = array(
            'user_id'    => $user_id,
            'status'     => 'SCHEDULED'
        );
        $Trip = DB::table('user_requests')->where($where)->get();
        if(count($Trip) > 0)
        return $this->return([
            'Trips'     =>  $Trip
        ]);
        else
            return $this->return([
            'Trips'     =>  NULL
        ]);
    }
    public function user_wallets(Request $req)
    {
        $req->validate([
            'user_id'   =>  'required|exists:user_requests'
        ]);
        $user_id   = $req->input('user_id');
        $where = array(
            'user_id'    => $user_id
        );
        $wallets = DB::table('user_wallets')->where($where)->get();
        if(count($wallets) > 0)
        return $this->return([
            'wallets'     =>  $wallets
        ]);
        else
            return $this->return([
            'wallets'     =>  NULL
        ]);
    }
    public function add_card(Request $req)
    {
        $req->validate([
            'user_id'      =>  'required|exists:users,id',
            'last_four'    =>  'required|digits:4',
            'card_id'      =>  'required|string', 
            'brand'        =>  'required|string'
        ]);
        $user_id        = $req->input('user_id');
        $last_four      = $req->input('last_four');
        $card_id        = $req->input('card_id');
        $brand          = $req->input('brand');
    
        $where     = array(
            'user_id'    => $user_id,
            'is_default' => 1
        );
        $cards = DB::table('cards')->where($where)->first();
        if($cards){
        $Card = new Card([
            'user_id'      => $user_id,
            'last_four'    => $last_four,
            'card_id'      => $card_id,
            'brand'        => $brand,
            'is_default'   => 0

        ]);
        $Card->save();    
        return $this->return([
            'is_default'   =>  false,
            'card'         =>  $Card
           ]);
        }
        else{
        $Card = new Card([
            'user_id'      => $user_id,
            'last_four'    => $last_four,
            'card_id'      => $card_id,
            'brand'        => $brand,
            'is_default'   => 1

        ]);
        $Card->save();    
        return $this->return([
            'is_default'   =>  true,
            'card'         =>  $Card
           ]);
        }
    }
    public function set_default(Request $req)
    {
        $req->validate([
            'id'           =>  'required|exists:cards',
            'user_id'      =>  'required|exists:users,id',
        ]);
        $id             = $req->input('id');
        $user_id        = $req->input('user_id');

        $where     = array(
            'user_id'    => $user_id,
            'is_default' => 1
        );
        $cards = Card::where($where);
        if($cards->first()){
        $cards->update([
            'is_default' => 0
        ]);    
        Card::where('id',$id)->update([
            'is_default' => 1
        ]);
        return $this->return([
            'is_default'   =>  true
           ]);
        }
        else{    
        return $this->returnError("Error In Selection");
        }
    }
    public function get_cards(Request $req)
    {
        $req->validate([
            'user_id'      =>  'required|exists:users,id',
        ]);
        $user_id        = $req->input('user_id');

        $where     = array(
            'user_id'    => $user_id
        );
        $cards = Card::where($where)->get();
        if(count($cards) > 0){
        return $this->return([
            'cards'   =>  $cards
           ]);
        }
        else{    
        return $this->return([
            'cards'   =>  NULL
           ]);
        }
    } 
    public function delete_card(Request $req)
    {
        $req->validate([
            'id'           =>  'required|exists:cards',
            'user_id'      =>  'required|exists:users,id',
        ]);
        $id             = $req->input('id');
        $user_id        = $req->input('user_id');

        $where     = array(
            'user_id'    => $user_id ,
            'id'         => $id
        );
        $cards = Card::where($where);
        $cards->delete();
        return $this->return([
            'card'   =>  'Card Deleted Successfully'
        ]);
        
    }
    public function trip_details(Request $req)
    {
        $req->validate([
            'trip_id'    =>  'required|exists:user_requests,id|exists:user_request_payments,request_id|exists:user_request_ratings,request_id'
        ]);
        $trip_id  = $req->input('trip_id');
        $Trip = DB::table('user_requests')->where('user_requests.id',$trip_id)->join('user_request_payments', 'user_requests.id', '=', 'user_request_payments.request_id')->join('user_request_ratings', 'user_requests.id', '=', 'user_request_ratings.request_id')->join('users', 'user_requests.user_id', '=', 'users.id')->select('user_requests.*','user_request_payments.*','user_request_ratings.*','users.*')->get();
        /*->select('user_requests.payment_mode as payment_type','user_requests.distance','user_request_payments.tax','user_request_payments.total','user_request_ratings.user_rating as rate','user_request_ratings.user_comment as comment','users.first_name','users.last_name')*/
        return $this->return([
            'data'  =>  $Trip
        ]);
    }
    public function change_mobile(Request $req)
    {
        $req->validate([
            'mobile'        => 'required|numeric',
            'user_id'   => 'required|exists:users,id',
        ]);
        $mobile         = $req->input('mobile');
        $user_id    = $req->input('user_id');
        DB::table('users')->where('id',$user_id)->update([
            'mobile'    =>  $mobile
        ]);
        $User = DB::table('users')->where('id',$user_id)->first();
        return $this->return([
            'data'   =>  $User
        ]);
    }
    public function check_email(Request $req)
    {
        $req->validate([
            'email'        => 'required|email|unique:users',
        ]);
        return $this->return([]);
    }
    public function rate_trip(Request $req)
    {
        $req->validate([
            'trip_id'         =>  'required|exists:user_requests,id',
            'rate'            =>  'required|numeric|min:0|max:5',
            'comment'         =>  'required|string|max:100'
        ]);
        $trip_id      =  $req->input('trip_id');
        $rate         =  $req->input('rate');
        $comment      =  $req->input('comment');
        DB::table('user_requests')->where('id',$trip_id)->update([
            'user_rated'   =>  $rate
        ]);
        $Trip =  DB::table('user_requests')->where('id',$trip_id)->first();
        $UserRequestRating = new UserRequestRating([
            'request_id'      => $Trip->id,
            'user_id'         => $Trip->user_id,
            'provider_id'     => $Trip->provider_id,
            'user_rating'     => $Trip->user_rating,
            'user_comment'    => $comment,
        ]);
        $UserRequestRating->save();
        return $this->return([]);
    }
    public function chk_user_balance($user_id,$amount)
    {
        $UserWallet = DB::table('user_wallets')->where('user_id',$user_id)->orderBy('id', 'desc')->first();
        if(!$UserWallet || $UserWallet->user_id != $user_id || $UserWallet->close_balance  <  $amount)
            return false;
        return true;
    }
    public function chk_user_mobile($user_id,$mobile)
    {
        $UserMobile = DB::table('users')->where('id',$user_id)->first()->mobile;
        if($UserMobile == $mobile)
            return false;
        return true;
    }
    public function transfer_money(Request $req)
    {
        $req->validate([
            'user_id'        =>  'required|numeric|exists:users,id',
            'mobile'         =>  'required|numeric|exists:users',
            'amount'         =>  'required|numeric',
        ]);
        $user_id = $req->input('user_id');
        $mobile  = $req->input('mobile');
        $amount  = $req->input('amount');

        if(!$this->chk_user_mobile($user_id,$mobile))
            return $this->returnError("You cant't transfer money to yourself ");
        if(!$this->chk_user_balance($user_id,$amount))
            return $this->returnError('No Balance Available');
        //Send OTP Message
        $OTP = $this->generate_otp($mobile);
        return $this->return([
            'otp'   => $OTP
        ]);

    }
    public function balance($user_id,$change,$type)
    {
        $User = DB::table('user_wallets')->where('user_id',$user_id)->orderBy('id', 'desc')->first();
        if(!$User)
            return [
                'open_balance'     => 0,
                'close_balance'    => $change
            ];
        if($type == 'D')
            return [
                'open_balance'     => $User->close_balance,
                'close_balance'    => $User->close_balance + (double)$change
            ];
        elseif($type == 'C')
            return [
                'open_balance'     => $User->close_balance,
                'close_balance'    => $User->close_balance - (double)$change
            ];
    }
    public function balance_provider($user_id,$change,$type)
    {
        $User = DB::table('provider_wallets')->where('provider_id',$user_id)->orderBy('id', 'desc')->first();
        if(!$User)
            return [
                'open_balance'     => 0,
                'close_balance'    => $change
            ];
        if($type == 'D')
            return [
                'open_balance'     => $User->close_balance,
                'close_balance'    => $User->close_balance + (double)$change
            ];
        elseif($type == 'C')
            return [
                'open_balance'     => $User->close_balance,
                'close_balance'    => $User->close_balance - (double)$change
            ];
    }
    public function verify_transfer(Request $req)
    {
        $req->validate([
            'user_id'        =>  'required|numeric|exists:users,id',
            'mobile'         =>  'required|numeric|exists:users',
            'code'           =>  'required|numeric',
            'amount'         =>  'required|numeric',
        ]);
        $user_id = $req->input('user_id');
        $mobile  = $req->input('mobile');
        $code    = $req->input('code');
        $amount  = $req->input('amount');
        
        $where = array(
            'mobile'   => $mobile,
            'otp'      => $code,
            'valid'    => 1
        );
        if(!DB::table('user_devices')->where($where)->first())
            return $this->returnError("Invalid OTP number");
        if(!$this->chk_user_mobile($user_id,$mobile))
            return $this->returnError("You cant't transfer money to yourself ");
        if(!$this->chk_user_balance($user_id,$amount))
            return $this->returnError('No Balance Available');

        $To_id = DB::table('users')->where('mobile',$mobile)->first()->id;

        $transaction_id = $user_id + time();
        $balance        = $this->balance($user_id,$amount,'C');
        $open_balance   = $balance['open_balance'];
        $close_balance  = $balance['close_balance'];
        $UserWallet_sender = new UserWallet([
            'user_id'           =>  $user_id,
            'amount'            =>  -$amount,
            'transaction_id'    =>  $transaction_id,
            'transaction_alias' =>  NULL,
            'open_balance'      =>  $open_balance,
            'close_balance'     =>  $close_balance,
            'type'              =>  'C'

        ]);
        $transaction_id = $To_id + time();
        $balance        = $this->balance($To_id,$amount,'D');
        $open_balance   = $balance['open_balance'];
        $close_balance  = $balance['close_balance'];
        $UserWallet_reciever = new UserWallet([
            'user_id'           =>  $To_id,
            'amount'            =>  $amount,
            'transaction_id'    =>  $transaction_id,
            'transaction_alias' =>  NULL,
            'open_balance'      =>  $open_balance,
            'close_balance'     =>  $close_balance,
            'type'              =>  'D'
        ]);
        $UserWallet_sender->save();
        $UserWallet_reciever->save();
        DB::table('user_devices')->where($where)->update([
            'valid'    =>  0
        ]);
        return $this->return([]);
    }
    public function WALLET($user_id,$provider_id,$amount)
    {
        $transaction_id = $user_id + time();
        $balance        = $this->balance($user_id,$amount,'C');
        
        $open_balance   = $balance['open_balance'];
        $close_balance  = $balance['close_balance'];
        $UserWallet = new UserWallet([
            'user_id'           =>  $user_id,
            'amount'            =>  -$amount,
            'transaction_id'    =>  $transaction_id,
            'transaction_alias' =>  NULL,
            'open_balance'      =>  $open_balance,
            'close_balance'     =>  $close_balance,
            'type'              =>  'C'

        ]);

        $transaction_id = $provider_id + time();
        $balance        = $this->balance_provider($provider_id,$amount,'D');
        $open_balance   = $balance['open_balance'];
        $close_balance  = $balance['close_balance'];
        $ProviderWallet = new ProviderWallet([
            'provider_id'       =>  $provider_id,
            'amount'            =>  $amount,
            'transaction_id'    =>  $transaction_id,
            'transaction_alias' =>  NULL,
            'open_balance'      =>  $open_balance,
            'close_balance'     =>  $close_balance,
            'type'              =>  'D'
        ]);
        $UserWallet->save();
        $ProviderWallet->save();
        return true;
    }
    public function send_payment(Request $req)
    {
        $req->validate([
            //'user_id'         =>  'required|exists:users,id',
            'trip_id'         =>  'required|exists:user_requests,id|unique:user_request_payments,request_id',
            'payment_mode'    =>  'required|in:WALLET,VISA,MADA,SUBSCRIBED',
            'status'          =>  'in:DROPPED',
            'amount'          =>   'numeric|min:0|not_in:0'
        ]);

        $trip_id         = $req->input('trip_id');
        $payment_mode    = $req->input('payment_mode');
        $status          = $req->input('status');
        $reference_no    = $req->input('reference_no');
        $amount          = $req->input('amount');
        
        $Trip =  DB::table('user_requests')->where('id',$trip_id)->first();
        $user_id = $Trip->user_id;
        if($payment_mode == 'WALLET'){
            if(!$this->chk_user_balance($user_id,$amount))
            return $this->returnError('No Balance Available');
            if(!$this->WALLET($user_id,$Trip->provider_id,$amount))
                return $this->returnError('Something Went Wrong');
        }

        DB::table('user_requests')->where('id',$trip_id)->update([
            'payment_mode'   =>  $payment_mode,
            'paid'           =>  1,
            'status'         =>  'DROPPED'
        ]);
        if($status == 'DROPPED')
        DB::table('providers')->where('id',$Trip->provider_id)->update([
            'provider_status'     =>  'Online'
        ]);
        
        $UserRequestPayment = new UserRequestPayment([
            'request_id'    =>  $Trip->id,
            'user_id'       =>  $Trip->user_id,
            'provider_id'   =>  $Trip->provider_id,
            'promocode_id'  =>  $Trip->promocode_id,
            'payment_mode'  =>  $Trip->payment_mode,
            'distance'      =>  $Trip->distance,
            'surge'         =>  $Trip->surge,
        ]);
        $UserRequestPayment->save();
        $Provider = Provider::where('id',$Trip->provider_id)->first();
        $to = [$Provider->social_unique_id];
        $title = 'Payment'; 
        $body = 'User Payment ACCEPTED';
        $this->sendFCM($to,$title,$body);
        return $this->return([]);
    }
    public function complained(Request $req)
    {
        $req->validate([
            'user_id'          => 'required|exists:users,id', 
            'provider_id'      => 'required|exists:providers,id',
            'complaint'        => 'required|string|max:100'
        ]);
        $user_id       = $req->input('user_id');
        $provider_id   = $req->input('provider_id');
        $complaint     = $req->input('complaint');
        $Complaint = new Complaint([
            'user_id'        => $user_id,
            'provider_id'    => $provider_id,
            'complaint'      => $complaint,
            'sender'         => 'USER'
        ]);
        $Complaint->save();
        return $this->return([
            'id'    => $Complaint->id
        ]);
    }
    public function reply_complained(Request $req)
    {
        $req->validate([
            'id'           => 'required|exists:complaints', 
            'reply'        => 'required|string|max:100'
        ]);
        $id       = $req->input('id');
        $reply    = $req->input('reply');
        $Update = Complaint::where('id',$id)->update([
            'reply'    =>  $reply
        ]);
        if($Update)
            return $this->return([]);
    }
    /*            Start Notification & Payment          */
    public function sendFCM2(Request $req)
    {
        $to      = [$req->input('to')]; 
        $title   = $req->input('title');
        $body    = $req->input('body');
        //Push to array Token and send 

        $push = PushNotification::setService('fcm')
                        ->setMessage([
                             'notification' => [
                                     'title'=>$title,
                                     'body'=>$body,
                                     'sound' => 'default'
                                     ],
                             'data' => [
                                     'extraPayLoad1' => 'value1'
                                     ]
                             ])
                        ->setApiKey(env('FCM_SERVER_KEY'))
                        ->setDevicesToken($to)//Array
                        ->send()
                        ->getFeedback();
        return [$push];                             
    }
    public function paytabs_payment(Request $req)
    {
        $pt = Paytabs::getInstance("MERCHANT_EMAIL", "SECRET_KEY");
    $result = $pt->create_pay_page(array(
        "merchant_email" => "MERCHANT_EMAIL",
        'secret_key' => "SECRET_KEY",
        'title' => "John Doe",
        'cc_first_name' => "John",
        'cc_last_name' => "Doe",
        'email' => "customer@email.com",
        'cc_phone_number' => "973",
        'phone_number' => "33333333",
        'billing_address' => "Juffair, Manama, Bahrain",
        'city' => "Manama",
        'state' => "Capital",
        'postal_code' => "97300",
        'country' => "BHR",
        'address_shipping' => "Juffair, Manama, Bahrain",
        'city_shipping' => "Manama",
        'state_shipping' => "Capital",
        'postal_code_shipping' => "97300",
        'country_shipping' => "BHR",
        "products_per_title"=> "Mobile Phone",
        'currency' => "BHD",
        "unit_price"=> "10",
        'quantity' => "1",
        'other_charges' => "0",
        'amount' => "10.00",
        'discount'=>"0",
        "msg_lang" => "english",
        "reference_no" => "1231231",
        "site_url" => "https://your-site.com",
        'return_url' => "https://www.mystore.com/paytabs_api/result.php",
        "cms_with_version" => "API USING PHP"
    ));
    
        if($result->response_code == 4012){
        return redirect($result->payment_url);
        }
        return [$result->result];
    }
    public function paytabs_response(Request $req)
    {
        $pt = Paytabs::getInstance("MERCHANT_EMAIL", "SECRET_KEY");
        $result = $pt->verify_payment($request->payment_reference);
        if($result->response_code == 100){
        // Payment Success
        }   
        return [$result->result];
    }
    /*            End Notification & Payment          */
}
