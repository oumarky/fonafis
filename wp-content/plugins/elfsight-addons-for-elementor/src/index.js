'use strict';


import { ElfsightEmbedSDK } from '@elfsight/embed-sdk/lib/embed-sdk.cjs';

const {
  displayEditButton,
  displayCreateButton,
  resetWidget,
  setQueryParams
} = ElfsightEmbedSDK;

import './style.styl';

setQueryParams({
  'utm_source': 'portals',
  'utm_medium': 'wordpress-org',
  'utm_campaign': 'elfsight-elementor-wordpress',
  'utm_content': 'iframe',
});

const IFRAME_ID = 'elementor-preview-iframe';
const SECTION_ACTIVE = 'elfsight-control-section-active';

const elfsightElementorWidgetControl = elementor.modules.controls.BaseData.extend({
  onReady: function () {
    this.defineControlsContainers();
    this.init();
  },

  widgetState: false,
  containers: {},

  setWidgetState: function() {
    const { id = null } = this.getControlValue();

    this.widgetState = !!id;
  },

  getWidgetState: function() {
    return this.widgetState;
  },

  defineControlsContainers: function() {
    this.containers = {
      sections: {
        edit: this.el.querySelector('#edit-container'),
        create: this.el.querySelector('#create-container'),
      },
      buttons: {
        edit: this.el.querySelector('#edit-widget-button-container'),
        create: this.el.querySelector('#create-widget-button-container'),
      }
    };
  },

  init: function() {
    this.setWidgetState();
    this.clearControls();

    if (this.getWidgetState()) {
      this.renderEditButton();
    } else {
      this.renderCreateButton();
    }
  },

  clearControls: function() {
    const { buttons, sections } = this.containers;

    sections.edit.classList.toggle(SECTION_ACTIVE, this.getWidgetState());
    sections.create.classList.toggle(SECTION_ACTIVE, !this.getWidgetState());

    for (let key in buttons) {
      if (buttons.hasOwnProperty(key)) {
        buttons[key].innerHTML = '';
      }
    }
  },

  renderEditButton: function() {
    const { id = null } = this.getControlValue();
    const container = this.containers.buttons.edit;

    displayEditButton(container, (widgetId) => {
      const previewIframe = window.document.getElementById(IFRAME_ID);

      resetWidget(widgetId, previewIframe);
    }, {
      widgetId: id,
      size: 'medium',
      width: '100%'
    });
  },

  renderCreateButton: function() {
    const container = this.containers.buttons.create;

    displayCreateButton(container, (widget) => {
      const { id, app } = widget;

      this.save({ id, app });
    }, {
      text: 'Select Widget',
      copyright: false,
      icon: 'plus',
      size: 'medium',
      width: '100%'
    });
  },

  save: function(widget) {
    this.setValue(widget);
    this.init();
  }
});

elementor.addControlView('elfsight-elementor-widget-control', elfsightElementorWidgetControl);
