// @ts-ignore
import { Controller } from '@hotwired/stimulus';
import introJs from 'intro.js';

export default class extends Controller {

  connect(): void {
    console.log("⚡️ Intro controller connected.");
    introJs().start();
  }

}

