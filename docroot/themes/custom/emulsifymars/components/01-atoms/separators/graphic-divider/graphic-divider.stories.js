import React from 'react';
import graphicDividerTwig from './graphic-divider.twig';
import graphicDividerData from './graphic-divider.yml';

/**
 * Storybook Definition.
 */
// export default { title: 'Atoms/Separators' };

export const graphicDividerExample = () => (
  <div dangerouslySetInnerHTML={{ __html: graphicDividerTwig(graphicDividerData) }} />
);
