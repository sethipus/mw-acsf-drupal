import { INITIAL_VIEWPORTS } from '@storybook/addon-viewport';

import React from 'react';

const customViewports = {
  xs: {
    name: 'iPhone SE, portrait',
    styles: {
      width: '320px',
      height: '568px',
    },
  },
  small: {
    name: 'iPhone X, portrait',
    styles: {
      width: '375px',
      height: '667px',
    },
  },
  medium: {
    name: 'iPad Mini, portrait',
    styles: {
      width: '768px',
      height: '1024px',
    },
  },
  large: {
    name: 'iPad Mini, landscape',
    styles: {
      width: '1024px',
      height: '768px',
    },
  },
  xl: {
    name: '13" MacBook Pro (2x scaling)',
    styles: {
      width: '1440px',
      height: '1024px',
    },
  },
  xxl: {
    name: 'Desktop',
    styles: {
      width: '1920px',
      height: '1080px',
    },
  },
  xxxl: {
    name: '2K Desktop',
    styles: {
      width: '2560px',
      height: '1440px',
    },
  },
};

export const parameters = {
  a11y: {
    element: '#root',
    config: {},
    options: {},
  },
  viewport: {
    viewports: {
      ...INITIAL_VIEWPORTS,
      ...customViewports,
    },
  },
  options: {
    storySort: (a, b) =>
      a[1].kind === b[1].kind
        ? 0
        : a[1].id.localeCompare(b[1].id, undefined, { numeric: true }),
  },
  previewTabs: {
    'storybook/docs/panel': {
      hidden: false,
    },
  },
};

// GLOBAL CSS
import '../components/style.scss';

const Twig = require('twig');
const twigDrupal = require('twig-drupal-filters');
const twigBEM = require('bem-twig-extension');
const twigAddAttributes = require('add-attributes-twig-extension');
const customTwigFunctions = require('./customTwigFilters');

Twig.cache();

twigDrupal(Twig);
twigBEM(Twig);
twigAddAttributes(Twig);
customTwigFunctions(Twig);

// config.js
import jquery from 'jquery';
import once from 'jquery-once';
import underscore from 'underscore';

global.$ = jquery;
global.jQuery = jquery;
global._ = underscore;

// If in a Drupal project, it's recommended to import a symlinked version of drupal.js.
import './_drupal.js';

// Below is for if Emulsify Gatsby style guide is being used
// // Gatsby's Link overrides:
// // Gatsby defines a global called ___loader to prevent its method calls from creating console errors you override it here
// global.___loader = {
//   enqueue: () => {},
//   hovering: () => {},
// };
// // Gatsby internal mocking to prevent unnecessary errors in storybook testing environment
// global.__PATH_PREFIX__ = '';
// // This is to utilized to override the window.___navigate method Gatsby defines and uses to report what path a Link would be taking us to if it wasn't inside a storybook
// window.___navigate = pathname => {
//   action('NavigateTo:')(pathname);
// };

//  Adding toolbar to change between resize mode and use a decorator to set a global variable that will be used by the mocked js twig filters.
global.sb_mars = global.sb_mars || {};

export const globalTypes = {
  resize_mode: {
    name: 'Resize mode',
    description: 'Resize mode',
    defaultValue: 'none',
    toolbar: {
      icon: 'outline',
      items: ['none', 'square', 'portrait', 'landscape'],
    },
  },
};

const resizeGlobalValueDecorator = (Story, context) => {
  global.sb_mars.resize_mode = context.globals.resize_mode;
  return <Story {...context} />;
};
export const decorators = [resizeGlobalValueDecorator];
