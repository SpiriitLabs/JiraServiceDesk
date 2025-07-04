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
        focused.scrollIntoView({ behavior: 'smooth', block: 'center' });
        focused.classList.add('focused-comment');
        focused.classList.add('bg-secondary-subtle');
        focused.classList.add('border-light');
    }
  }

}

