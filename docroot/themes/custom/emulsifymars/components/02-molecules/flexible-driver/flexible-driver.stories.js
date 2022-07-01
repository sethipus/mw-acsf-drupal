import React from 'react';

import flexibleDriver from './flexible-driver.twig';
import flexibleDriverData from './flexible-driver.yml';

/**
 * Storybook Definition.
 */
export default {
  title: 'Components/[ML 08] Flexible Driver',
  parameters: {
    componentSubtitle: `This module can be used as a way to break
       up the page and drive to off-site or on-site
       content. Dissimilarly to the Content Feature,
       this module does not feature a background image,
       and is meant to act as a smaller and more compact
       driver to content. It can be displayed in the following
       pages - Homepage, Landing page, About page, product hub,
       Content hub, Product detail, Recipe detail, article and
       Campaign page.`,
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
    Title: {
      name: 'Title',
      description:
        'Title of the content.<b> Maximum character limit is 65.</b>',
      defaultValue: { summary: 'title' },
      table: {
        category: 'Text',
      },
      control: { type: 'text' },
    },
    Description: {
      name: 'Content description',
      description:
        'Description of the content. <b>Maximum character limit is 160.</b>',
      defaultValue: { summary: 'lorem pisum..' },
      table: {
        category: 'Text',
      },
      control: { type: 'text' },
    },
    ButtonCTA: {
      name: 'Button CTA',
      description:
        'Button CTA of the content.<b> Maximum character limit is 15.</b>',
      table: {
        category: 'Text',
      },
      control: { type: 'text' },
    },
    LeftImage: {
      name: 'Left Image',
      defaultValue: { summary: 'https://picsum.photos/200' },
      description:
        'Left side image of the content( Web Images are only applicable). Dimensions <ul><b> <li> Mobile : 375px X 435px </li>  <li> Tablet : 768px X 601px </li> <li>Desktop : 2880px X 1196px </li></b></ul>',
      table: {
        category: 'Images',
      },
      control: { type: 'object' },
    },
    RightImage: {
      name: 'Right Image',
      defaultValue: { summary: 'https://picsum.photos/200' },
      description:
        'Right side image of the content( Web Images are only applicable). Dimensions <ul><b> <li> Mobile : 375px X 435px </li>  <li> Tablet : 768px X 601px </li> <li>Desktop : 2880px X 1196px </li></b></ul>',
      table: {
        category: 'Images',
      },
      control: { type: 'object' },
    },
    
  },
};

export const flexibleDriverComponent = ({
  theme,
  Title,
  Description,
  ButtonCTA,
  LeftImage,
  RightImage,
}) => {
  return (
    <div
      dangerouslySetInnerHTML={{
        __html: flexibleDriver({
          ...flexibleDriverData,
          theme_styles: theme,
          flexible_driver_heading: Title,
          flexible_driver_text: Description,
          flexible_driver_button_text: ButtonCTA,
          storybook_flexible_driver_asset_1: LeftImage,
          storybook_flexible_driver_asset_2: RightImage,
        }),
      }}
    />
  );
};
flexibleDriverComponent.args = {
  theme: flexibleDriverData.theme_styles,
  Title: flexibleDriverData.flexible_driver_heading,
  Description: flexibleDriverData.flexible_driver_text,
  ButtonCTA: flexibleDriverData.flexible_driver_button_text,
  LeftImage: flexibleDriverData.storybook_flexible_driver_asset_1,
  RightImage: flexibleDriverData.storybook_flexible_driver_asset_2,
};
