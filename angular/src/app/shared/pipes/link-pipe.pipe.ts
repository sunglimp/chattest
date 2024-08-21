import { Pipe, PipeTransform } from "@angular/core";

@Pipe({
  name: "linkPipe"
})
export class LinkPipePipe implements PipeTransform {
  transform(value: any): any {
    var exp = /((((ftp|http|https|gopher|mailto|news|nntp|telnet|wais|file|prospero|aim|webcal):([A-Za-z0-9$_.+!*(),;/?:@&~=-]){2})|www[.])([a-zA-Z0-9][a-zA-Z0-9#$_.+!*(),;/?:@&~=%-]*))(?![^<]*?>)(?![^<]*?<\/a>)(?![^<]*?<\/img>)/gi;
    if (value) {
      if (value.indexOf("http") > -1) {
        return value.replace(exp, `<a href=\"$1\" target=\"_blank\">$1</a>`);
      }
      return value.replace(exp, `<a href=https://$1 target=\"_blank\">$1</a>`);
    }
    if (value === null || value === undefined) {
      return "";
    }
    return value;
  }
}
