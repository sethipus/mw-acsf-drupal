// Documentation on theming Storybook: https://storybook.js.org/docs/configurations/theming/

import { create } from '@storybook/theming';

export default create({
  base: 'light',

  // Branding
  brandTitle: 'Starter-Kits',
  brandUrl: 'https://www.mars.com',
  brandImage:
    'https://www.mars.com/sites/g/files/jydpyr316/files/Mars%20Wordmark%20RGB%20Blue.png',

  appBg: 'white',

  colorSecondary: 'rgb(0, 0, 160)',

  fontBase: 'Bahnschrift',

  barBg: 'white',
  barTextColor: 'black',
  barSelectedColor: 'black',

  inputBg: 'white',
  inputBorder: 'black',
  inputTextColor: 'black',
  inputBorderRadius: 5,
});
