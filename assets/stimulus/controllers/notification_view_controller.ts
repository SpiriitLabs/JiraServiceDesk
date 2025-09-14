// @ts-ignore
import { Controller } from '@hotwired/stimulus';

export default class extends Controller {

    connect(): void {
        console.log("📝 Notification view controller connected.");

        const elements: Array<Element> = [...this.element.getElementsByClassName('notify-item')];

        for (const element of elements) {
            element.addEventListener('mouseenter', async (e) => {
                let id = element.getAttribute('data-id');

                try {
                    const response = await fetch(`/app/notification/${id}/viewed/api`, {
                        method: 'POST',
                        headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    });

                    if (!response.ok) throw new Error('Failed to update notification');

                } catch (error) {
                    console.error("❌ Failed to update issue transition:", error);
                    window.location.reload();
                }
            })
        }


    }
}
