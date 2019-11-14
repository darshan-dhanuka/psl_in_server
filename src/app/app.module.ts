import { BrowserModule } from '@angular/platform-browser';
import { NgModule } from '@angular/core';
import { HttpClientModule } from '@angular/common/http';
import { FormsModule } from '@angular/forms';
import { AuthenticationService } from './authentication.service';
import { AuthGuardService } from './auth-guard.service';

import { AppRoutingModule } from './app-routing.module';
import { AppComponent } from './app.component';
import { ReactiveFormsModule } from '@angular/forms';																		
import { LandingPageComponent } from './landing-page/landing-page.component';
import { ThankYouComponent } from './thank-you/thank-you.component';
import { EventScheduleLandingComponent } from './event-schedule-landing/event-schedule-landing.component';
import { LoaderDivComponent } from './loader-div/loader-div.component';

@NgModule({
  declarations: [
    AppComponent,
    LandingPageComponent,
    ThankYouComponent,
    EventScheduleLandingComponent,
    LoaderDivComponent
  ],
  imports: [
    BrowserModule,
    AppRoutingModule,
	HttpClientModule,	
	FormsModule,	 
	ReactiveFormsModule
  ],
  providers: [AuthenticationService, AuthGuardService
   
  ],
  bootstrap: [AppComponent]
})
export class AppModule { }
