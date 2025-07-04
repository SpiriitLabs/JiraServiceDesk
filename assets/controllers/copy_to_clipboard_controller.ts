// @ts-ignore
import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
  static values = {
    url: String,
    successText: String,
  }

  static targets = ["button", "icon"];

  declare readonly buttonTarget: HTMLButtonElement;
  declare readonly iconTarget: HTMLElement;
  declare urlValue: String;
  declare readonly successTextValue: String;

  connect(): void {
    console.log("📋 CopyToClipboard controller connected.");

    if (this.urlValue == '') {
      this.urlValue = window.location.href;
    }
  }

  public copy(): void
  {
    navigator.clipboard.writeText(this.urlValue.toString()).then(() => {
      this.iconTarget.classList.remove('ri-link');
      this.iconTarget.classList.add('ri-link-unlink-m');
      
      let textSpan = document.createElement('span');
      if (this.successTextValue !== '') {
        textSpan.classList.add('copy-text', 'ms-1');
        this.buttonTarget.appendChild(textSpan);
        textSpan.textContent = this.successTextValue.toString();
      }

      this.buttonTarget.classList.remove('text-secondary');
      this.buttonTarget.classList.add('text-success');

      setTimeout(() => {
        this.iconTarget.classList.remove('ri-link-unlink-m');
        this.iconTarget.classList.add('ri-link');

        if (this.successTextValue !== '') {
          if (textSpan) {
            textSpan.textContent = '';
          }
        }

        this.buttonTarget.classList.remove('text-success');
        this.buttonTarget.classList.add('text-secondary');
      }, 2000);
      
    }).catch(err => {
        console.error('Failed to copy: ', err);
    });

  }


}

