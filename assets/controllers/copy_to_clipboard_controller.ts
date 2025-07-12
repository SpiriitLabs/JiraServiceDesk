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
  declare urlValue: string;
  declare readonly successTextValue: string;
  declare textSpan: HTMLSpanElement;

  connect(): void {
    console.log("📋 CopyToClipboard controller connected.");
    this.textSpan = document.createElement('span');

    if (this.urlValue == '') {
      this.urlValue = window.location.href;
    }
  }

  public copy(): void
  {
    navigator.clipboard.writeText(this.urlValue.toString()).then(() => {
      this.resetButton();
      this.copiedButton();

      setTimeout(() => {
        this.resetButton();
      }, 2000);
      
    }).catch(err => {
        console.error('Failed to copy: ', err);
    });
  }

  private copiedButton(): void
  {
    this.iconTarget.classList.remove('ri-link');
    this.iconTarget.classList.add('ri-link-unlink-m');
    
    if (this.successTextValue !== '') {
      this.textSpan.classList.add('copy-text', 'ms-1');
      this.buttonTarget.appendChild(this.textSpan);
      this.textSpan.textContent = this.successTextValue.toString();
    }

    this.buttonTarget.classList.remove('text-secondary');
    this.buttonTarget.classList.add('text-success');
  }

  private resetButton(): void
  {
      this.iconTarget.classList.remove('ri-link-unlink-m');
      this.iconTarget.classList.add('ri-link');

      if (this.successTextValue !== '') {
        if (this.textSpan) {
          this.textSpan.textContent = '';
        }
      }

      this.buttonTarget.classList.remove('text-success');
      this.buttonTarget.classList.add('text-secondary');
  }


}

