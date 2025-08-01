import { Controller } from '@hotwired/stimulus';


export default class extends Controller {

  connect(): void {
    console.log("📋 Scrollbar Top controller connected.");

    const scrollbarTop = document.getElementById('scrollbar-top');
    if (scrollbarTop) {
      scrollbarTop.addEventListener('scroll', () => {
        const target = document.getElementById('scrollbar-bottom');
        if (target) {
          target.scrollLeft = scrollbarTop.scrollLeft;
        }
      });
    }

    const scrollbarBottom = document.getElementById('scrollbar-bottom');
    if (scrollbarBottom) {
      scrollbarBottom.addEventListener('scroll', () => {
        const target = document.getElementById('scrollbar-top');
        if (target) {
          target.scrollLeft = scrollbarBottom.scrollLeft;
        }
      });
      // init top width.
      const topInner = document.getElementById('scrollbar-top-inner');
      if (topInner && '' == topInner.style.width) {
        topInner.style.width = scrollbarBottom.scrollWidth + 'px';
      }
    }
  }

}

