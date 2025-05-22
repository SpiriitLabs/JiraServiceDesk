// @ts-ignore
import { Controller } from '@hotwired/stimulus';
import markerSDK from '@marker.io/browser';

export default class extends Controller {
  static values = {
    projectId: String,
  };

  declare readonly projectIdValue: string|null;

  connect(): void {
    console.log("💄 Marker.IO controller connected.");
    if (this.projectIdValue !== null && this.projectIdValue !== "") {
      this.initializeMarker(this.projectIdValue);
    } else {
      console.warn("💥 Project ID is not set.");
    }
  }

  private async initializeMarker(projectId: string): Promise<void> {
    await markerSDK.loadWidget({
      project: projectId,
    });
  }

}
