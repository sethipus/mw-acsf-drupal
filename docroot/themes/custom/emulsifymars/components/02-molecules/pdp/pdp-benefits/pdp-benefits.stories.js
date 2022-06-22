import React from 'react';
import { useEffect } from '@storybook/client-api';

import pdpbenefits from './pdp-benefits.twig';
import pdpbenefitsData from './pdp-benefits.yml';

export default {
  title: 'Components/[ML 23] Product Detail Hero/ PDP Benefits',
  argTypes: {
    benefits_data: {
      name: 'Benefit Info',
      description: 'Allergen info of the product.',
      defaultValue: { summary: 'LOREM' },
      table: {
        category: 'Text',
      },
      control: { type: 'object' },
    },
  },
};

export const pdpBenefitsModuleLayout = ({ benefits_data }) => {
  useEffect(() => Drupal.attachBehaviors(), []);
  return (
    <div
      dangerouslySetInnerHTML={{
        __html: pdpbenefits({
          ...pdpbenefitsData,
          benefits_data: benefits_data,
        }),
      }}
    />
  );
};
pdpBenefitsModuleLayout.args = {
  benefits_data: pdpbenefitsData.benefits_data,
};
