import React from 'react';
import { useEffect } from '@storybook/client-api';

import grid from './grid.twig';
import gridData from './grid.yml';
import gridff from './grid-ff.twig';
import gridffData from './grid-ff.yml';
import gridCardData from './grid-cards.yml';
import gridCtaData from './grid-ctas.yml';
import ajaxGrid from './ajax-card-grid.twig';
import ajaxGridData from './ajax-card-grid.yml';
import ajaxCardGrid from './ajaxcardgrid';

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

export const ajaxCardGridExample = () => {
  useEffect(() => Drupal.attachBehaviors(), []);
  return <div dangerouslySetInnerHTML={{ __html: ajaxGrid(ajaxGridData) }} />
};
