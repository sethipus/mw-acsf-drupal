import React from 'react';
import { useEffect } from '@storybook/client-api';

import '../../01-atoms/video/fullscreen-video/video';

import recipeHeroModuleTwig from './recipe-hero-module.twig';
import recipeHeroModuleVideoData from './recipe-hero-module-video.yml';
import recipeHeroModuleImageData from './recipe-hero-module-image.yml';
import recipeSocial from '../menus/social/social-menu.yml';

/**
 * Storybook Definition.
 */
export default {
  title: 'Components/[ML 24] Recipe Detail Module',
  parameters: {
    componentSubtitle:`The Recipe Detail hero is required at
    the top of all Recipe Detail pages. It
    can be displayed in recipe detail page
    and campaign page.`,
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
    LabelContent: {
      name: 'Label text',
      table: {
        category: 'Text',
      },
      defaultValue: { summary: 'Lorem Ipsum..' },
      description: 'Change the label text of the recipe.<b>Maximum character limit is 60.</b>',
      control: 'text',
    },
    backgroundColorEnable: {
      name: 'Enable Background Color',
      table: {
        category: 'Theme',
      },
      description: 'Enable indicator for background color.',
      control: { type: 'boolean' },
    },
    backgroundColor: {
      name: 'Background Color',
      table: {
        category: 'Theme',
      },
      description: 'Change the background color of the recipe',
      control: { type: 'color' },
    },
    RecipeDescription: {
      name: 'Recipe Description text',
      table: {
        category: 'Text',
      },
      defaultValue: { summary: 'Lorem Ipsum...' },
      description: 'Change the description of the recipe',
      control: 'text',
    },
    CookingTime: {
      name: 'Cooking time',
      table: {
        category: 'Text',
      },
      defaultValue: { summary: '23mins' },
      description: 'Change the cooking time of the recipe.',
      control: 'text',
    },
    NumberOfIngridents: {
      name: 'Ingridents required',
      table: {
        category: 'Text',
      },
      defaultValue: { summary: '12' },
      description: 'Number of ingridents required for the recipe.',
      control: 'text',
    },
    NumberOfServings: {
      name: 'No. of Servings',
      table: {
        category: 'Text',
      },
      defaultValue: { summary: '10' },
      description: 'Number of serving plates possible for the recipe.',
      control: 'text',
    },
    video:{
      name:'Video Enable Indicator',
      table: {
        category: 'Theme',
      },
      description:'Enable the video instead of image',
      control:{
        type:'boolean'
      }
    },
    images: {
      name: 'Background Image',
      table: {
        category: 'Theme',
      },
      description:
        'Change the background Image of the recipe module.Dimensions <ul><b> <li> Mobile : 375px X 435px </li>  <li> Tablet : 768px X 601px </li> <li>Desktop : 2880px X 1196px </li></b></ul>',
      defaultValue: {
        summary:
          'http://dove.mars.acsitefactory.com/sites/g/files/fnmzdf186/files/2020-12/Dove%20Home%20Banner%2021-9.PNG',
      },
      control: { type: 'object' },
    },
  },
};

export const recipeHeroModule = ({
  theme,
  LabelContent,
  CookingTime,
  NumberOfIngridents,
  NumberOfServings,
  RecipeDescription,
  backgroundColorEnable,
  backgroundColor,
  video,
  images,
}) => {
  useEffect(() => Drupal.attachBehaviors(), []);
  return (
    <div
      dangerouslySetInnerHTML={{
        __html: recipeHeroModuleTwig({
          ...recipeHeroModuleVideoData,
          ...recipeSocial,
          theme_styles: theme,
          background_color_override:backgroundColorEnable,
          background_color: backgroundColor,
          recipe_header_text: LabelContent,
          recipe_cooking_time: CookingTime,
          recipe_ingredients_number: NumberOfIngridents,
          recipe_number_of_servings: NumberOfServings,
          recipe_description_text: RecipeDescription,
          videoEnableIndicator: video,
          images,
        }),
      }}
    />
  );
};
recipeHeroModule.args = {
  theme: recipeHeroModuleVideoData.theme_styles,
  backgroundColorEnable:recipeHeroModuleVideoData.background_color_override,
  backgroundColor: recipeHeroModuleVideoData.background_color,
  LabelContent: recipeHeroModuleVideoData.recipe_header_text,
  CookingTime: recipeHeroModuleVideoData.recipe_cooking_time,
  NumberOfIngridents: recipeHeroModuleVideoData.recipe_ingredients_number,
  NumberOfServings: recipeHeroModuleVideoData.recipe_number_of_servings,
  RecipeDescription: recipeHeroModuleVideoData.recipe_description_text,
  video: recipeHeroModuleVideoData.videoEnableIndicator,
  images: recipeHeroModuleVideoData.images,
};
