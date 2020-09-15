import React from 'react';
import dropdown from './dropdown.twig';
import dropdownData from './dropdown.yml';

export default { title: 'Molecules/Dropdown' };

export const dropdownExample = () => (
  <div dangerouslySetInnerHTML={{ __html: dropdown(dropdownData) }} />
);
