import { Component, OnInit } from '@angular/core';
import { Router,ActivatedRoute } from "@angular/router";
import { PlatformLocation } from '@angular/common';
import { AuthenticationService, UserDetails, TokenPayload } from '../authentication.service';


const newLocal = 'block';
@Component({
  selector: 'app-landing-page',
  templateUrl: './landing-page.component.html',
  styleUrls: ['./landing-page.component.css']
})
export class LandingPageComponent implements OnInit {
  htmlStr:string = "";
  sendoptStr:string;
  validotpStr:string;
  email:string;
  password:string;
  param1: string;
  param2: string;
  param3: string;
  public show_terms : boolean = false;
  public show_dialog : boolean = false;
  public show_reg : boolean = false;
  public button_name : any = 'Show Login Form!';
  errorcode: any;
  private form: any;
  credentials: TokenPayload = {
    id: 0,
    uname:"",
    name: "",
    email: "",
    password: "",
    confirm_password: "",
    state: 0,
    city: 0,
    phone: "",
    dob: "",
    address:"",
    terms: false,
    referral_code: ""
  };
  details: UserDetails;
  constructor(public auth: AuthenticationService, private router: Router, location: PlatformLocation,private route: ActivatedRoute) {
    location.onPopState(() => {
       this.load();
   });
   this.route.queryParams.subscribe(params => {
    this.param1 = params['utm_source'];
    this.param2 = params['utm_medium'];
    this.param3 = params['utm_campaign'];
});
 }
  ngOnInit() {
    
  }
load()
{
  window.location.reload();
}

register() {
  //let tempArr : any = [];
  //console.log("heeeeee===="+this.credentials);
  this.auth.register(this.credentials).subscribe(
    () => {
      document.getElementById('reg_modal').style.display = 'none';
      document.getElementById('otp_modal').style.display = 'block';
      //this.router.navigateByUrl('#home')
      this.email = this.credentials['email'];
      this.password = this.credentials['password'];
      this.htmlStr = "";
    },
    err => {
      //console.log(err.error);
      if(err.error.uname)
      {
        this.htmlStr = 'This username has already been taken.';
      }
      else if(err.error.email)
      {
       
       // this.htmlStr = err.error.email[0];
        document.getElementById('email_err').style.display = 'block';
        //document.getElementById('otp_modal').style.display = 'block';
      }
      window.scroll(0,0);
      //console.log('oops', error) 
    }
  );
}




showterms() {
  this.show_terms = !this.show_terms;
  document.getElementById('terms_div').style.display = 'block';
  this.show_terms = !this.show_terms;
}

toggle() {
  document.getElementById('divshow2').style.display = 'block';
}

togglereg() {
  document.getElementById('divreg').style.display = 'block';
}

regstatic() {
  window.scrollTo(0, 0);
}


keyPress(event: any) {
  const pattern = /[0-9]/;

  let inputChar = String.fromCharCode(event.charCode);
  if (event.keyCode != 8 && !pattern.test(inputChar)) {
    event.preventDefault();
  }
}

keyPressspace(event: any) {
 
  let inputChar = String.fromCharCode(event.charCode);
  if ((event.keyCode >= 32 && event.keyCode <= 44) || event.keyCode == 46 || event.keyCode == 47 || (event.keyCode >= 58 && event.keyCode <= 64) || (event.keyCode >= 91 && event.keyCode <= 94) || event.keyCode == 96 || (event.keyCode >= 123 && event.keyCode <= 126)) {
    event.preventDefault();
  }
}

sendotp_func(phone) {
  //console.log(phone.value);
  if(phone.value == "")
  {
      document.getElementById('otp_sent_error').style.display = 'block';
      this.sendoptStr = 'Phone is required.';
  }
  else
  {
    document.getElementById('loader_show').style.display = 'block';
    this.form = phone.value;
    // console.log(this.form);
      this.auth.sendotp(this.form).subscribe(
      result => {
        
        this.errorcode = result['errorcode'];
        if(this.errorcode == 0)
        {
          //success
          document.getElementById('loader_show').style.display = 'none';
          document.getElementById('otp_sent_span').style.display = 'block';
          document.getElementById('otp_sent_error').style.display = 'none';
          this.sendoptStr = 'OTP has been sent to your mobile.';
    
        }
        else 
        {
          //failure
          document.getElementById('loader_show').style.display = 'none';
          document.getElementById('otp_sent_span').style.display = 'none';
          document.getElementById('otp_sent_error').style.display = 'block';
          this.sendoptStr = 'Oops..!! Something went wrong. Please check mobile number or try again.';
        }
        
        
      },
      err => {
        document.getElementById('loader_show').style.display = 'none';
          document.getElementById('otp_sent_span').style.display = 'none';
          document.getElementById('otp_sent_error').style.display = 'block';
          this.sendoptStr = 'Oops..!! Something went wrong. Please try again and check mobile number.';
      }
    )
 
  }
  
 }
 
 

 register_next(f2)
 {
  document.getElementById('loader_show').style.display = 'block';
  //verify otp
  this.form = f2.value;
  console.log(this.form);
  this.form['phone1'] = this.form['phone'];
  this.form['utm_source'] = this.param1;
  this.form['utm_medium'] = this.param2;
  this.form['utm_campaign'] = this.param3;
  this.email = this.form['email'];
  //console.log(this.form);
 
   /* otp comment start-----------*/
     this.auth.otpfunc(this.form).subscribe(
       result => {
         
         this.errorcode = result['errorcode'];
         if(this.errorcode == 0)
         {
           //success
           document.getElementById('otp_valid_span').style.display = 'none';
           
           this.validotpStr = '';
           /* otp comment end----------*/
           //register
           this.auth.up_register_landing(this.form).subscribe(
             result => {
                document.getElementById('loader_show').style.display = 'none';
                alert("Registered Successfully..!!");
                //this.login();
                window.location.href = 'https://www.pokersportsleague.in/thank-you?utm_source='+this.param1+'&user_id='+result.user['id']+'&email='+this.email;
             },
             err => {
                document.getElementById('loader_show').style.display = 'none';
                if(err.error.uname)
                {
                  this.htmlStr = 'This username has already been taken.';
                }
                else if(err.error.email)
                {
                  // this.htmlStr = err.error.email[0];
                  document.getElementById('email_err').style.display = 'block';
                  //document.getElementById('otp_modal').style.display = 'block';
                }else{
                  this.htmlStr = 'Something went wrong . Please try later.'
                }
                window.scroll(0,0);
             }
           )
         /* otp comment start----------  */
         }
         else
         {
           //failure
           document.getElementById('loader_show').style.display = 'none';
           document.getElementById('otp_valid_span').style.display = 'block';
           this.validotpStr = 'Invalid OTP.';
 
         }
 
         //console.log(this.errorcode);
        
      
         },
       err => {
         document.getElementById('loader_show').style.display = 'none';
         alert("Something went wrong.Please try again later ! ");
       }
     ) 
     /* otp comment end----------*/
  
 }
 


togglefp() {
    document.getElementById('divshow2').style.display = 'none';
    document.getElementById('divfp').style.display = newLocal;
  }

}
