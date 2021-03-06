import { Component, OnInit } from '@angular/core';
import { Router,ActivatedRoute } from "@angular/router";

@Component({
  selector: 'app-thank-you',
  templateUrl: './thank-you.component.html',
  styleUrls: ['./thank-you.component.css']
})

export class ThankYouComponent implements OnInit {
  param1: string;
  param2: string;
  constructor(private router: Router,private route: ActivatedRoute) { 
    this.route.queryParams.subscribe(params => {
      this.param1 = params['email'];
      this.param2 = params['user_id'];
    });
  }

  ngOnInit() {
    setTimeout(() => {
      window.location.href = 'https://www.pokersportsleague.com//qualifier?email='+this.param1+'&redirect=ty';
    }, 10000);
  }

}
