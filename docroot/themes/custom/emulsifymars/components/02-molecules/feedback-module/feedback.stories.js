import React from 'react';
import { useEffect } from '@storybook/client-api';

import feedback from './feedback.twig';
import feedbackPositive from './feedback-positive.twig';
import feedbackNegative from './feedback-negative.twig';
import feedbackData from './feedback.yml';

import './feedback';

export default {
  title: 'Components/[ML 29] Feedback Module',
  parameters: {
    componentSubtitle: `This module serves as a customer service function,
     allowing users to submit feedback or contact a brand to provide additional
     information. It can be added to the following pages - Landing page, product hub,
     content hub, product detail, recipe detail, article, contact & help, campaign page,
     where to buy, newsletter and Search results page.`,
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
    brandShape: {
      name: 'Brand Shape',
      description: 'SVG for the respective brand can be added.',
      table: {
        category: 'Theme',
      },
      control: {
        type: 'text',
      },
    },
    description:{
      name:'Description',
      description:'Text content for the feedback module',
      table:{
        category:'Text'
      },
      control:{
        type:'text'
      }
    },
    standardHeading: {
      name: 'Standard Heading',
      description:
        'Only applicable to ✅Standard ❌Positive Feedback ❌Negative Feedback. <b>Maximum CC is 25.</b>',
      table: {
        category: 'Text',
      },
      control: {
        type: 'text',
      },
    },
    postitiveHeading: {
      name: 'Positive Heading',
      description:
        'Only applicable to ❌Standard ✅Positive Feedback ❌Negative Feedback. <b>Maximum CC is 25.</b>',
      table: {
        category: 'Text',
      },
      control: {
        type: 'text',
      },
    },
    negetiveHeading: {
      name: 'Standard Heading',
      description:
        'Only applicable to ❌Standard ❌Positive Feedback ✅Negative Feedback. <b>Maximum CC is 25.</b>',
      table: {
        category: 'Text',
      },
      control: {
        type: 'text',
      },
    },
    standardChoices: {
      name: 'Choose Option CTA',
      description: 'Options can be changed or removed as per the requirement',
      table: {
        category: 'Text',
      },
      control: {
        type: 'object',
      },
    },
    positiveSVG:{
      name:'SVG Icon(for positive)',
      description:'SVG for the positive feedback layout',
      table: {
        category: 'Text',
      },
      control: {
        type: 'text',
      },
    },
    positiveImageAsset:{
      name:'Image Asset',
      description:'Image for the positive feedback layout',
      table: {
        category: 'Theme',
      },
      control: {
        type: 'object',
      },
    },
    negetiveSvg:{
      name:'SVG Icon(for negetive)',
      description:'SVG for the negetive feedback layout',
      table: {
        category: 'Text',
      },
      control: {
        type: 'text',
      },
    }
  },
};

export const feedbackStandardLayout = ({
  theme,
  brandShape,
  description,
  standardHeading,
  standardChoices,
}) => {
  useEffect(() => Drupal.attachBehaviors(), []);
  return (
    <div
      dangerouslySetInnerHTML={{
        __html: feedback({
          ...feedbackData,
          theme_styles: theme,
          brand_shape: brandShape,
          feedback_paragraph_content:description,
          feedback_heading: standardHeading,
          choices: standardChoices,
        }),
      }}
    />
  );
};
feedbackStandardLayout.args = {
  theme: feedbackData.theme_styles,
  brandShape: feedbackData.brand_shape,
  description: feedbackData.feedback_paragraph_content,
  standardHeading: feedbackData.feedback_heading,
  standardChoices: feedbackData.choices,
  postitiveHeading:'Not applicable to this layout',
  negetiveHeading:'Not applicable to this layout',
  positiveSVG:'Not applicable to this layout',
  positiveImageAsset:'Not applicable to this layout',
  negetiveSvg:'Not applicable to this layout',

};
export const feedbackPositiveStateayout = ({
  theme,
  brandShape,
  description,
  postitiveHeading,
  positiveSVG,
  positiveImageAsset
}) => {
  return (
    <div
      dangerouslySetInnerHTML={{
        __html: feedbackPositive({
          ...feedbackData,
          theme_styles: theme,
          brand_shape: brandShape,
          feedback_paragraph_content:description,
          feedback_positive_heading: postitiveHeading,
          tick:positiveSVG,
          polling_png_asset:positiveImageAsset
        }),
      }}
    />
  );
};
feedbackPositiveStateayout.args = {
  theme: feedbackData.theme_styles,
  brandShape: feedbackData.brand_shape,
  description: feedbackData.feedback_paragraph_content,
  postitiveHeading: feedbackData.feedback_positive_heading,
  standardHeading:'Not applicable to this layout',
  negetiveHeading:'Not applicable to this layout',
  standardChoices: 'Not applicable to this layout',
  positiveSVG:feedbackData.tick,
  positiveImageAsset:feedbackData.polling_png_asset,
  negetiveSvg:'Not applicable to this layout',
};
export const feedbackNegativeStateayout = ({
  theme,
  brandShape,
  description,
  negetiveHeading,
  negetiveSvg
}) => {
  return (
    <div
      dangerouslySetInnerHTML={{
        __html: feedbackNegative({
          ...feedbackData,
          theme_styles: theme,
          brand_shape: brandShape,
          feedback_paragraph_content:description,
          feedback_negative_heading: negetiveHeading,
          negative_shape:negetiveSvg
        }),
      }}
    />
  );
};
feedbackNegativeStateayout.args = {
  theme: feedbackData.theme_styles,
  brandShape: feedbackData.brand_shape,
  description: feedbackData.feedback_paragraph_content,
  negetiveHeading:feedbackData.feedback_negative_heading,
  standardHeading:'Not applicable to this layout',
  postitiveHeading:'Not applicable to this layout',
  standardChoices: 'Not applicable to this layout',
  positiveSVG:'Not applicable to this layout',
  positiveImageAsset:'Not applicable to this layout',
  negetiveSvg:feedbackData.negative_shape
};
