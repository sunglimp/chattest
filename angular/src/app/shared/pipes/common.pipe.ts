import { NgModule } from "@angular/core";
import { Pipe, PipeTransform } from "@angular/core";

@Pipe({
  name: "checkTagSelection"
})
export class CheckTagSelectionPipe implements PipeTransform {
  transform(value: any, args?: any): any {
    let flag = false;
    if (value && value.length > 0) {
      if (value.indexOf(args) > -1) {
        flag = true;
      }
    }
    return flag;
  }
}

@Pipe({
  name: "removeAttachment"
})
export class RemoveAttachmentPipe implements PipeTransform {
  transform(value: any, args?: any): any {
    if (value) {
      // console.log(value.replace("_attachment", ""));
      return value.replace("_attachment", "");
    }
    return value;
  }
}

@Pipe({
  name: "checkForAttachmentPipe"
})
export class CheckForAttachmentPipe implements PipeTransform {
  transform(value: any, args?: any): any {
    if (value) {
      if (Array.isArray(value)) {
        return true;
      }
      return false;
    }
    return false;
  }
}

@Pipe({
  name: "removeCountryCode"
})
export class RemoveCountryCodePipe implements PipeTransform {
  transform(value: any, args?: any): any {
    if (value) {
      console.log(value);
      return value.split(" ")[1];
    }
    return value;
  }
}
