import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { LoaderDivComponent } from './loader-div.component';

describe('LoaderDivComponent', () => {
  let component: LoaderDivComponent;
  let fixture: ComponentFixture<LoaderDivComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ LoaderDivComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(LoaderDivComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
