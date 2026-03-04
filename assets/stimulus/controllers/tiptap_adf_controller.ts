import {Controller} from "@hotwired/stimulus";
import {Editor} from '@tiptap/core';
import StarterKit from '@tiptap/starter-kit';
import Link from '@tiptap/extension-link';
import Underline from '@tiptap/extension-underline';
import Image from '@tiptap/extension-image';
import Table from '@tiptap/extension-table';
import TableRow from '@tiptap/extension-table-row';
import TableCell from '@tiptap/extension-table-cell';
import TableHeader from '@tiptap/extension-table-header';
import {Node, Mark, mergeAttributes} from '@tiptap/core';

// --- Resizable Image Extension ---

const ResizableImage = Image.extend({
  addAttributes() {
    return {
      ...this.parent?.(),
      width: {
        default: null,
        parseHTML: (el: HTMLElement) => el.getAttribute('width') || el.style.width || null,
        renderHTML: (attrs: Record<string, any>) => {
          if (!attrs.width) return {};
          return {width: attrs.width, style: `width: ${attrs.width}px`};
        },
      },
      jiraMediaFileId: {
        default: null,
        rendered: false,
      },
    };
  },

  addNodeView() {
    return ({node, getPos, editor}) => {
      const container = document.createElement('div');
      container.classList.add('tiptap-resizable-image');

      const img = document.createElement('img');
      img.src = node.attrs.src || '';
      img.alt = node.attrs.alt || '';
      if (node.attrs.width) {
        img.style.width = `${node.attrs.width}px`;
      } else {
        img.style.maxWidth = '100%';
      }

      const handle = document.createElement('div');
      handle.classList.add('tiptap-resize-handle');

      container.appendChild(img);
      container.appendChild(handle);

      // Resize logic
      let startX = 0;
      let startWidth = 0;

      const onMouseMove = (e: MouseEvent) => {
        const newWidth = Math.max(50, startWidth + (e.clientX - startX));
        img.style.width = `${newWidth}px`;
      };

      const onMouseUp = (e: MouseEvent) => {
        document.removeEventListener('mousemove', onMouseMove);
        document.removeEventListener('mouseup', onMouseUp);
        document.body.style.cursor = '';
        document.body.style.userSelect = '';

        const newWidth = Math.max(50, startWidth + (e.clientX - startX));
        if (typeof getPos === 'function') {
          editor.chain().focus().command(({tr}) => {
            tr.setNodeMarkup(getPos(), undefined, {
              ...node.attrs,
              width: Math.round(newWidth),
            });
            return true;
          }).run();
        }
      };

      handle.addEventListener('mousedown', (e: MouseEvent) => {
        e.preventDefault();
        startX = e.clientX;
        startWidth = img.getBoundingClientRect().width;
        document.body.style.cursor = 'ew-resize';
        document.body.style.userSelect = 'none';
        document.addEventListener('mousemove', onMouseMove);
        document.addEventListener('mouseup', onMouseUp);
      });

      return {
        dom: container,
        update: (updatedNode) => {
          if (updatedNode.type.name !== 'image') return false;
          img.src = updatedNode.attrs.src || '';
          img.alt = updatedNode.attrs.alt || '';
          if (updatedNode.attrs.width) {
            img.style.width = `${updatedNode.attrs.width}px`;
          } else {
            img.style.maxWidth = '100%';
          }
          return true;
        },
        destroy: () => {
          document.removeEventListener('mousemove', onMouseMove);
          document.removeEventListener('mouseup', onMouseUp);
        },
      };
    };
  },
});

// --- Custom ADF Node Extensions ---

const PanelNode = Node.create({
  name: 'panel',
  group: 'block',
  content: 'block+',
  addAttributes() {
    return {
      panelType: {default: 'info'},
    };
  },
  parseHTML() {
    return [{tag: 'div[data-panel-type]'}];
  },
  renderHTML({node, HTMLAttributes}) {
    return [
      'div',
      mergeAttributes(HTMLAttributes, {
        'data-panel-type': node.attrs.panelType,
        class: `adf-panel adf-panel-${node.attrs.panelType}`,
      }),
      0,
    ];
  },
});

