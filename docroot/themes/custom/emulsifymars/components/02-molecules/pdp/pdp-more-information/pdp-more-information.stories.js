import React from 'react';
import { useEffect } from '@storybook/client-api';

import pdpMoreInfo from './pdp-more-information.twig';
import pdpMoreInfoData from './pdp-more-information.yml';

export default {
  title: 'Components/[ML 23] Product Detail Hero/ PDP More Information',
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
    additional_data: {
      name: 'Benefit Info',
      description: 'Additional info of the product.',
      defaultValue: { summary: 'LOREM' },
      table: {
        category: 'Text',
      },
      control: { type: 'object' },
    },
  },
};

export const pdpBenefitsModuleLayout = ({ theme, additional_data }) => {
  useEffect(() => Drupal.attachBehaviors(), []);
  return (
    <div
      dangerouslySetInnerHTML={{
        __html: pdpMoreInfo({
          ...pdpMoreInfoData,
          theme_styles: theme,
          pdp_common_more_information_data: additional_data,
        }),
      }}
    />
  );
};
pdpBenefitsModuleLayout.args = {
  theme: pdpMoreInfoData.theme_styles,
  additional_data: pdpMoreInfoData.pdp_common_more_information_data,
};
