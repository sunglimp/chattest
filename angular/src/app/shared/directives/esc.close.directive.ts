import { Directive, HostListener, Output, EventEmitter } from '@angular/core';

@Directive({
  selector: '[escClose]'
})
export class EscCloseDirective {

  constructor() { }

  @Output() escEvent = new EventEmitter<boolean>();

  @HostListener('document:keyup',['$event']) handleKeyUp(event){
    if(event.keyCode === 27){
      this.escEvent.emit(false);
    }
  }

}
