// @ts-ignore
import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
  static targets = ["notification"];
  static values = { id: Number, link: String };

  declare readonly notificationTarget: HTMLDivElement;
  declare readonly idValue: number;
  declare readonly linkValue: string;

    connect(): void {
    }

    public close(): void {
      this.markAsRead();
      this.removeNotification();
    }

    public open(): void {
      this.markAsRead().then(() => {
        window.location.href = this.linkValue;
      });
    }

    private removeNotification(): void {
      this.notificationTarget.remove();

      const list = document.getElementById('notifications-list');
      if (!list) return;

      if (list.children.length > 0) {
        return;
      }

      const badge = document.getElementById('notifications-icon-badge');
      if (badge) {
        badge.remove();
      }

      if (!list.querySelector('.text-muted.font-12.text-uppercase.mt-2')) {
        const h5 = document.createElement('h5');
        h5.className = 'text-muted font-12 text-uppercase mt-2';
        h5.textContent = 'No notifications';
        list.appendChild(h5);
      }
    }

    private async markAsRead(): Promise<void> {
      try {
        const response = await fetch(`/app/notification/${this.idValue}/viewed/api`, {
          method: 'POST',
          headers: { 'X-Requested-With': 'XMLHttpRequest' },
        });

        if (!response.ok) throw new Error('Failed to update notification');
      } catch (error) {
        console.error("❌ Failed to update notification:", error);
        window.location.reload();
      }
    }
}
