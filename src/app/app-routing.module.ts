import { NgModule } from '@angular/core';
import { Routes, RouterModule } from '@angular/router';
import { LandingPageComponent } from './landing-page/landing-page.component';
import { ThankYouComponent } from './thank-you/thank-you.component';


const routes: Routes = [
  {path: '', component: LandingPageComponent},
  {path: 'landing', component:  LandingPageComponent},
  {path: 'thank-you', component:  ThankYouComponent}
];

@NgModule({
  imports: [RouterModule.forRoot(routes)],
  exports: [RouterModule]
})
export class AppRoutingModule { }
