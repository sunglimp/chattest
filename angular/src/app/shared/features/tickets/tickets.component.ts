import {
  Component,
  OnInit,
  EventEmitter,
  Output,
  Input,
  NgZone
} from "@angular/core";
import { TicketService } from "../../services/ticket.service";
import { TicketFields } from "../../models/client.model";
import { Router } from "@angular/router";
import { ChatService } from "../../services/chat.service";
import { FormBuilder, FormGroup, NgForm } from "@angular/forms";
import { RemoveAttachmentPipe } from "./../../pipes/common.pipe";

import * as countryTelData from "country-telephone-data";
// import {
//   CountryISO,
//   SearchCountryField,
//   TooltipLabel
// } from "ngx-intl-tel-input";

@Component({
  selector: "app-tickets",
  templateUrl: "./tickets.component.html",
  styleUrls: ["./tickets.component.scss"]
})
export class TicketsComponent implements OnInit {
  // SearchCountryField = SearchCountryField;
  // CountryISO = CountryISO;
  // TooltipLabel = TooltipLabel;
  countryTelData = countryTelData;
  phone_number = "";
  gettingLanguage: object;
  @Output() ticketEmitter: EventEmitter<any> = new EventEmitter();
  @Input() chatId;
  @Input("clientData") clientData;
  @Input() textHeading;
  @Input() appId;
  @Input() Heading;
  @Input("language") language: object;
  success: boolean = false;
  failure: boolean = false;
  createTicket: boolean = true;
  ticketId: string;
  submitted: boolean = false;
  isLoading: boolean = true;
  msg: any;
  fields: TicketFields[] = [];
  ticketFormData: FormGroup;

  constructor(
    private ticketService: TicketService,
    private router: Router,
    public chatService: ChatService,
    private _formBuilder: FormBuilder,
    private ngZone: NgZone,
    private _removeAttachmentPipe: RemoveAttachmentPipe
  ) {}

  ngOnInit() {
    this.ticketService.ticketObservable.subscribe(val => {
      this.closePopup();
    });
    this.ticketService.lqsObservable.subscribe(val => {
      this.closePopup();
    });
    this.getLanguage();
    this.getFields();

    if (this.clientData.sourceType === "whatsapp") {
      this.detectCountryAndUpdate();
    }
  }
  getLanguage() {
    this.ticketService.getLanguage().subscribe(res => {
      this.gettingLanguage = res["data"]["interpretation"];
      this.chatService.currentLanguage = res["data"]["language"];
    });
  }
  getFields() {
    this.ticketService.getFields(this.appId).subscribe((response: any) => {
      if (response.status) {
        this.isLoading = false;
        const formBuilderData = {};
        const formBuilderDataAttachmentInput = {};
        response.data[0]["fields"].forEach((data, index) => {
          // if (index === 3 || index === 4) {
          let field = new TicketFields();
          field.displayName = data.representation_name;
          field.fieldName = data.field_name;
          field.groupName = data.group_name;
          field.isAttachment = data.is_attachment;
          field.isDropdown = data.is_dropdown || "0";
          if (field.isDropdown === "1") {
            field.dropdownValues = Object.keys(data.dropdown_values);
          }
          field.isRequired = data.is_mandatory == 1;
          field.errors = "";
          field.attachmenterrors = "";
          formBuilderData[field.fieldName] = "";
          this.fields.push(field);
          if (field.isAttachment === "1") {
            let field1 = new TicketFields();
            field1.displayName = data.representation_name;
            field1.fieldName = data.field_name + "_attachment";
            field1.groupName = data.group_name;
            field1.isAttachment = data.is_attachment;
            field1.isRequired = data.is_mandatory == 1;
            field1.errors = "";
            field.attachmenterrors = "";
            formBuilderDataAttachmentInput[field1.fieldName] = "";
          }
          // }
        });
        const fieldList = {
          ...formBuilderData,
          ...formBuilderDataAttachmentInput
        };
        this.ticketFormData = this._formBuilder.group(fieldList);
      }
    });
  }

  minimizePopup(type) {
    if (type === "LQS") {
      this.chatService.lqsIsMinimized = true;
      this.chatService.ticketIsMinimized = false;
    } else if (type === "TMS") {
      this.chatService.ticketIsMinimized = true;
      this.chatService.lqsIsMinimized = false;
    }
  }

