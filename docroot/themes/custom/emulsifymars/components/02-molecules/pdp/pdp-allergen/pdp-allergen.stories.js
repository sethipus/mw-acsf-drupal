import React from 'react';
import { useEffect } from '@storybook/client-api';

import pdpAllergens from './pdp-allergen.twig';
import pdpAllergensData from './pdp-allergen.yml';

export default {
  title: 'Components/[ML 23] Product Detail Hero/ PDP Allergens',
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
    allergen_data: {
      name: 'Allergen Info',
      description: 'Allergen info of the product.',
      defaultValue: { summary: 'LOREM' },
      table: {
        category: 'Text',
      },
      control: { type: 'object' },
    },
    common_data: {
      name: 'Common Contents of the block',
      description: 'Basic information of the allergens block',
      table: {
        category: 'Text',
      },
      control: {
        type: 'object',
      },
    },
  },
};

export const pdpNutritionModuleLayout = ({
  theme,
  allergen_data,
  common_data,
}) => {
  useEffect(() => Drupal.attachBehaviors(), []);
  return (
    <div
      dangerouslySetInnerHTML={{
        __html: pdpAllergens({
          ...pdpAllergensData,
          theme_styles: theme,
          pdp_allergen_data: allergen_data,
          pdp_common_allergen_data: common_data,
        }),
      }}
    />
  );
};
pdpNutritionModuleLayout.args = {
  theme: pdpAllergensData.theme_styles,
  allergen_data: pdpAllergensData.pdp_allergen_data,
  common_data: pdpAllergensData.pdp_common_allergen_data,
};
