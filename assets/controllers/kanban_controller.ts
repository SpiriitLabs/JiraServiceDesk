// @ts-ignore
import { Controller } from '@hotwired/stimulus';
import dragula from "dragula";
import Swal from 'sweetalert2';

export default class extends Controller {
  static values = {
    columns: String,
    selectInputTitle: String,
  };

  declare readonly columnsValue: string;
  declare readonly selectInputTitleValue: string;
  private drake: any;

  connect(): void {
    console.log("🌲 Kanban controller connected.");

    if (window.innerWidth > 768) {
      this.createSortableKanban();
    }
  }

  public createSortableKanban(): void {
    const divElements: HTMLDivElement[] = [];
    JSON.parse(this.columnsValue).forEach((columnId: string) => {
      divElements.push(document.getElementById(columnId) as HTMLDivElement);
    });

    this.drake = dragula(divElements);

    this.drake.on('drop', async (element: Element, target: Element) => {
      const projectKey = element.getAttribute('data-project-key');
      const issueId = element.getAttribute('data-issue-id');
      const transitionData = target.getAttribute('data-kanban-transition-ids');

      if (!issueId || !transitionData) {
        console.warn("Missing data for issue or column.");
        return;
      }

      let transitions;
      try {
        transitions = JSON.parse(transitionData);
      } catch (e) {
        console.error("Invalid transition JSON:", transitionData);
        return;
      }

      let selectedTransitionId;

      if (transitions.length === 1) {
        selectedTransitionId = transitions[0].id;
      } else {
        const options = transitions.reduce((acc: Record<string, string>, t: any) => {
          acc[t.id] = t.name;
          return acc;
        }, {});

        const result = await Swal.fire({
          title: this.selectInputTitleValue,
          input: 'select',
          inputOptions: options,
          showCancelButton: true,
          allowOutsideClick: true,
          allowEscapeKey: true,
        });

        if (!result.isConfirmed || !result.value) {
          window.location.reload();
          return;
        }

        selectedTransitionId = result.value;
      }

      try {
        const response = await fetch(`/app/project/${projectKey}/issue/${issueId}/transition/${selectedTransitionId}/api`, {
          method: 'POST',
          headers: { 'X-Requested-With': 'XMLHttpRequest' },
        });

        if (!response.ok) throw new Error('Failed to update issue');

      } catch (error) {
        console.error("❌ Failed to update issue transition:", error);
        window.location.reload();
      }
    });
  }
}
