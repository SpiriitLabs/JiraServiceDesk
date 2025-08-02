import { Controller } from '@hotwired/stimulus';


export default class extends Controller {
  static targets = ["scrollbarTop", "scrollbarTopInner", "scrollbarBottom"];

  declare readonly scrollbarTopTarget: HTMLDivElement;
  declare readonly scrollbarTopInnerTarget: HTMLDivElement;
  declare readonly scrollbarBottomTarget: HTMLDivElement;

  connect(): void {
    console.log("📋 Scrollbar Top controller connected.");

    this.listener();
    if (this.scrollbarTopInnerTarget && '' == this.scrollbarTopInnerTarget.style.width) {
      this.scrollbarTopInnerTarget.style.width = this.scrollbarBottomTarget.scrollWidth + 'px';
    }
  }

  private listener(): void
  {
    this.scrollbarTopTarget.addEventListener('scroll', () => {
      this.scrollbarBottomTarget.scrollLeft = this.scrollbarTopTarget.scrollLeft;
    });

    this.scrollbarBottomTarget.addEventListener('scroll', () => {
      this.scrollbarTopTarget.scrollLeft = this.scrollbarBottomTarget.scrollLeft;
    });
  }

}

