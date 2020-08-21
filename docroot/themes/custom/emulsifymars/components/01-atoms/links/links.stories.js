import React from 'react';

import link from './link/link.twig';

import linkData from './link/link.yml';

/**
 * Storybook Definition.
 */
export default { title: 'Atoms/Links' };

export const links = () => (
  <div dangerouslySetInnerHTML={{ __html: link(linkData) }} />
);
