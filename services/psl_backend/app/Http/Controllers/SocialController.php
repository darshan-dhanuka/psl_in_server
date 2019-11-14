<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;

class SocialController extends Controller
{
    //
    public function socialogin(Request $request)
    {
        //$credentials = $request->json()->all();
        $email = $request->json()->get('email');
        $name = $request->json()->get('name');
        $token = $request->json()->get('id');
        //var_dump($credentials);
       // $check = DB::select('select id from users where email = :email_id', ['email_id' => $email]);
        $check = DB::select('select id from users where email = ?', [$email]);
        $count = count($check);
        if($count > 0)
        {
            //dd($check);
            return $check;
        }
        else
        {
			$sel = DB::select('select max(id) as id from users');
			$created_datetime = date("Y-m-d H:i:s");
            $referral_code =  strtolower(substr($name,0,4)).trim(($sel[0]->id) + 1);
            $uname =  "g_".trim(($sel[0]->id) + 1);
			$insert = DB::insert('insert into users (email, name,google_token,uname,referral_code,created_at,updated_at ) VALUES (?, ?,?,?,?,?,?)', [$email, $name,$token,$uname,$referral_code,$created_datetime,$created_datetime]); 
            return $check;
        }
        
    } 
}
