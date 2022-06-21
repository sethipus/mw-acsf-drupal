import React from 'react';

import freeformStory from './freeform-story.twig';
import freeformStoryData from './freeform-story-center.yml';

/**
 * Storybook Definition.
 */
export default {
  title: 'Components/[ML 02] Freeform Story Block',
  parameters: {
    componentSubtitle: `A flexible component
                       to set up a page narrative
                       through a variety of layouts,
                       supporting text and images.
                       This module comes in 3 different
                       orientations, each with an optional
                       image. The orientations are as follows: Left,
                       Right and Center. It can be dispalyed in the
                       following pages: Homepage, Landing page, About page
                       and Campaign page.`,
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
    enableBackgroundColor:{
        name:'Background Color Usage',
        table:{
            category:'Theme'
        },
        description:'Apply background color to the story',
        control:{
            type:'boolean'
        }
    },
    BackgroundColor: {
      name: 'Background Color',
      table: {
        category: 'Theme',
      },
      defaultValue: { summary: '#fff' },
      description: 'Background color of the story',
      control: { type: 'color' },
    },
    BackgroundImage: {
      name: 'Background Image',
      table: {
        category: 'Theme',
      },
      defaultValue: {
        summary:
          'http://dove.mars.acsitefactory.com/sites/g/files/fnmzdf186/files/2020-12/Dove%20Home%20Banner%2021-9.PNG',
      },
      description:
        'Background image of the story.Dimensions <ul><b> <li> Mobile : 375px X 435px </li>  <li> Tablet : 768px X 601px </li> <li>Desktop : 2880px X 1196px </li></b></ul>',
      control: { type: 'object' },
    },
    SubHeadingTitle: {
      name: 'Header 1',
      table: {
        category: 'Text',
      },
      defaultValue: { summary: 'Lorem Ipsum..' },
      description:
        'Subheading title of the story. <b>Maximum character limit is 60.</b>',
      control: { type: 'text' },
    },
    HeadingTitle: {
      name: 'Header 2',
      table: {
        category: 'Text',
      },
      defaultValue: { summary: 'Lorem..' },
      description:
        'Heading title of the story.<b>  Maximum character limit is 60 </b>.',
      control: { type: 'text' },
    },
    ContentText: {
      name: 'Body Copy',
      table: {
        category: 'Text',
      },
      defaultValue: { summary: 'Lorem..' },
      description:
        'Content of the story.<b> Maximum character limit is 1000.</b>',
      control: { type: 'text' },
    },
    Align: {
      name: 'Alignment',
      table: {
        category: 'Theme',
      },
      defaultValue: { summary: 'Left' },
      description: 'Alignemnt of the story',
      control: 'select',
      options: ['left', 'right', 'center'],
    },
  },
};

export const freeformStoryModule = ({
  theme,
  enableBackgroundColor,
  BackgroundColor,
  BackgroundImage,
  SubHeadingTitle,
  HeadingTitle,
  ContentText,
  Align,
}) => {
  return (
    <div
      dangerouslySetInnerHTML={{
        __html: freeformStory({
          ...freeformStoryData,
          theme_styles: theme,
          use_custom_color:enableBackgroundColor,
          custom_background_color: BackgroundColor,
          freeform_story_img_src: BackgroundImage,
          freeform_story_header_1: SubHeadingTitle,
          freeform_story_header_2: HeadingTitle,
          freeform_story_paragraph_content: ContentText,
          freeform_story_align: Align,
        }),
      }}
    />
  );
};
freeformStoryModule.args = {
  theme: freeformStoryData.theme_styles,
  enableBackgroundColor:freeformStoryData.use_custom_color,
  BackgroundColor: freeformStoryData.custom_background_color,
  BackgroundImage: freeformStoryData.freeform_story_img_src,
  SubHeadingTitle: freeformStoryData.freeform_story_header_1,
  HeadingTitle: freeformStoryData.freeform_story_header_2,
  ContentText: freeformStoryData.freeform_story_paragraph_content,
  Align: freeformStoryData.freeform_story_align,
};