const ExpandNode = Node.create({
  name: 'expand',
  group: 'block',
  content: 'block+',
  addAttributes() {
    return {
      title: {default: null},
    };
  },
  parseHTML() {
    return [{tag: 'details'}];
  },
  renderHTML({node, HTMLAttributes}) {
    return [
      'details',
      mergeAttributes(HTMLAttributes, {class: 'adf-expand'}),
      ['summary', {}, node.attrs.title || ''],
      ['div', {}, 0],
    ];
  },
});

const MediaNode = Node.create({
  name: 'media',
  group: 'block',
  atom: true,
  addAttributes() {
    return {
      id: {default: null},
      alt: {default: null},
      type: {default: 'file'},
      width: {default: null},
      height: {default: null},
      collection: {default: null},
      url: {default: null},
    };
  },
  parseHTML() {
    return [{tag: 'img[data-media-id]'}];
  },
  renderHTML({node, HTMLAttributes}) {
    const src = node.attrs.url || node.attrs.id || '';
    return [
      'img',
      mergeAttributes(HTMLAttributes, {
        src,
        alt: node.attrs.alt || '',
        'data-media-id': node.attrs.id,
        style: 'max-width: 100%;',
      }),
    ];
  },
});

const MediaSingleNode = Node.create({
  name: 'mediaSingle',
  group: 'block',
  content: 'media',
  addAttributes() {
    return {
      layout: {default: 'align-start'},
    };
  },
  parseHTML() {
    return [{tag: 'div[data-media-single]'}];
  },
  renderHTML({HTMLAttributes}) {
    return [
      'div',
      mergeAttributes(HTMLAttributes, {
        'data-media-single': '',
        class: 'adf-media-single',
      }),
      0,
    ];
  },
});

const MediaGroupNode = Node.create({
  name: 'mediaGroup',
  group: 'block',
  content: 'media+',
  parseHTML() {
    return [{tag: 'div[data-media-group]'}];
  },
  renderHTML({HTMLAttributes}) {
    return [
      'div',
      mergeAttributes(HTMLAttributes, {
        'data-media-group': '',
        class: 'adf-media-group',
      }),
      0,
    ];
  },
});

const InlineCardNode = Node.create({
  name: 'inlineCard',
  group: 'inline',
  inline: true,
  atom: true,
  addAttributes() {
    return {
      url: {default: null},
    };
  },
  parseHTML() {
    return [{tag: 'a[data-inline-card]'}];
  },
  renderHTML({node, HTMLAttributes}) {
    return [
      'a',
      mergeAttributes(HTMLAttributes, {
        href: node.attrs.url,
        'data-inline-card': '',
        class: 'adf-inline-card',
      }),
      node.attrs.url || '',
    ];
  },
});

const StatusNode = Node.create({
  name: 'status',
  group: 'inline',
  inline: true,
  atom: true,
  addAttributes() {
    return {
      text: {default: ''},
      color: {default: 'neutral'},
      localId: {default: null},
      style: {default: null},
    };
  },
  parseHTML() {
    return [{tag: 'span[data-status]'}];
  },
  renderHTML({node, HTMLAttributes}) {
    return [
      'span',
      mergeAttributes(HTMLAttributes, {
        'data-status': '',
        class: `adf-status adf-status-${node.attrs.color}`,
      }),
      node.attrs.text,
    ];
  },
});

const DateNode = Node.create({
  name: 'date',
  group: 'inline',
  inline: true,
  atom: true,
  addAttributes() {
    return {
      timestamp: {default: null},
    };
  },
  parseHTML() {
    return [{tag: 'time[data-date]'}];
  },
  renderHTML({node, HTMLAttributes}) {
    const date = node.attrs.timestamp
      ? new Date(parseInt(node.attrs.timestamp, 10)).toLocaleDateString()
      : '';
    return [
      'time',
      mergeAttributes(HTMLAttributes, {
        'data-date': '',
        datetime: node.attrs.timestamp,
      }),
      date,
    ];
  },
});

