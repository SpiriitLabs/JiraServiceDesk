import {Controller} from "@hotwired/stimulus";
import { convertHtmlToADF } from "@razroo/html-to-adf";

import Quill from 'quill';

export default class extends Controller {
  static targets = ['editor', 'input']

  declare toolbarValue: Array<any>;
  declare readonly editorTarget: HTMLDivElement;
  declare readonly inputTarget: HTMLInputElement;


  connect() {
    console.log("QuillEditor connected 🖋")

    this.editorTarget.style.height = this.inputTarget.style.height;

    const quillEditor = new Quill(this.editorTarget, {
      theme: 'snow',
      modules: {
        toolbar: [
          ['bold', 'italic', 'underline', 'strike'],        // toggled buttons
          ['blockquote', 'code-block'],
          ['link'],

          [{ 'header': 1 }, { 'header': 2 }],               // custom button values
          [{ 'list': 'ordered'}, { 'list': 'bullet' }, { 'list': 'check' }],
          [{ 'script': 'sub'}, { 'script': 'super' }],      // superscript/subscript
          [{ 'indent': '-1'}, { 'indent': '+1' }],          // outdent/indent
          [{ 'direction': 'rtl' }],                         // text direction

          [{ 'size': ['small', false, 'large', 'huge'] }],  // custom dropdown
          [{ 'header': [1, 2, 3, 4, 5, 6, false] }],

          [{ 'color': [] }, { 'background': [] }],          // dropdown with defaults from theme
          [{ 'font': [] }],
          [{ 'align': [] }],

          ['clean']                                         // remove formatting button
        ],
      }
    });

    quillEditor.on('text-change', (): void => {
      this.inputTarget.value = convertHtmlToADF(quillEditor.getSemanticHTML());
    })
  }
}
