import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { EventScheduleLandingComponent } from './event-schedule-landing.component';

describe('EventScheduleLandingComponent', () => {
  let component: EventScheduleLandingComponent;
  let fixture: ComponentFixture<EventScheduleLandingComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ EventScheduleLandingComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(EventScheduleLandingComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
