import React from 'react';
import { useEffect } from '@storybook/client-api';

import pdpCooking from './pdp-cooking.twig';
import pdpCookingData from './pdp-cooking.yml';

export default {
  title: 'Components/[ML 23] Product Detail Hero/ PDP Cooking',
  argTypes: {
    theme: {
      name: 'Theme',
      description: 'Theme for the card',
      defaultValue: {
        summary: 'Twix',
      },
      table: {
        category: 'Theme',
      },
      control: {
        type: 'select',
        options: ['twix', 'dove', 'mars', 'galaxy'],
      },
    },
    cooking_data: {
      name: 'Cooking Info',
      description: 'Allergen info of the product.',
      defaultValue: { summary: 'LOREM' },
      table: {
        category: 'Text',
      },
      control: { type: 'object' },
    },
    common_data: {
      name: 'Common Contents of the block',
      description: 'Basic information of the cooking block',
      table: {
        category: 'Text',
      },
      control: {
        type: 'object',
      },
    },
  },
};

export const pdpCookingModuleLayout = ({
  theme,
  cooking_data,
  common_data,
}) => {
  useEffect(() => Drupal.attachBehaviors(), []);
  return (
    <div
      dangerouslySetInnerHTML={{
        __html: pdpCooking({
          ...pdpCookingData,
          theme_styles: theme,
          pdp_cooking_data: cooking_data,
          pdp_common_cooking_data: common_data,
        }),
      }}
    />
  );
};
pdpCookingModuleLayout.args = {
  theme: pdpCookingData.theme_styles,
  cooking_data: pdpCookingData.pdp_cooking_data,
  common_data: pdpCookingData.pdp_common_cooking_data,
};
