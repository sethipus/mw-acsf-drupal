import { configure, addDecorator, addParameters } from '@storybook/react';
import { withA11y } from '@storybook/addon-a11y';
import { action } from '@storybook/addon-actions';
import { INITIAL_VIEWPORTS } from '@storybook/addon-viewport';

// Theming
import emulsifyTheme from './emulsifyTheme';

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
  }
};

addParameters({
  options: {
    theme: emulsifyTheme,
  },
  viewport: {
    viewports: {
      ...INITIAL_VIEWPORTS,
      ...customViewports,
    },
  },
});

// GLOBAL CSS
import '../components/style.scss';

addDecorator(withA11y);

const Twig = require('twig');
const twigDrupal = require('twig-drupal-filters');
const twigBEM = require('bem-twig-extension');
const twigAddAttributes = require('add-attributes-twig-extension');
const customTwigFunctions = require('./customTwigFilters');

Twig.cache();

twigDrupal(Twig);
twigBEM(Twig);
twigAddAttributes(Twig);
customTwigFunctions(Twig)

// config.js
import jquery from "jquery";
import once from "jquery-once";
import underscore from "underscore";

global.$ = jquery;
global.jQuery =  jquery;
global._ = underscore;

// If in a Drupal project, it's recommended to import a symlinked version of drupal.js.
import './_drupal.js';

// automatically import all files ending in *.stories.js
configure(require.context('../components', true, /\.stories\.js$/), module);

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
