// @ts-ignore
import { Controller } from '@hotwired/stimulus';
import introJs from 'intro.js';

export default class extends Controller {
  static values = {
    hasCompletedIntroduction: Boolean,
    apiUrl: String,
  };

  declare readonly hasCompletedIntroductionValue: boolean;
  declare readonly apiUrlValue: string;

  connect(): void {
    console.log("📄 Intro controller connected.");

    if (this.hasCompletedIntroductionValue == false) {
      introJs().start();

      this.completeIntroduction();
    }
  }

  private async completeIntroduction(): Promise<void>
  {
    await fetch(
      this.apiUrlValue,
      {
        method: "POST",
      }
    )
  }

}