const MentionNode = Node.create({
  name: 'mention',
  group: 'inline',
  inline: true,
  atom: true,
  addAttributes() {
    return {
      id: {default: null},
      text: {default: null},
      accessLevel: {default: null},
    };
  },
  parseHTML() {
    return [{tag: 'span[data-mention-id]'}];
  },
  renderHTML({node, HTMLAttributes}) {
    return [
      'span',
      mergeAttributes(HTMLAttributes, {
        'data-mention-id': node.attrs.id,
        class: 'adf-mention',
      }),
      node.attrs.text || `@${node.attrs.id}`,
    ];
  },
});

// Custom marks for ADF-specific text formatting
const TextColorMark = Mark.create({
  name: 'textColor',
  addAttributes() {
    return {
      color: {default: null},
    };
  },
  parseHTML() {
    return [{tag: 'span[data-text-color]'}];
  },
  renderHTML({HTMLAttributes}) {
    return [
      'span',
      mergeAttributes(HTMLAttributes, {
        'data-text-color': HTMLAttributes.color,
        style: `color: ${HTMLAttributes.color}`,
      }),
    ];
  },
});

const BackgroundColorMark = Mark.create({
  name: 'backgroundColor',
  addAttributes() {
    return {
      color: {default: null},
    };
  },
  parseHTML() {
    return [{tag: 'span[data-background-color]'}];
  },
  renderHTML({HTMLAttributes}) {
    return [
      'span',
      mergeAttributes(HTMLAttributes, {
        'data-background-color': HTMLAttributes.color,
        style: `background-color: ${HTMLAttributes.color}`,
      }),
    ];
  },
});

const SubSupMark = Mark.create({
  name: 'subsup',
  addAttributes() {
    return {
      type: {default: 'sub'},
    };
  },
  parseHTML() {
    return [
      {tag: 'sub', attrs: {type: 'sub'}},
      {tag: 'sup', attrs: {type: 'sup'}},
    ];
  },
  renderHTML({HTMLAttributes}) {
    const tag = HTMLAttributes.type === 'sup' ? 'sup' : 'sub';
    return [tag, mergeAttributes(HTMLAttributes)];
  },
});

// --- ADF <-> TipTap JSON Conversion ---

// Maps between TipTap names and ADF names
const NODE_NAME_TO_ADF: Record<string, string> = {
  horizontalRule: 'rule',
};
const NODE_NAME_FROM_ADF: Record<string, string> = {
  rule: 'horizontalRule',
};
const MARK_NAME_TO_ADF: Record<string, string> = {
  bold: 'strong',
  italic: 'em',
};
const MARK_NAME_FROM_ADF: Record<string, string> = {
  strong: 'bold',
  em: 'italic',
};

interface AdfNode {
  type: string;
  attrs?: Record<string, any>;
  content?: AdfNode[];
  marks?: Array<{type: string; attrs?: Record<string, any>}>;
  text?: string;
  version?: number;
}

function convertNodeFromAdf(node: AdfNode, attachmentMap: Record<string, string>): AdfNode {
  // Convert ADF mediaSingle > media to TipTap image node for proper editing support
  if (node.type === 'mediaSingle' && node.content?.length === 1 && node.content[0].type === 'media') {
    const media = node.content[0];
    const mediaId = media.attrs?.id || '';
    const src = attachmentMap[mediaId] || mediaId;
    const attrs: Record<string, any> = {
      src,
      alt: media.attrs?.alt || '',
    };
    // Read width from mediaSingle (where Jira stores it) or media node
    const width = node.attrs?.width || media.attrs?.width;
    if (width) {
      attrs.width = width;
    }
    return {
      type: 'image',
      attrs,
    };
  }

  const result: AdfNode = {...node};
  result.type = NODE_NAME_FROM_ADF[node.type] || node.type;

  if (node.marks) {
    result.marks = node.marks.map(mark => ({
      ...mark,
      type: MARK_NAME_FROM_ADF[mark.type] || mark.type,
    }));
  }

  if (node.content) {
    result.content = node.content.map(child => convertNodeFromAdf(child, attachmentMap));
  }

  delete result.version;
  return result;
}

