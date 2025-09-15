// @ts-ignore
import { Controller } from '@hotwired/stimulus';

export default class extends Controller {

    connect(): void {
        console.log("📝 Notification view controller connected.");

        const elements: Array<Element> = [...this.element.getElementsByClassName('notify-item')];

        for (const element of elements) {
            let isViewed = element.getAttribute('data-is-viewed');
            if (isViewed) {
                continue;
            }
            element.addEventListener('mouseenter', async (e) => {
                let id = element.getAttribute('data-id');

                try {
                    const response = await fetch(`/app/notification/${id}/viewed/api`, {
                        method: 'POST',
                        headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    });

                    if (!response.ok) throw new Error('Failed to update notification');

                } catch (error) {
                    console.error("❌ Failed to update notification:", error);
                    window.location.reload();
                }
            })
        }


    }
}
