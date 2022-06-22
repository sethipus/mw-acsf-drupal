import React from 'react';
import { useEffect } from '@storybook/client-api';
import productFeature from './product-feature.twig';
import productFeatureData from './product-feature.yml';

import './product-feature';

export default {
  title: 'Components/[ML 06] Product Feature',
  parameters: {
    componentSubtitle: `A large component that calls attention 
                      to a specific product being showcased, 
                      driving to a Product Detail Page. The 
                      module has clear messaging and a CTA. 
                      This Module will push you out to a product 
                      detail page. It can be added to the following
                      pages - Homepage, Landing page, Product page,
                      Content hub and Campaign page.`,
  },
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
    Eyebrow: {
      name: 'Eyebrow',
      defaultValue: { summary: 'Product ABC ' },
      table: {
        category: 'Text',
      },
      description:
        'Eyebrow text for the product feature. <b>Maximum character limit is 15.</b>',
      control: { type: 'text' },
    },
    Title: {
      name: 'Title text',
      table: {
        category: 'Text',
      },
      defaultValue: { summary: 'ABC Chocolate' },
      description:
        'Title for the product feature.<b> Maximum character limit is 55.</b>',
      control: { type: 'text' },
    },
    Background: {
      name: 'Background Color',
      table: {
        category: 'Theme',
      },
      defaultValue: { summary: '#fff' },
      description: 'Background color HEX value for the product feature',
      control: { type: 'color' },
    },
    ProductImage: {
      name: 'Image Assets',
      table: {
        category: 'Image',
      },
      description:
        'Product image for the product.Dimensions <ul><b> <li> Mobile : 375px X 435px </li>  <li> Tablet : 768px X 601px </li> <li>Desktop : 2880px X 1196px </li></b></ul>',
      control: { type: 'object' },
    },
    ExploreCTA: {
      name: 'Button CTA',
      table: {
        category: 'Text',
      },
      defaultValue: { summary: 'Explore' },
      description:
        'Button CTA text for the product feature. <b>Maximum character limit is 15.</b>',
      control: { type: 'text' },
    },
  },
};

export const prodcutFeatureModule = ({
  theme,
  Eyebrow,
  Title,
  Background,
  ProductImage,
  ExploreCTA,
}) => {
  useEffect(() => Drupal.attachBehaviors(), []);
  return (
    <div
      dangerouslySetInnerHTML={{
        __html: productFeature({
          ...productFeatureData,
          theme_styles: theme,
          eyebrow_text: Eyebrow,
          storybook_product_feature_heading: Title,
          storybook_product_feature_background_color: Background,
          image_src: ProductImage,
          default_link_content: ExploreCTA,
        }),
      }}
    />
  );
};
prodcutFeatureModule.args = {
  theme: productFeatureData.theme_styles,
  Eyebrow: productFeatureData.eyebrow_text,
  Title: productFeatureData.storybook_product_feature_heading,
  Background: productFeatureData.storybook_product_feature_background_color,
  ProductImage: productFeatureData.image_src,
  ExploreCTA: productFeatureData.default_link_content,
};
