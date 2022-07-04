import React from 'react';
import { useEffect } from '@storybook/client-api';

import '../../01-atoms/video/fullscreen-video/video';

import recipeFeatureModuleTwig from './recipe-feature-module.twig';
import recipeFeatureModuleData from './recipe-feature-module.yml';

/**
 * Storybook Definition.
 */
export default {
  title: 'Components/[ML 05] Recipe Feature',
  parameters: {
    componentSubtitle:
      `This module highlights a single recipe
       and drives to a Recipe Detail Page. It
        can be added to the following pages -
        Homepage, Landing page, Content hub,
        Product detail and campaign page.`,
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
    recipe_Eyebrow: {
      name: 'Eyebrow',
      defaultValue: { summary: 'Recipe' },
      table: {
        category: 'Text',
      },
      description: 'Eyebrow text for the recipe feature.<b> Maximum character limit is 15.</b>',
      control: { type: 'text' },
    },
    recipe_Title: {
      name: 'Recipe title',
      defaultValue: { summary: 'Product ABC ' },
      table: {
        category: 'Text',
      },
      description: 'Recipe title for the recipe feature.<b> Maximum character limit is 60.</b>',
      control: { type: 'text' },
    },
    recipe_cta: {
      name: 'Button CTA',
      defaultValue: { summary: 'SEE DETAILS ' },
      table: {
        category: 'Text',
      },
      description: 'Button CTA for the recipe feature button.<b> Maximum character limit is 15.</b>',
      control: { type: 'object' },
    },
    recipe_media: {
      name: 'Recipe Image',
      defaultValue: {
        summary:
          'http://dove.mars.acsitefactory.com/sites/g/files/fnmzdf186/files/2020-12/Dove%20Home%20Banner%2021-9.PNG',
      },
      table: {
        category: 'Image',
      },
      description: 'Recipe image for the recipe.Dimensions <ul><b> <li> Mobile : 375px X 435px </li>  <li> Tablet : 768px X 601px </li> <li>Desktop : 2880px X 1196px </li></b></ul>',
      control: { type: 'object' },
    },
    recipe_block_title: {
      name: 'Block Title',
      table: {
        category: 'Text',
      },
      description: 'Block title for the recipe feature.<b> Maximum character limit is 15.</b>',
      control: { type: 'text' },
    },
    graphic_divider:{
      name: 'Graphic Divider',
      table: {
        category: 'Theme',
      },
      description: 'Graphic divider for the recipe feature',
      control: { type: 'text' },
    },
  },
};

export const recipeFeatureModule = ({
  theme,
  recipe_Eyebrow,
  recipe_Title,
  recipe_cta,
  recipe_media,
  recipe_block_title,
  graphic_divider
}) => {
  return (
    <div
      dangerouslySetInnerHTML={{
        __html: recipeFeatureModuleTwig({
          ...recipeFeatureModuleData,
          theme_styles:theme,
          eyebrow: recipe_Eyebrow,
          title: recipe_Title,
          cta:recipe_cta,
          recipe_media:recipe_media,
          block_title:recipe_block_title,
          graphic_divider:graphic_divider
        }),
      }}
    />
  );
};
recipeFeatureModule.args = {
  theme:recipeFeatureModuleData.theme_styles,
  recipe_block_title:recipeFeatureModuleData.block_title,
  graphic_divider:recipeFeatureModuleData.graphic_divider,
  recipe_Eyebrow: recipeFeatureModuleData.eyebrow,
  recipe_Title: recipeFeatureModuleData.title,
  recipe_cta: recipeFeatureModuleData.cta,
  recipe_media: recipeFeatureModuleData.recipe_media,
};
