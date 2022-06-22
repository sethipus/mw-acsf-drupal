import React from 'react';
import { useEffect } from '@storybook/client-api';
import contentFeature from './content-feature.twig';
import contentFeatureData from './content-feature.yml';

import './content-feature';

export default {
  title: 'Components/[ML 07] Content Feature',
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
      description: 'Eyebrow of the content.<b> Maximum character limit is 15. </b>',
      defaultValue: { summary: 'INITIATIVE' },
      table: {
        category: 'Text',
      },
      control: { type: 'text' },
    },

    Title: {
      name: 'Title',
      description: 'Title of the content. <b>Maximum character limit is 55.</b>',
      defaultValue: { summary: 'Title..' },
      table: {
        category: 'Text',
      },
      control: { type: 'text' },
    },

    background_images: {
      name: 'Background Image',
      description: 'Background Image of the content.Dimensions <ul><b> <li> Mobile : 375px X 435px </li>  <li> Tablet : 768px X 601px </li> <li>Desktop : 2880px X 1196px </li></b></ul>',
      defaultValue: {
        summary:
          'http://dove.mars.acsitefactory.com/sites/g/files/fnmzdf186/files/2020-12/Dove%20Home%20Banner%2021-9.PNG',
      },
      table: {
        category: 'Image',
      },
      control: { type: 'object' },
    },

    Description: {
      name: 'Feature Description',
      description: 'Description of the content. <b>Maximum character limit is 300.</b>',
      defaultValue: { summary: 'lorem pisum..' },
      table: {
        category: 'Text',
      },
      control: { type: 'text' },
    },

    ExploreCTA: {
      name: 'Button CTA',
      description: 'Button text. <b>Maximum character limit is 15.</b>',
      defaultValue: { summary: 'Submit' },
      table: {
        category: 'Text',
      },
      control: { type: 'text' },
    },
  },
  parameters:{
    componentSubtitle:'A large component that calls attention to a specific content being showcased, driving to an Article Page. The module has clear messaging and a CTA. This module can only be used to highlight articles. It can added in the following pages - Homepage, Landing page, About page, Product Hub, Content Hub, Article, Campaign Page.'
  }
};

export const ContentFeatureLayout = ({
  theme,
  Eyebrow,
  Title,
  background_images,
  Description,
  ExploreCTA,
}) => {
  useEffect(() => Drupal.attachBehaviors(), []);
  return (
    <div
      dangerouslySetInnerHTML={{
        __html: contentFeature({
          ...contentFeatureData,
          theme_styles:theme,
          eyebrow_text: Eyebrow,
          storybook_content_feature_heading: Title,
          paragraph_content: Description,
          default_link_content: ExploreCTA,
          background_images:background_images,
        }),
      }}
    />
  );
};

ContentFeatureLayout.args = {
  theme: contentFeatureData.theme_styles,
  Eyebrow: contentFeatureData.eyebrow_text,
  Title: contentFeatureData.storybook_content_feature_heading,
  Description: contentFeatureData.paragraph_content,
  background_images: contentFeatureData.background_images,
  ExploreCTA: contentFeatureData.default_link_content,
};
