import React from 'react';

import link from './link/link.twig';
import defaultLink from './defaultLink/defaultLink.twig';

import linkData from './link/link.yml';
import defaultLinkData from './defaultLink/defaultLink.yml';

/**
 * Storybook Definition.
 */
// export default { title: 'Atoms/Links' };

export const links = () => (
  <div dangerouslySetInnerHTML={{ __html: link(linkData) }} />
);

export const defaultLinks = () => (
  <div dangerouslySetInnerHTML={{ __html: defaultLink(defaultLinkData) }} />
);
