import { Controller } from '@hotwired/stimulus';
import { renderStreamMessage } from '@hotwired/turbo';

export default class extends Controller {
  async navigate(event: Event): Promise<void> {
    event.preventDefault();

    const link = event.currentTarget as HTMLAnchorElement;

    const response = await fetch(link.href, {
      headers: {
        'Accept': 'text/vnd.turbo-stream.html, text/html, application/xhtml+xml',
      },
    });

    if (response.ok) {
      const html = await response.text();
      renderStreamMessage(html);
    }
  }
}
