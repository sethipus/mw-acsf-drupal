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
    Eyebrow: {
      name: 'Eyebrow',
      defaultValue: { summary: 'Recipe' },
      table: {
        category: 'Text',
      },
      description: 'Eyebrow text for the recipe feature.<b> Maximum character limit is 15.</b>',
      control: { type: 'text' },
    },
    RecipeTitle: {
      name: 'Recipe title',
      defaultValue: { summary: 'Product ABC ' },
      table: {
        category: 'Text',
      },
      description: 'Recipe title for the recipe feature.<b> Maximum character limit is 60.</b>',
      control: { type: 'text' },
    },
    cta: {
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
  },
};

export const recipeFeatureModule = ({
  theme,
  Eyebrow,
  RecipeTitle,
  cta,
  recipe_media,
}) => {
  return (
    <div
      dangerouslySetInnerHTML={{
        __html: recipeFeatureModuleTwig({
          ...recipeFeatureModuleData,
          theme_styles:theme,
          eyebrow: Eyebrow,
          title: RecipeTitle,
          cta:cta,
          recipe_media:recipe_media,
        }),
      }}
    />
  );
};
recipeFeatureModule.args = {
  theme:recipeFeatureModuleData.theme_styles,
  Eyebrow: recipeFeatureModuleData.eyebrow,
  RecipeTitle: recipeFeatureModuleData.title,
  cta: recipeFeatureModuleData.cta,
  recipe_media: recipeFeatureModuleData.recipe_media,
};
