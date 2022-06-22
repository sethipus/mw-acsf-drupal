import React from 'react';
import { useEffect } from '@storybook/client-api';

import pdpNutrition from './pdp-nutrition.twig';
import pdpNutritionData from './pdp-nutrition.yml';

export default {
  title: 'Components/[ML 23] Product Detail Hero/ PDP Nutrition',
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
    nutrition_data: {
      name: 'Nutrition Info',
      description:
        'Nutritional info of the product.',
      defaultValue: { summary: 'LOREM' },
      table: {
        category: 'Text',
      },
      control: { type: 'object' },
    },
    common_content: {
      name: 'Common Contents of the block',
      description: 'Basic information of the nutrition block',
      table: {
        category: 'Text',
      },
      control: {
        type: 'object',
      },
    },
  },
};

export const pdpNutritionModuleLayout = ({ theme, nutrition_data, common_content, }) => {
  useEffect(() => Drupal.attachBehaviors(), []);
  return (
    <div
      dangerouslySetInnerHTML={{
        __html: pdpNutrition({
          ...pdpNutritionData,
          theme_styles: theme,
          pdp_nutrition_data:nutrition_data,
          pdp_common_nutrition_data:common_content
        }),
      }}
    />
  );
};
pdpNutritionModuleLayout.args = {
  theme: pdpNutritionData.theme_styles,
  common_content: pdpNutritionData.pdp_common_nutrition_data,
  nutrition_data: pdpNutritionData.pdp_nutrition_data,
};
