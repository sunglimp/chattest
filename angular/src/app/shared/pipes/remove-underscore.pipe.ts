import { Pipe, PipeTransform } from "@angular/core";

@Pipe({
  name: "removeUnderscore"
})
export class RemoveUnderscorePipe implements PipeTransform {
  transform(value: any, args?: any): any {
    value = value
      .replace("_", " ")
      .replace("_", " ")
      .replace("_", " ");
    return value;
  }
}
