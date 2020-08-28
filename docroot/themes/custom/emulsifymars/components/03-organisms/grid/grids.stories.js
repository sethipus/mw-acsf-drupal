import React from 'react';

import grid from './grid.twig';
import gridData from './grid.yml';
import gridff from './grid-ff.twig';
import gridffData from './grid-ff.yml';
import gridCardData from './grid-cards.yml';
import gridCtaData from './grid-ctas.yml';

/**
 * Storybook Definition.
 */
export default { title: 'Organisms/Grids' };

export const defaultGrid = () => (
  <div dangerouslySetInnerHTML={{ __html: grid(gridData) }} />
);
export const FlexibleFramerGrid = () => (
  <div
    dangerouslySetInnerHTML={{ __html: gridff({...gridffData }) }}
  />
);
export const cardGrid = () => (
  <div
    dangerouslySetInnerHTML={{ __html: grid({ ...gridData, ...gridCardData }) }}
  />
);
export const ctaGrid = () => (
  <div
    dangerouslySetInnerHTML={{ __html: grid({ ...gridData, ...gridCtaData }) }}
  />
);