function convertNodeToAdf(node: AdfNode): AdfNode {
  // Convert TipTap image nodes to ADF mediaSingle > media
  if (node.type === 'image' && node.attrs?.src) {
    const src = node.attrs.src;

    const mediaSingleAttrs: Record<string, any> = {layout: 'align-start'};
    if (node.attrs.width) {
      mediaSingleAttrs.width = node.attrs.width;
    }

    // Newly uploaded images have a Jira media file UUID — use type "file"
    if (node.attrs.jiraMediaFileId) {
      return {
        type: 'mediaSingle',
        attrs: mediaSingleAttrs,
        content: [{
          type: 'media',
          attrs: {
            id: node.attrs.jiraMediaFileId,
            type: 'file',
            collection: '',
          },
        }],
      };
    }

    // Base64 images (create mode): keep data: URL as media id for server-side processing
    if (src.startsWith('data:')) {
      return {
        type: 'mediaSingle',
        attrs: mediaSingleAttrs,
        content: [{
          type: 'media',
          attrs: {
            id: src,
            type: 'file',
            collection: '',
          },
        }],
      };
    }

    // Existing Jira images: extract attachment ID from proxy URL
    let mediaId = src;
    const match = src.match(/\/attachment\/(.+)/);
    if (match) {
      mediaId = match[1];
    }
    return {
      type: 'mediaSingle',
      attrs: mediaSingleAttrs,
      content: [{
        type: 'media',
        attrs: {
          id: mediaId,
          type: 'file',
          collection: '',
        },
      }],
    };
  }

  const result: AdfNode = {...node};
  result.type = NODE_NAME_TO_ADF[node.type] || node.type;

  // Convert media nodes: remove display-only 'url' attr
  if (node.type === 'media' && node.attrs) {
    const {url, ...adfAttrs} = node.attrs;
    result.attrs = adfAttrs;
  }

  if (node.marks) {
    result.marks = node.marks.map(mark => ({
      ...mark,
      type: MARK_NAME_TO_ADF[mark.type] || mark.type,
    }));
  }

  if (node.content) {
    result.content = node.content.map(convertNodeToAdf);
  }

  return result;
}

function adfToTipTap(adf: AdfNode, attachmentMap: Record<string, string>): AdfNode {
  return convertNodeFromAdf(adf, attachmentMap);
}

function tipTapToAdf(doc: AdfNode): AdfNode {
  const result = convertNodeToAdf(doc);
  result.version = 1;
  return result;
}

// --- Toolbar Definition ---

interface ToolbarButton {
  icon: string;
  title: string;
  action: (editor: Editor) => void;
  isActive?: (editor: Editor) => boolean;
}

type ToolbarItem = ToolbarButton | 'separator';

