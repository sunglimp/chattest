import { Injectable } from '@angular/core';
import { tap } from "rxjs/operators";
import {
  HttpInterceptor,
  HttpRequest,
  HttpResponse,
  HttpHandler,
  HttpEvent,
  HttpErrorResponse
} from '@angular/common/http';

import { Observable } from 'rxjs';
@Injectable()
export class HttpInterceptorService implements HttpInterceptor {
  constructor() {}
  intercept(
    request: HttpRequest<any>,
    next: HttpHandler
  ): Observable<HttpEvent<any>> {
    let currentUserToken = window['USER'].api_token;
    const updatedRequest = request.clone({
      headers: request.headers.set("Authorization", `${currentUserToken}`)
    });
    return next.handle(request).pipe(
      tap(
        event => {
          if (event instanceof HttpResponse) {
            // console.log("api call error :",  error['status']);
          }
        },
        error => {
          if(error['status'] == 401){
            location.reload();
          }
        }
      )
    );
  }
}
