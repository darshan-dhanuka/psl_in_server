<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
//use JWTAuth;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Facades\JWTFactory;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Tymon\JWTAuth\PayloadFactory;
use Tymon\JWTAuth\JWTManager as JWT;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class UserController extends Controller
{   

    public function register(Request $request)
    {
        //dd($request);
        //die;
		$sel = DB::select("SELECT phone FROM users where email=? AND (phone != null OR phone != '') AND campaign_flag = 0",[$request->json()->get('email')]);

		if(count($sel) > 0)
		{
			$validator = Validator::make($request->json()->all() , [
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6', 
            'uname' => 'required|unique:users', 
			]);
		}
		else
		{
			$validator = Validator::make($request->json()->all() , [   
            'password' => 'required|string|min:6', 
			'uname' => 'required|unique:users', 
			]);
		}

		if($validator->fails()){
                return response()->json($validator->errors(), 422 );
			}
        //$user = User::create([
		$user = User::updateOrCreate(['email' => $request->json()->get('email')],[
            'name' => $request->json()->get('name'),
            'password' => Hash::make($request->json()->get('password')),
            'state' => $request->json()->get('state'),
            'city' => $request->json()->get('city'),
            'address' => $request->json()->get('address'),
            'dob' =>date('Y-m-d', strtotime($request->json()->get('dob'))),
            'phone' => $request->json()->get('phone'),
            'uname' => $request->json()->get('uname'),
            'referred_by' => $request->json()->get('referral_code'),
            
        ]);

        $token = JWTAuth::fromUser($user);

        return response()->json(compact('user','token'),201);
    }
    
    public function login(Request $request)
    {
        $credentials = $request->json()->all();
		
		//print_r($credentials);
		
		$loginField = $request->json()->all()['email'];
		$credentials = null;
		$name = "";
		if ($loginField !== null) {
			$loginType = filter_var($loginField, FILTER_VALIDATE_EMAIL) ? 'email' : 'uname';
			//print_r($loginType);
			request()->merge([ $loginType => $loginField ]);

			$credentials = request([ $loginType, 'password' ]);
		} 
		else {
			return $this->response->errorBadRequest('What do you think you\'re doing?');
		}
		//var_dump($credentials);

		$sel = DB::select("SELECT phone FROM users where ".$loginType."=? AND (phone != null OR phone != '')",[$loginField]);

		if(count($sel) > 0)
		{
			try {
            if (! $token = JWTAuth::attempt($credentials)) {
                return response()->json(['error' => 'invalid_credentials'], 400);
            }
			} catch (JWTException $e) {
				return response()->json(['error' => 'could_not_create_token'], 500);
			}
			$currentUser = Auth::user();
			$name = $currentUser->name;
			$uname = $currentUser->uname;
			
			
		}
		else
		{
			 return response()->json(['error' => 'invalid_credentials'], 400);
		}
		
        //print_r(compact('token','resp'));exit;
		return response()->json( compact('token','name','uname') );
    }

    

    public function getAuthenticatedUser()
    {
        try {
            if (! $user = JWTAuth::parseToken()->authenticate()) {
                return response()->json(['user_not_found'], 404);
            }
        } catch (Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
            return response()->json(['token_expired'], $e->getStatusCode());
        } catch (Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {
            return response()->json(['token_invalid'], $e->getStatusCode());
        } catch (Tymon\JWTAuth\Exceptions\JWTException $e) {
            return response()->json(['token_absent'], $e->getStatusCode());
        }
        return response()->json(compact('user'));
    }

    public function forgetpw(Request $request)
    {
        error_reporting(E_ALL ^ E_NOTICE);
        $resp = array();
        $credentials = $request->json()->all();
        $mobile_number = $credentials['phone'];
        //dd($mobile_number);
        $apiKey = urlencode('hMkQfydUC6M-JRvPew5uwgT75vdyitJKmfztDmvSgN');

        $sel_qry = DB::select('SELECT * FROM users WHERE phone  = ? ', [$mobile_number]);
        $num_rows = count($sel_qry);

        if($num_rows > 0)
        {
            // $resp['errorcode'] = 0;
             // Message details
        $otp = rand(100000,999999);
        //$otp = 111111;
        //$numbers = array(919773486995);
        $sender = urlencode('PSLWEB');
        $message = rawurlencode('This is your otp - '.$otp.' .Please put this to verify');

        $numbers = $mobile_number;

        // Prepare data for POST request
        $data = array('apikey' => $apiKey, 'numbers' => $numbers, "sender" => $sender, "message" => $message);

        // Send the POST request with cURL
       $ch = curl_init('https://api.textlocal.in/send/');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);
        $content_mod = json_decode($response,true);

        if(trim($content_mod['status']) == 'success'){
            $ins_qry = DB::insert('insert into tbl_otp_verify  (number,otp) values (?, ?)', [$numbers, $otp]);
            if($ins_qry){
                $resp['errorcode'] = 0;
                $resp['msg'] = 'Message sent successfully';
            }else{
                $resp['errorcode'] = 1;
                $resp['msg'] = 'Message failed';
            }
        }else{
            $resp['errorcode'] = 1;
            $resp['msg'] = 'Message failed';
        }
        return json_encode($resp);

         }
         else{
             $resp['errorcode'] = 2;
             $resp['msg'] = 'Mobile_number does not exist!!';
             echo json_encode($resp);die;
         }

        
    }


    public function verify_otp(Request $request)
     {
         $credentials = $request->json()->all();
         $mobile_number = $credentials['phone1'];
         $otp = $credentials['otp_text'];
         $otp_db = "";
         $sel_qry = DB::select('SELECT otp FROM  tbl_otp_verify WHERE  
                        number = ? ORDER BY id DESC limit 1', [$mobile_number] );
         //dd($sel_qry);
         if($sel_qry)
             $otp_db = $sel_qry[0]->otp;

         //$num_row = count($sel_qry);
         if($otp_db == $otp){
             $resp['errorcode'] = 0;
             $resp['msg'] = 'Otp Valid';
         }else{
             $resp['errorcode'] = 1;
             $resp['msg'] = 'Invalid Otp';
         }

         return json_encode($resp);
     }

     public function reset_password(Request $request)
     {
         $credentials = $request->json()->all();
         $password = Hash::make($credentials['password']);
         $mobile_num = $credentials['phone1'];

         $sel_qry = DB::update('UPDATE users SET password = ?,password_updated_at = NOW() WHERE phone = ?', [$password,$mobile_num]);
         //dd($sel_qry);
         if($sel_qry){
             $resp['errorcode'] = 0;
             $resp['msg'] = 'Otp Valid';
         }else{
             $resp['errorcode'] = 1;
             $resp['msg'] = 'Invalid Otp';
         }

         echo json_encode($resp);
     }


	public function send_otp(Request $request)
    {
        error_reporting(E_ALL ^ E_NOTICE);
        $resp = array();
        $credentials = $request->json()->all();
        $mobile_number = $credentials[0];
        //dd($mobile_number);
		//exit;
        $apiKey = urlencode('hMkQfydUC6M-JRvPew5uwgT75vdyitJKmfztDmvSgN');
            // $resp['errorcode'] = 0;
             // Message details
        $otp = rand(100000,999999);
        //$otp = 111111;
        //$numbers = array(919773486995);
        $sender = urlencode('PSLWEB');
        $message = rawurlencode('This is your otp - '.$otp.' .Please put this to verify');

        $numbers = $mobile_number;

        // Prepare data for POST request
        $data = array('apikey' => $apiKey, 'numbers' => $numbers, "sender" => $sender, "message" => $message);

        // Send the POST request with cURL
        $ch = curl_init('https://api.textlocal.in/send/');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);
        $content_mod = json_decode($response,true);

        if(trim($content_mod['status']) == 'success'){
            $ins_qry = DB::insert('insert into tbl_otp_verify  (number,otp) values (?, ?)', [$numbers, $otp]);
            if($ins_qry){
                $resp['errorcode'] = 0;
                $resp['msg'] = 'Message sent successfully';
            }else{
                $resp['errorcode'] = 1;
                $resp['msg'] = 'Message failed';
            }
        }else{
            $resp['errorcode'] = 1;
            $resp['msg'] = 'Message failed';
        }
        return json_encode($resp);
        
    }


	public function psl_register(Request $request)
    {
        DB::enableQueryLog();
		$resp = array();
        $credentials = $request->json()->all();
        $name = $credentials['name'];
        $phone = $credentials['phone'];
        $referred_by = $credentials['referral_code'];
        $email = $credentials['email'];
        $sel = DB::select("SELECT id FROM users where email=?",[$email]);
        $referral_code =  strtolower(substr($name,0,4)).trim(($sel[0]->id));
		$upd_qry = DB::update('UPDATE users SET phone = ?,name=?,referred_by=?,referral_code=?  WHERE email = ?', [$phone,$name,$referred_by,$referral_code,$email]);
		/* $query = DB::getQueryLog();
		dd($upd_qry);
		exit; */
		if($upd_qry){
                $resp['errorcode'] = 0;
                $resp['msg'] = 'Registered successfully';
            }else{
                $resp['errorcode'] = 1;
                $resp['msg'] = 'Registered failed';
            }
       
        return json_encode($resp);
    }
    
	public function e_awsses(Request $request)
	{
		// Replace sender@example.com with your "From" address.
		// This address must be verified with Amazon SES.
		$sender = 'darshan.dhanukaa@gmail.com';
		$senderName = 'Sender Name';

		// Replace recipient@example.com with a "To" address. If your account
		// is still in the sandbox, this address must be verified.
		$recipient = 'sweta.bhandari1@gmail.com';

		// Replace smtp_username with your Amazon SES SMTP user name.
		$usernameSmtp = 'AKIARZNTZEVKSQHACKJP';

		// Replace smtp_password with your Amazon SES SMTP password.
		$passwordSmtp = 'BKgD1VqVjfO3cdZpSDfJVq2dR1lVRTFUdpjuqFb4M+Bz';

		// Specify a configuration set. If you do not want to use a configuration
		// set, comment or remove the next line.
		//$configurationSet = 'ConfigSet';

		// If you're using Amazon SES in a region other than US West (Oregon),
		// replace email-smtp.us-west-2.amazonaws.com with the Amazon SES SMTP
		// endpoint in the appropriate region.
		$host = 'email-smtp.us-east-1.amazonaws.com';
		$port = 587;

		// The subject line of the email
		$subject = 'Amazon SES test (SMTP interface accessed using PHP)';

		// The plain-text body of the email
		$bodyText =  "Email Test\r\nThis email was sent through the
			Amazon SES SMTP interface using the PHPMailer class.";

		// The HTML-formatted body of the email
		$bodyHtml = '<h1>Email Test</h1>
			<p>This email was sent through the
			<a href="https://aws.amazon.com/ses">Amazon SES</a> SMTP
			interface using the <a href="https://github.com/PHPMailer/PHPMailer">
			PHPMailer</a> class.</p>';

		$mail = new PHPMailer(true);

		try {
			// Specify the SMTP settings.
			$mail->isSMTP();
			$mail->setFrom($sender, $senderName);
			$mail->Username   = $usernameSmtp;
			$mail->Password   = $passwordSmtp;
			$mail->Host       = $host;
			$mail->Port       = $port;
			$mail->SMTPAuth   = true;
			$mail->SMTPSecure = 'tls';
			//$mail->addCustomHeader('X-SES-CONFIGURATION-SET', $configurationSet);

			// Specify the message recipients.
			$mail->addAddress($recipient);
			// You can also add CC, BCC, and additional To recipients here.

			// Specify the content of the message.
			$mail->isHTML(true);
			$mail->Subject    = $subject;
			$mail->Body       = $bodyHtml;
			$mail->AltBody    = $bodyText;
			$mail->Send();
			echo "Email sent!" , PHP_EOL;
		} catch (phpmailerException $e) {
			echo "An error occurred. {$e->errorMessage()}", PHP_EOL; //Catch errors from PHPMailer.
		} catch (Exception $e) {
			echo "Email not sent. {$mail->ErrorInfo}", PHP_EOL; //Catch errors from Amazon SES.
		}

		
	}
    
   /* public function update_password_once(Request $request)
    {
        $credentials = $request->json()->all();
        $password = Hash::make('Psl@1234');
       // $mobile_num = $credentials['mobile_num'];

        $sel_qry = DB::update('UPDATE users SET password = ?', [$password]);
        //dd($sel_qry);
        if($sel_qry){
            $resp['errorcode'] = 0;
            $resp['msg'] = 'Updated';
        }else{
            $resp['errorcode'] = 1;
            $resp['msg'] = 'Failed';
        }

        echo json_encode($resp);
    }*/

    public function user_details(Request $request)
    {
        //DB::enableQueryLog();
		$credentials = $request->json()->all();
        //dd($credentials);
        $email = $credentials['email'];

		if ($email !== null) 
			$variable = filter_var($email, FILTER_VALIDATE_EMAIL) ? 'email' : 'uname';

        $sel_qry = DB::select('SELECT * FROM  users  WHERE '.$variable.' = ?',[$email]);
		//$query = DB::getQueryLog();
		//dd(DB::getQueryLog());
		//dd($sel_qry);
		
        if($sel_qry){
            $resp['data'] = $sel_qry;
            $resp['errorcode'] = 0;
            $resp['msg'] = 'Success';
        }else{
            $resp['errorcode'] = 1;
            $resp['msg'] = 'No Data found';
        }

        echo json_encode($resp);
    }

    public function change_password_user(Request $request)
     {
        $credentials = $request->json()->all();
        $old_password = $credentials['password'];
        //echo "<br>".$credentials['password'];
        //echo "<br>".$credentials['new_password'];
        $password = Hash::make($credentials['new_password']);
        $email = $credentials['email'];
		
		$user = User::where('email', '=', $email)->first();

         //$chck_old_pw = DB::select('SELECT id FROM users WHERE password = ? AND email = ?', [$old_password,$email]);
         //dd($chck_old_pw);
         //if(count($chck_old_pw) > 0){
         if(Hash::check($old_password, $user->password)){

             $sel_qry = DB::update('UPDATE users SET password = ?,password_updated_at = NOW() WHERE email = ?', [$password,$email]);

             if($sel_qry){
                 $resp['errorcode'] = 0;
                 $resp['msg'] = 'Password Updated';
             }else{
                 $resp['errorcode'] = 1;
                 $resp['msg'] = 'Something went wrong. Please try again later';
             }
         }else{
             $resp['errorcode'] = 2;
             $resp['msg'] = 'Please put correct old password';
         }

         echo json_encode($resp);
     }

public function pro_player_registration(Request $request)
     {
		ini_set("memory_limit",'2048M');
		ini_set("max_execution_time",0);
		
         $credentials = $request->all();
		
        // print_r($credentials);
     
         $user_id = $credentials['user_email'];
         if($user_id == '' || $user_id == null){
             $resp['errorcode'] = 1;
             $resp['msg'] = 'No Email found';
         }

		if ($user_id !== null) 
			 $variable = filter_var($user_id, FILTER_VALIDATE_EMAIL) ? 'email' : 'uname';
		 
         $sel_details  = DB::select("SELECT * FROM users WHERE ".$variable." = ?",[$user_id]);

         //print_r($sel_details);
        
         if(count($sel_details) > 0){

             $sel_qry = DB::insert('INSERT INTO tbl_pro_player_reg (name,email_id,phone,user_id) VALUES (?,?,?,?)',[$sel_details[0]->name,$user_id,$sel_details[0]->phone,$sel_details[0]->id]);

             if($sel_qry){
                 $ins_str = '';
                 for($i = 0;$i < 10;$i++){
				    $file = $request->file('form_details_'.$i);
                    $destinationPath="../../uploads/".$sel_details[0]->id."_".$i."/";
                    if(!is_dir($destinationPath)){
                        mkdir($destinationPath,0755,true);
                   }
                    $file->move($destinationPath,$file->getClientOriginalName());
                     $ins_str .="('".$sel_details[0]->id."','".$credentials['name_'.$i]."','".$credentials['type_'.$i]."','".$credentials['buyin_'.$i]."','".$credentials['entries_'.$i]."','".$credentials['position_'.$i]."','".$destinationPath."".$file->getClientOriginalName()."'),";

                 }
                 $ins_str = rtrim($ins_str,',');
                 $ins_qry = DB::insert('INSERT INTO tbl_pro_player_details(reference_id,tourney_name,tourney_type,tourney_buyin,total_entries,final_position,file_path)VALUES '.$ins_str);
                
                 if($ins_qry){
                     $sel_qry = DB::update('UPDATE users SET pro_reg = 1 WHERE id = ?', [$sel_details[0]->id]);
                     $resp['errorcode'] = 0;
                     $resp['msg'] = 'Success';
                 }else{
                     $resp['errorcode'] = 1;
                     $resp['msg'] = 'Something went wrong.Please try later . ';
                 }
             }else{
                 $resp['errorcode'] = 1;
                 $resp['msg'] = 'Something went wrong.Please try later . ';
             }
         }else{
             $resp['errorcode'] = 1;
             $resp['msg'] = 'No Data Found.Please try later . ';
         }
         echo json_encode($resp);
     }

     public function update_adda_id(Request $request)
     {
         $credentials = $request->json()->all();
         $adda_id = $credentials['adda_id'];
         $email  = $credentials['email'];
		 $uname  = $credentials['email'];								 
           
         $sel_qry = DB::update('UPDATE users SET adda_id = ?,adda_updated_at = NOW() WHERE (email =? OR uname= ?)', [$adda_id,$email,$uname]);

         if($sel_qry){
            $up_sel_qry = DB::update('UPDATE users SET qualifier_apply = 1 WHERE (email =? OR uname= ?)', [$email,$uname]);
             $resp['errorcode'] = 0;
             $resp['msg'] = 'Updated Successfully';
         }else{
             $resp['errorcode'] = 1;
             $resp['msg'] = 'Something went wrong . Please try later . ';
         }

         echo json_encode($resp);
     }
	 
	 
	  public function update_adda_redirect(Request $request)
      {
          $credentials = $request->json()->all();
          //print_r($credentials);die;
          $email  = $credentials['email'];
		  $uname  = $credentials['email'];								  
         
          $sel_qry = DB::update('UPDATE users SET adda_redirect_flag =1,adda_updated_at = NOW() WHERE (email =? OR uname= ?)', [$email,$uname]);
          if($sel_qry){
              $resp['errorcode'] = 0;
              $resp['msg'] = 'Updated Successfully';
          }else{
              $resp['errorcode'] = 1;
              $resp['msg'] = 'Something went wrong . Please try later . ';
          }

          echo json_encode($resp);
      }
	  
	  public function psl_register_landing(Request $request)
     {
		 $credentials = $request->json()->all();
		 $password_txt = "Psl@12345";
         $sel = DB::select("SELECT phone FROM users where email=? AND (phone != null OR phone != '')",[$request->json()->get('email')]);

         if(count($sel) > 0)
         {
             $validator = Validator::make($request->json()->all() , [
             'uname' => 'required|unique:users',
			 'phone' => 'required|unique:users', 
             ]);
         }
         else
         {
             $validator = Validator::make($request->json()->all() , [
             'uname' => 'required|unique:users',
			 'phone' => 'required|unique:users', 
             ]);
         }

         if($validator->fails()){
                 return response()->json($validator->errors(), 422 );
             }
         //$user = User::create([
         $user = User::updateOrCreate(['email' => $request->json()->get('email')],[
             'name' => $request->json()->get('name'),
             'password' => Hash::make($password_txt),
             'state' => $request->json()->get('state'),
             'city' => $request->json()->get('city'),
             'address' => $request->json()->get('address'),
             'dob' =>date('Y-m-d', strtotime($request->json()->get('dob'))),
             'phone' => $request->json()->get('phone'),
             'uname' => $request->json()->get('uname'),
             'referred_by' => $request->json()->get('referral_code'),
             'utm_source' => $request->json()->get('utm_source'),
             'utm_medium' => $request->json()->get('utm_medium'),
             'utm_campaign' => $request->json()->get('utm_campaign'),
             'campaign_flag' => 1,

         ]);

         $token = JWTAuth::fromUser($user);
		//print_r($user->id);die;
		 //$sel = DB::update("UPDATE users SET  where email=? AND (phone != null OR phone != '')",[$request->json()->get('email')]);
         return response()->json(compact('user','token'),201);
     }
     public function psl_register_app(Request $request)
     {
		 $credentials = $request->json()->all();
         $sel = DB::select("SELECT phone FROM users where email=? AND (phone != null OR phone != '')",[$request->json()->get('email')]);

         if(count($sel) > 0)
         {
             $validator = Validator::make($request->json()->all() , [
             'uname' => 'required|unique:users',
             ]);
         }
         else
         {
             $validator = Validator::make($request->json()->all() , [
             'uname' => 'required|unique:users',
             ]);
         }

         if($validator->fails()){
                 return response()->json($validator->errors(), 422 );
             }
         //$user = User::create([
         $user = User::updateOrCreate(['email' => $request->json()->get('email')],[
             'name' => $request->json()->get('name'),
             'password' => Hash::make($request->json()->get('password')),
             'state' => $request->json()->get('state'),
             'city' => $request->json()->get('city'),
             'address' => $request->json()->get('address'),
             'dob' =>date('Y-m-d', strtotime($request->json()->get('dob'))),
             'phone' => $request->json()->get('phone'),
             'uname' => $request->json()->get('uname'),
             'referred_by' => $request->json()->get('referral_code'),
             'utm_source' => $request->json()->get('utm_source'),
             'utm_medium' => $request->json()->get('utm_medium'),
             'utm_campaign' => $request->json()->get('utm_campaign'),
             'campaign_flag' => 1,

         ]);

         $token = JWTAuth::fromUser($user);
		
         return response()->json(compact('user','token'),201);
     }
}