const TOOLBAR_ITEMS: ToolbarItem[] = [
  {
    icon: 'mdi-format-bold',
    title: 'Bold',
    action: (e) => e.chain().focus().toggleBold().run(),
    isActive: (e) => e.isActive('bold'),
  },
  {
    icon: 'mdi-format-italic',
    title: 'Italic',
    action: (e) => e.chain().focus().toggleItalic().run(),
    isActive: (e) => e.isActive('italic'),
  },
  {
    icon: 'mdi-format-underline',
    title: 'Underline',
    action: (e) => e.chain().focus().toggleUnderline().run(),
    isActive: (e) => e.isActive('underline'),
  },
  {
    icon: 'mdi-format-strikethrough',
    title: 'Strikethrough',
    action: (e) => e.chain().focus().toggleStrike().run(),
    isActive: (e) => e.isActive('strike'),
  },
  'separator',
  {
    icon: 'mdi-format-header-1',
    title: 'Heading 1',
    action: (e) => e.chain().focus().toggleHeading({level: 1}).run(),
    isActive: (e) => e.isActive('heading', {level: 1}),
  },
  {
    icon: 'mdi-format-header-2',
    title: 'Heading 2',
    action: (e) => e.chain().focus().toggleHeading({level: 2}).run(),
    isActive: (e) => e.isActive('heading', {level: 2}),
  },
  {
    icon: 'mdi-format-header-3',
    title: 'Heading 3',
    action: (e) => e.chain().focus().toggleHeading({level: 3}).run(),
    isActive: (e) => e.isActive('heading', {level: 3}),
  },
  'separator',
  {
    icon: 'mdi-format-list-bulleted',
    title: 'Bullet List',
    action: (e) => e.chain().focus().toggleBulletList().run(),
    isActive: (e) => e.isActive('bulletList'),
  },
  {
    icon: 'mdi-format-list-numbered',
    title: 'Ordered List',
    action: (e) => e.chain().focus().toggleOrderedList().run(),
    isActive: (e) => e.isActive('orderedList'),
  },
  'separator',
  {
    icon: 'mdi-format-quote-close',
    title: 'Blockquote',
    action: (e) => e.chain().focus().toggleBlockquote().run(),
    isActive: (e) => e.isActive('blockquote'),
  },
  {
    icon: 'mdi-code-tags',
    title: 'Code',
    action: (e) => e.chain().focus().toggleCode().run(),
    isActive: (e) => e.isActive('code'),
  },
  {
    icon: 'mdi-code-braces',
    title: 'Code Block',
    action: (e) => e.chain().focus().toggleCodeBlock().run(),
    isActive: (e) => e.isActive('codeBlock'),
  },
  'separator',
  {
    icon: 'mdi-link-variant',
    title: 'Link',
    action: (e) => {
      if (e.isActive('link')) {
        e.chain().focus().unsetLink().run();
        return;
      }
      const url = window.prompt('URL:');
      if (url) {
        e.chain().focus().setLink({href: url}).run();
      }
    },
    isActive: (e) => e.isActive('link'),
  },
  {
    icon: 'mdi-minus',
    title: 'Horizontal Rule',
    action: (e) => e.chain().focus().setHorizontalRule().run(),
  },
  'separator',
  {
    icon: 'mdi-undo',
    title: 'Undo',
    action: (e) => e.chain().focus().undo().run(),
  },
  {
    icon: 'mdi-redo',
    title: 'Redo',
    action: (e) => e.chain().focus().redo().run(),
  },
];

// --- Stimulus Controller ---

export default class extends Controller {
  static targets = ['editor', 'input'];
  static values = {
    issueKey: String,
    uploadUrl: String,
    initialAdf: String,
    attachmentMap: String,
  };

  declare readonly editorTarget: HTMLDivElement;
  declare readonly inputTarget: HTMLInputElement;
  declare issueKeyValue: string;
  declare uploadUrlValue: string;
  declare initialAdfValue: string;
  declare attachmentMapValue: string;

  private editor: Editor | null = null;
  private toolbarButtons: Array<{el: HTMLButtonElement; item: ToolbarButton}> = [];

