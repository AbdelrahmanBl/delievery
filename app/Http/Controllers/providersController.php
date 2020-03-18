<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User_Devices;
use App\Provider;
use App\ProviderDocument;
use App\UserWallet;
use App\ProviderWallet;
use App\UserRequest;
use App\UserRequestPayment;
use App\UserRequestRating;
use App\User;
use App\serviceType;
use App\Complaint;
use App\Fleet;
use App\Promocode;
use App\Markerter;
use App\Calculation;
use Cookie;
use DB;
use Exception;
use Auth;
use File;
use PushNotification;

class providersController extends Controller
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
    public function loginUsingId($id)
    {
        $statue = false;
        while($statue != true){
            $token = str_random(64);
            if(!DB::table('providers')->where('remember_token',$token)->first())
                $statue = true;
        }
        $User = DB::table('providers')->where('id',$id);
        $User->update([
            'last_login'       => date("Y-m-d H:i:s"),
            'remember_token'   => $token,
        ]); 
        return $User->first();
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
        $UserData = DB::table('providers')->where('mobile',$User->mobile)->first();
        $this->invalidOTP($where);
        if($UserData){
            $User = $this->loginUsingId($UserData->id);
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
            'first_name'    => 'required|string|max:15',
            'last_name'     => 'required|string|max:15',
            'email'         => 'email|unique:providers',
            'gender'        => 'required|in:M,F,U',
            'mobile'        => 'required|numeric',
            'device_type'   => 'required|in:android,ios,Web',
            'email'         => 'required|email|max:64',
            'password'      => 'required'
        ]);

        $first_name      = $req->input('first_name');
        $last_name       = $req->input('last_name');
        $email           = $req->input('email'); //optional
        $password        = $req->input('password');
        $gender          = $req->input('gender');
        $mobile          = $req->input('mobile');
        $device_type     = $req->input('device_type');
        $UserData = DB::table('providers')->where('mobile',$mobile)->first();
        if($UserData){
        $User = $this->loginUsingId($UserData->id);
        $register = true;
      }
      else{
        $User = new Provider([
            'first_name'      => $first_name ,
            'last_name'       => $last_name ,
            'email'           => $email ,
            'password'        => $password,
            'gender'          => $gender ,
            'mobile'          => $mobile ,
            'device_type'     => $device_type ,     
        ]);
        $User->save();
        $User = $this->loginUsingId($User->id);
        $register = false;
      }
      return $this->return([
            'register'  => $register, 
            'data'      => $User,
        ]);
    }
    public function get_profile(Request $req)
    {
        $user_id = $req->input('provider_id');
        $User  = DB::table('providers')->where('id',$user_id)->first();
        if($User)
        return $this->return([
            'register'  => true, 
            'data'      => $User,
            ]);
        else
            return $this->returnError('Provider Not Found');
    }
    public function update_profile(Request $req)
    {
        $req->validate([
            'first_name'    => 'string|max:15',
            'last_name'     => 'string|max:15',
            'email'         => 'email|unique:providers',
            'gender'        => 'in:M,F,U',
            'mobile'        => 'numeric',
            'device_type'   => 'in:android,ios,Web',
        ]);
        $token = $req->header('remember-token');
        $User = DB::table('providers')->where('remember_token',$token);
        $User->update($req->all());
        return $this->return([
            'register' => true,
            'data'     => $User->first(),
        ]);
    }
    public function get_documents(Request $req)
    {
        $req->validate([
            'provider_id'    => 'required|exists:providers,id'
        ]);
        $provider = $req->input('provider_id');
        $Documents = DB::table('documents')->get(['id','name']);
        $Provider_docs = DB::table('provider_documents')->where('provider_id',$provider)->get();
        if(count($Provider_docs) > 0)
        return $this->return([
            'documents'             => $Documents ,
            'provider_documents'    => $Provider_docs 
        ]);
        else
            return $this->return([
                'documents'             => $Documents,
                'provider_documents'    => NULL
            ]);
    }
    public function update_document($provider_id,$document_id,$image)
    {
        $CHKDoc = DB::table('provider_documents')->where('provider_id',$provider_id)->where('document_id',$document_id);
        ($CHKDoc->first())? $Url = $CHKDoc->first()->url : $Url = NULL ;
        if( $Url != NULL)
            File::delete($Url);
        $imageName =  $document_id . time() .'.'.$image->getClientOriginalExtension();
        $destinationPath = "provider/documents/".$provider_id;
        $url = $destinationPath . '/' . $imageName;
        $image->move($destinationPath, $imageName);
        
        if($CHKDoc->first()){
            $CHKDoc->update([
                'url'      =>  $url, 
            ]);
        }
        else{
            $provider_doc = new ProviderDocument([
                'provider_id'    => $provider_id,
                'document_id'    => $document_id,
                'url'            => $url,
                'unique_id'      => NULL,
                'status'         => 'ASSESSING',
            ]);
            $provider_doc->save();
        }
    }
    public function update_documents(Request $req)
    {
        $req->validate([
            'provider_id'    => 'required|exists:providers,id',
            'images.*'          => 'required|image|mimes:jpeg,png,jpg|max:2000'

        ]);
        $provider_id    = $req->input('provider_id');
        $images         = $req->file('images');
        
        foreach ( $images  as $document_id => $image) {
            if(!DB::table('documents')->where('id',$document_id)->first())
                return $this->returnError('document_id : '.$document_id.' not found');
            $this->update_document($provider_id,$document_id,$image);
        }
        $Provider = DB::table('providers')->where('id',$provider_id)->first();
        return $this->return([
            'status'  => $Provider->status
        ]);
    }
    public function calculations($request_id,$user_id,$provider_id,$fleet_id,$promocode_id,$service_type_id,$travel_time,$distance)
    {
        $Calculation = new Calculation();    
        $serviceTypeTable = serviceType::where(array(
            'id'      => $service_type_id ,
            'status'  => 1
        ))->first();
        $Calculation->fixed = (float)$serviceTypeTable->fixed;

        $FleetTable     = Fleet::where('id',$fleet_id)->first();
        $PromocodeTable = Promocode::where('id',$promocode_id)->first();
        $Calculation->price = $Calculation->fixed + ( $serviceTypeTable->distance * $travel_time ) + ( $serviceTypeTable->distance * $distance ) ;
        
        $Calculation->commision_per = 20; //Fixed Value
        $Calculation->commision  = ( $Calculation->price * $Calculation->commision_per ) / 100 ;
        $Calculation->fleet_per =  ($FleetTable)? $FleetTable->commision : 0 ;
        $Calculation->fleet  = $Calculation->price * $Calculation->fleet_per ;
        $Calculation->discount_per =  $PromocodeTable->percentage;
        $Calculation->discount  = ( $Calculation->price * $Calculation->discount_per ) / 100 ;
        $Calculation->tax_per = 5 ;//Fixed Value
        $Calculation->tax = ( $Calculation->price * $Calculation->tax_per ) / 100;
        $Calculation->govt_static = .5 ;//Fixed Value
        $Calculation->govt_per = 10.00;//Fixed Value
        $Calculation->govt = ( ( $Calculation->price * $Calculation->govt_per ) / 100 ) + $Calculation->govt_static; 
        
        $MarkerterTable = Markerter::where('code',$PromocodeTable->promo_code)->first();
        $Calculation->tax_marketing_per = ($MarkerterTable)? $MarkerterTable->commission_per : 0 ;
        $Calculation->tax_marketing_static  = ($MarkerterTable)? $MarkerterTable->commission : 0;
        $Calculation->tax_marketing = ( ( $Calculation->price * $Calculation->tax_marketing_per ) / 100 ) + $Calculation->tax_marketing_static;
        $Calculation->total = $Calculation->price + $Calculation->tax - $Calculation->discount ;
        $Calculation->minutes = (int)($travel_time % 60);
        $Calculation->hours   = (int)($travel_time / 60);
        return $Calculation;
        
    }
    public function update_request(Request $req)
    {
        $req->validate([
            'provider_id'  =>  'required|exists:providers,id',
            'booking_id'   =>  'required|exists:user_requests',
            'status'       =>  'required|in:ACCEPTED,STARTED,ARRIVED,PICKEDUP,DROPPED,COMPLETED',
        ]);
        $booking_id         =  $req->input('booking_id');
        $status             =  $req->input('status');
        $provider_id        =  $req->input('provider_id');
        UserRequest::where('booking_id',$booking_id)->update([
            'status'       =>  $status,
            'provider_id'  =>  $provider_id,
        ]);
        if($status == 'STARTED')
        Provider::where('id',$provider_id)->update([
            'provider_status'     =>  'Busy'
        ]);
        else if($status == 'DROPPED'){
        Provider::where('id',$provider_id)->update([
            'provider_status'     =>  'Online'
           ]);  
        $Trip =  UserRequest::where('booking_id',$booking_id)->first();
        $ProviderTable = Provider::where('id',$provider_id)->first();
        $calculations = $this->calculations($Trip->id,$Trip->user_id,$provider_id,$ProviderTable->fleet,$Trip->promocode_id,$Trip->service_type_id,$Trip->travel_time,$Trip->distance);
        if(!UserRequestPayment::where('request_id',$Trip->id)->first()){
        $UserRequestPayment = new UserRequestPayment([
            'request_id'                => $Trip->id,
            'user_id'                   => $Trip->user_id,
            'provider_id'               => $Trip->provider_id,
            'fleet_id'                  => $ProviderTable->fleet,
            'promocode_id'              => $Trip->promocode_id,
            'payment_id'                => $Trip->provider_id.time(),
            'payment_mode'              => $Trip->payment_mode,
            'minute'                    => $calculations->minutes,
            'hour'                      => $calculations->hours,
            'fixed'                     => $calculations->fixed,
            'distance'                  => $Trip->distance,
            'commision'                 => $calculations->commision,
            'commision_per'             => $calculations->commision_per,
            'fleet'                     => $calculations->fleet,
            'fleet_per'                 => $calculations->fleet_per,
            'discount'                  => $calculations->discount,
            'discount_per'              => $calculations->discount_per,
            'tax'                       => $calculations->tax,
            'tax_per'                   => $calculations->tax_per,
            'govt'                      => $calculations->govt,
            'govt_per'                  => $calculations->govt_per,
            'tax_marketing'             => $calculations->tax_marketing,
            'tax_marketing_tax_per'     => $calculations->tax_marketing_per,
            'total'                     => $calculations->total,
            'surge'                     => $Trip->surge,
        ]);
        $UserRequestPayment->save();
            }  
        }
        //$Trip  = UserRequest::where('booking_id',$booking_id)->first();
        $to    = [DB::table('users')->where('id',$Trip->user_id)->first()->social_unique_id];
        $title = 'ADD Request';
        $body  = $Trip->status;
        $this->sendFCM($to,$title,$body);
        if($status == 'DROPPED')
        return $this->return([
            'invoice'      => $UserRequestPayment
        ]);
        else return $this->return([]);
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
            'cancelled_by'  =>  'PROVIDER',
            'cancel_reason' => $cancel_reason ,
        ]);
        return $this->return([]);
    }
    public function register_device(Request $req)
    {
        $req->validate([
            'user_id'           =>  'required|exists:providers,id',
            'social_unique_id'  =>  'required',
        ]);
        $user_id             = $req->input('user_id');
        $social_unique_id    = $req->input('social_unique_id');

        DB::table('providers')->where('id',$user_id)->update([
            'social_unique_id'  => $social_unique_id
        ]);
        return $this->return([]);
    }
    public function check_status(Request $req)
    {
        $req->validate([
            'provider_id'   =>  'required|exists:providers,id'
        ]);
        $provider_id = $req->input('provider_id');
        $status = ['SEARCHING','ACCEPTED','STARTED','ARRIVED','PICKEDUP','DROPPED'];
        $CHKStatus = UserRequest::where('provider_id',$provider_id)->whereIn('status',$status)->first(); 
        $provider_status = DB::table('providers')->where('id',$provider_id)->first()->status;
        if($CHKStatus){
            $user = User::where('id',$CHKStatus->user_id)->first();
            return $this->return([
                'user'        =>  $user,
                'status'      =>  $provider_status,
                'trip'        =>  $CHKStatus
            ]);
        }    
        else{
            $Status = UserRequest::where('provider_id',0)->where('status','SEARCHING')->first();
            $user = User::where('id',$Status->user_id)->first();
            if($Status)
            {
                return $this->return([
                'user'        =>  $user,    
                'status'      =>  $provider_status,
                'trip'        =>  $Status
            ]);
            }   
        }
            return $this->return([
                'user'        =>  NULL,
                'status'      =>  $provider_status,
                'trip'        =>  NULL
            ]);
    }
    public function history_trips(Request $req)
    {
        $req->validate([
            'provider_id'   =>  'required|exists:user_requests'
        ]);
        $provider_id   = $req->input('provider_id');
        $Trip = DB::table('user_requests')->where('user_requests.provider_id',$provider_id)->where('paid',1)->join('service_types', 'user_requests.service_type_id', '=', 'service_types.id')->join('user_request_payments', 'user_requests.id', '=', 'user_request_payments.request_id')->select('user_requests.status','user_requests.started_at as from','user_requests.finished_at as to','user_requests.created_at as date','user_requests.booking_id','user_request_payments.total as amount','service_types.name as service_type')->get();
        if($Trip)
        return $this->return([
            'data'  =>  $Trip
        ]);
        else
            return $this->returnError('Error cntact Admin');
    }
    public function trip_details(Request $req)
    {
        $req->validate([
            'trip_id'    =>  'required|exists:user_requests,id|exists:user_request_payments,request_id|exists:user_request_ratings,request_id'
        ]);
        $trip_id  = $req->input('trip_id');
        $Trip = DB::table('user_requests')->where('user_requests.id',$trip_id)->join('user_request_payments', 'user_requests.id', '=', 'user_request_payments.request_id')->join('user_request_ratings', 'user_requests.id', '=', 'user_request_ratings.request_id')->join('users', 'user_requests.user_id', '=', 'users.id')->select('user_requests.payment_mode as payment_type','user_requests.distance','user_request_payments.tax','user_request_payments.total','user_request_ratings.user_rating as rate','user_request_ratings.user_comment as comment','users.first_name','users.last_name')->get();
        return $this->return([
            'data'  =>  $Trip
        ]);
    }
    public function earning(Request $req)
    {
        $req->validate([
            'provider_id'   => 'required'
        ]);
        $provider_id   = $req->input('provider_id');
        $earning = DB::table('user_request_payments')->where('provider_id',$provider_id)->get(['request_id','hour','minute','distance','total']);
        if($earning)
        return $this->return([
            'data'   =>  $earning
        ]);
        else
           return $this->return([
            'data'   =>  NULL
        ]); 
    }
    public function wallet(Request $req)
    {
        $req->validate([
            'provider_id'   => 'required|exists:provider_wallets'
        ]);
        $provider_id   = $req->input('provider_id');
        $wallet = DB::table('provider_wallets')->where('provider_id',$provider_id)->get(['transaction_alias as booking_id','amount','created_at as date']);
        return $this->return([
            'data'   =>  $wallet
        ]);
    }
    public function transfer_change(Request $req)
    {
        $req->validate([
            'user_id'       => 'required|exists:users,id',
            'provider_id'   => 'required|exists:providers,id',
            'change'        => 'required|numeric',
            'booking_id'    => 'required|unique:user_requests'
        ]);
        $user_id        = $req->input('user_id');
        $provider_id    = $req->input('provider_id');
        $change         = $req->input('change');
        $booking_id     = $req->input('booking_id');

        $transaction_id = $user_id + time();
        $User = DB::table('user_wallets')->where('user_id',$user_id)->orderBy('id', 'desc')->first();
        if($User){
            $open_balance    =  $User->close_balance;
            $close_balance   =  $open_balance + (double)$change;
        }
        else{
            $open_balance    = 0;
            $close_balance   = $change;
        }
        $UserWallet = new UserWallet([
            'user_id'           =>  $user_id,
            'amount'            =>  $change,
            'transaction_id'    =>  $transaction_id,
            'transaction_alias' =>  $booking_id,
            'open_balance'      =>  $open_balance,
            'close_balance'     =>  $close_balance,
            'type'              =>  'D'

        ]);
        $transaction_id = $provider_id + time();
        $Provider = DB::table('provider_wallets')->where('provider_id',$provider_id)->orderBy('id', 'desc')->first();
        if($Provider){
            $open_balance    =  $Provider->close_balance;
            $close_balance   =  $open_balance - (double)$change;
        }
        else{
            $open_balance    = 0;
            $close_balance   = -$change;
        }
        $ProviderWallet = new ProviderWallet([
            'provider_id'       =>  $provider_id,
            'amount'            =>  -$change,
            'transaction_id'    =>  $transaction_id, 
            'transaction_alias' =>  $booking_id,
            'open_balance'      =>  $open_balance,
            'close_balance'     =>  $close_balance,
            'type'              =>  'C'
        ]);
        $UserWallet->save();
        $ProviderWallet->save();
        return $this->return([]);
    }
    public function change_mobile(Request $req)
    {
        $req->validate([
            'mobile'        => 'required|numeric',
            'provider_id'   => 'required|exists:providers,id',
        ]);
        $mobile         = $req->input('mobile');
        $provider_id    = $req->input('provider_id');
        DB::table('providers')->where('id',$provider_id)->update([
            'mobile'    =>  $mobile
        ]);
        $User = DB::table('providers')->where('id',$provider_id)->first();
        return $this->return([
            'data'   =>  $User
        ]);
    }
    public function check_email(Request $req)
    {
        $req->validate([
            'email'        => 'required|email|unique:providers',
        ]);
        return $this->return([]);
    }
    public function update_status(Request $req)
    {
        $req->validate([
            'provider_id'   => 'required|exists:providers,id',
            'status'        => 'required|in:Login,Logout'
        ]);
        $provider_id      = $req->input('provider_id');
        $status           = $req->input('status');
        DB::table('providers')->where('id',$provider_id)->update([
            'status'    =>  $status
        ]);
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
            'sender'         => 'PROVIDER'
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
    public function send_payment(Request $req)
    {
        $req->validate([
            'provider_id'     =>  'required|exists:providers,id',
            'trip_id'         =>  'required|exists:user_requests,id|unique:user_request_payments,request_id',
            'payment_mode'    =>  'required|in:CASH',
            //'status'          =>  'in:DROPPED'
        ]);
        $provider_id     = $req->input('provider_id');
        $trip_id         = $req->input('trip_id');
        $payment_mode    = $req->input('payment_mode');
        DB::table('user_requests')->where('id',$trip_id)->update([
            'payment_mode'   =>  $payment_mode,
            'paid'           =>  1 ,
        ]);
        return $this->return([]);
    }
    
    /*public function rate_trip(Request $req)
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
    public function transfer_money(Request $req)
    {
        $req->validate([
            'mobile'         =>  'required|numeric|exists:providers',
        ]);
        $mobile  = $req->input('mobile');
    }
    public function verify_transfer(Request $req)
    {
        $req->validate([
            'mobile'         =>  'required|numeric|exists:providers',
            'code'           =>  'required|numeric',
            'amount'         =>  'required|numeric',
        ]);
        $mobile  = $req->input('mobile');
        $code    = $req->input('code');
        $amount  = $req->input('amount');
    }*/
}
