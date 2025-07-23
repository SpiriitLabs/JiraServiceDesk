// @ts-ignore
import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
  static values = {
    focusedCommentId: String,
  }
  static targets = [];
  
  declare readonly focusedCommentIdValue: string;

  connect(): void {
    console.log("🔍️ Focus comment controller connected.");

    const focused = document.getElementById(this.focusedCommentIdValue);
    if (focused) {
      const elementHeight = focused.offsetHeight;
      const viewportHeight = window.innerHeight;

      console.log(elementHeight < viewportHeight * 0.75);
      if (elementHeight < viewportHeight * 0.75) {
        focused.scrollIntoView({ behavior: 'smooth', block: 'center' });
      } else {
        const rect = focused.getBoundingClientRect();
        const offset = window.scrollY + rect.top - 100;
        window.scrollTo({
          top: offset,
          behavior: 'smooth',
        });
      }

      focused.classList.add('focused-comment');
      focused.classList.add('bg-secondary-subtle');
      focused.classList.add('border-light');
    }
  }

}