  connect() {
    const attachmentMap: Record<string, string> = this.attachmentMapValue
      ? JSON.parse(this.attachmentMapValue)
      : {};

    let initialContent: any = undefined;
    if (this.initialAdfValue) {
      try {
        const adf = JSON.parse(this.initialAdfValue);
        initialContent = adfToTipTap(adf, attachmentMap);
      } catch (e) {
        console.error('Failed to parse initial ADF:', e);
      }
    }

    // Create toolbar before the editor
    const toolbar = this.createToolbar();
    this.editorTarget.parentElement!.insertBefore(toolbar, this.editorTarget);

    this.editor = new Editor({
      element: this.editorTarget,
      extensions: [
        StarterKit.configure({
          horizontalRule: true,
          codeBlock: true,
          blockquote: true,
        }),
        Link.configure({
          openOnClick: false,
        }),
        Underline,
        ResizableImage.configure({
          inline: false,
          allowBase64: true,
        }),
        Table.configure({
          resizable: false,
        }),
        TableRow,
        TableCell,
        TableHeader,
        // ADF-specific extensions
        PanelNode,
        ExpandNode,
        MediaNode,
        MediaSingleNode,
        MediaGroupNode,
        InlineCardNode,
        StatusNode,
        DateNode,
        MentionNode,
        TextColorMark,
        BackgroundColorMark,
        SubSupMark,
      ],
      content: initialContent,
      onUpdate: ({editor}) => {
        const tipTapJson = editor.getJSON();
        const adfJson = tipTapToAdf(tipTapJson as AdfNode);
        this.inputTarget.value = JSON.stringify(adfJson);
      },
      onSelectionUpdate: () => {
        this.updateToolbarState();
      },
      onTransaction: () => {
        this.updateToolbarState();
      },
      editorProps: {
        handlePaste: (view, event) => {
          const items = event.clipboardData?.items;
          if (!items) return false;

          for (const item of Array.from(items)) {
            if (item.type.startsWith('image/')) {
              event.preventDefault();
              const file = item.getAsFile();
              if (file) {
                this.handleImageFile(file);
              }
              return true;
            }
          }
          return false;
        },
        handleDrop: (view, event) => {
          const files = event.dataTransfer?.files;
          if (!files || files.length === 0) return false;

          for (const file of Array.from(files)) {
            if (file.type.startsWith('image/')) {
              event.preventDefault();
              this.handleImageFile(file);
              return true;
            }
          }
          return false;
        },
      },
    });

    // If no initial ADF content but input has a value (e.g. form re-render after error),
    // don't overwrite
    if (!initialContent && this.inputTarget.value) {
      // input already has value, leave it
    } else if (!initialContent) {
      // Empty editor, initialize input with empty ADF
      this.inputTarget.value = '';
    }
  }

  disconnect() {
    this.editor?.destroy();
    this.editor = null;
    this.toolbarButtons = [];
  }

  private createToolbar(): HTMLDivElement {
    const toolbar = document.createElement('div');
    toolbar.className = 'tiptap-toolbar';
    let currentGroup = document.createElement('div');
    currentGroup.className = 'tiptap-toolbar-group';

    for (const item of TOOLBAR_ITEMS) {
      if (item === 'separator') {
        if (currentGroup.children.length > 0) {
          toolbar.appendChild(currentGroup);
          currentGroup = document.createElement('div');
          currentGroup.className = 'tiptap-toolbar-group';
        }
        continue;
      }

      const btn = document.createElement('button');
      btn.type = 'button';
      btn.title = item.title;
      btn.innerHTML = `<span class="mdi ${item.icon}"></span>`;
      btn.addEventListener('click', (e) => {
        e.preventDefault();
        if (this.editor) {
          item.action(this.editor);
        }
      });
      currentGroup.appendChild(btn);
      this.toolbarButtons.push({el: btn, item});
    }

    if (currentGroup.children.length > 0) {
      toolbar.appendChild(currentGroup);
    }

    return toolbar;
  }

  private updateToolbarState(): void {
    if (!this.editor) return;
    for (const {el, item} of this.toolbarButtons) {
      if (item.isActive) {
        el.classList.toggle('is-active', item.isActive(this.editor));
      }
    }
  }

  private handleImageFile(file: File): void {
    if (this.uploadUrlValue) {
      this.uploadAndInsertImage(file);
    } else {
      this.insertBase64Image(file);
    }
  }

  private async uploadAndInsertImage(file: File): Promise<void> {
    const formData = new FormData();
    formData.append('file', file);

    try {
      const response = await fetch(this.uploadUrlValue, {
        method: 'POST',
        body: formData,
      });

      if (!response.ok) {
        console.error('Image upload failed:', response.statusText);
        return;
      }

      const data = await response.json();
      if (data.url && this.editor) {
        this.editor.chain().focus().setImage({
          src: data.url,
          alt: file.name,
          jiraMediaFileId: data.mediaFileId || null,
        } as any).run();
      }
    } catch (error) {
      console.error('Image upload error:', error);
    }
  }

  private insertBase64Image(file: File): void {
    const reader = new FileReader();
    reader.onload = () => {
      if (reader.result && this.editor) {
        this.editor.chain().focus().setImage({
          src: reader.result as string,
          alt: file.name,
        }).run();
      }
    };
    reader.readAsDataURL(file);
  }
}