  closePopup() {
    if (this.success == true && this.router.url === "/ticket") {
      this.ticketEmitter.emit({
        ticketCreated: false,
        closeSuccess: true
      });
    } else {
      this.ticketEmitter.emit({
        ticketCreated: false,
        close: true
      });
    }
  }

  onFileSelect(event, i, form: NgForm) {
    const fileSize = event.target.files[0].size / 1000000;

    if (fileSize < 10) {
      const file = event.target.files[0];
      this.ticketFormData
        .get(this.fields[i].fieldName + "_attachment")
        .setValue(file);
      form.controls[this.fields[i].fieldName + "_attachment"].setValue(
        file.name
      );
      this.fields[i].attachmenterrors = "";
    } else {
      this.fields[i].attachmenterrors =
        this.fields[i].displayName +
        " document can't be greater than 10MB in size";
      // alert("Please upload a file of valid format and size");
    }
  }

  showInternational: boolean = true;
  selectedCountry: String = "in";
  selectedNumber = "";
  detectCountryAndUpdate() {
    let selectedCountry = "in";
    let dialCode = "91";
    if (!this.clientData.clientInfo.whatsapp.identifier.includes("*")) {
      for (let i = 0; i < this.countryTelData.allCountries.length; i++) {
        if (
          this.clientData.clientInfo &&
          this.clientData.clientInfo.whatsapp &&
          this.clientData.clientInfo.whatsapp.identifier &&
          this.clientData.clientInfo.whatsapp.identifier.startsWith(
            this.countryTelData.allCountries[i].dialCode
          )
        ) {
          selectedCountry = this.countryTelData.allCountries[i]["iso2"];
          dialCode = this.countryTelData.allCountries[i]["dialCode"];
          this.showInternational = false;
          this.clientData.clientInfo.whatsapp.identifier = this.clientData.clientInfo.whatsapp.identifier
            .replace("+", "")
            .split(" ")
            .join("");

          if (
            this.clientData.clientInfo.whatsapp.identifier.substr(
              0,
              dialCode.length
            ) == dialCode
          ) {
            this.selectedNumber =
              "+" +
              dialCode +
              " " +
              this.clientData.clientInfo.whatsapp.identifier.substr(
                dialCode.length
              );
          }

          setTimeout(() => {
            this.showInternational = true;
            this.selectedCountry = selectedCountry;
          }, 0);
          return;
        }
      }
    }
  }

  onFormSubmit(form: NgForm) {
    this.submitted = true;
    let payload = new FormData();
    payload.append("chat_id", this.chatId);
    payload.append("application", this.appId);
    this.fields.forEach(field => {
      field.errors = "";
      field.attachmenterrors = "";
      if (field.isAttachment == "1") {
        payload.append(
          field.fieldName + "_attachment",
          this.ticketFormData.get(field.fieldName + "_attachment").value
        );
        if (field.fieldName.toLowerCase() === "mobile") {
          payload.append(
            field.fieldName,
            form.control.value[field.fieldName]
              .toString()
              .replace(" ", "")
              .replace("+", "")
          );
        } else {
          payload.append(field.fieldName, form.control.value[field.fieldName]);
        }
      } else if (field.isAttachment == "0") {
        if (field.fieldName.toLowerCase() === "mobile") {
          payload.append(
            field.fieldName,
            form.control.value[field.fieldName]
              .toString()
              .replace(" ", "")
              .replace("+", "")
          );
        } else {
          payload.append(field.fieldName, form.control.value[field.fieldName]);
        }
      }
    });

    if (form.valid) {
      this.isLoading = true;
      this.ticketService.createTicket(payload).subscribe(
        (response: any) => {
          this.isLoading = false;
          this.createTicket = false;
          if (response.status) {
            this.failure = false;
            this.success = true;
            this.msg = response["message"];
            this.ticketId = response.data[0]["ticket_id"];
            this.ticketEmitter.emit({
              ticketCreated: true
            });
            this.chatService.ticketIsMinimized = false;
            this.chatService.lqsIsMinimized = false;
          } else {
            this.failure = true;
            this.success = false;
          }
        },
        error => {
          this.isLoading = false;
          let errorArray = error.error.errors;

          let keys = Object.keys(errorArray);

          keys.forEach(key => {
            let input = this.fields.filter(field => field.fieldName === key);
            if (input && input.length > 0) {
              input[0].errors = errorArray[key];
              if (input[0].isAttachment === "1") {
                input[0].attachmenterrors = errorArray[key + "_attachment"];
              }
            }
          });
        }
      );
    }
  }
}
