import { addons } from '@storybook/addons';
import emulsifyTheme from './emulsifyTheme';

addons.setConfig({
  showRoots: false,
  theme: emulsifyTheme,
});
