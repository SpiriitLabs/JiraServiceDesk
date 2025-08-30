// @ts-ignore
import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
  static targets = ["cta"];

  declare readonly ctaTarget: HTMLElement;
  declare deferredPrompt;

  connect(): void {
    window.addEventListener('beforeinstallprompt', (e) => {
      e.preventDefault();
      this.deferredPrompt = e;
    });
  }

  async installApp() {
    if (this.deferredPrompt) {
      this.deferredPrompt.prompt();
      const { outcome } = await this.deferredPrompt.userChoice;
      this.deferredPrompt = null;
    }
  }
}
