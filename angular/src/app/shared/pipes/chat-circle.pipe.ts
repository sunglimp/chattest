import { Pipe, PipeTransform } from "@angular/core";

@Pipe({
  name: "chatCircle"
})
export class ChatCirclePipe implements PipeTransform {
  transform(value: string, args?: any): any {
    if (value === null) return null;
    let splits = value.split(" ");
    splits = splits.filter(split => {
      return split !== "";
    });
    if (splits.length > 0) {
      if (splits.length === 1) return splits[0][0];
      else return splits[0][0] + splits[1][0];
    }
    return "$$";
  }
}
