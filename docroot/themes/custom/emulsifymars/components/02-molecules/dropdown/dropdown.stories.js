import React from 'react';
import { useEffect } from '@storybook/client-api';

import dropdown from './dropdown.twig';
import dropdownData from './dropdown.yml';

import './dropdown';

// export default { title: 'Molecules/Dropdown' };

export const dropdownExample = () => {
  useEffect(() => Drupal.attachBehaviors(), []);
  return <div dangerouslySetInnerHTML={{ __html: dropdown(dropdownData) }} />;
};
