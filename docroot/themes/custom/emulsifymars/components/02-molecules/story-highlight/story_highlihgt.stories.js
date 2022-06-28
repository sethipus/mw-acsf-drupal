import React from 'react';
import storyHighlight from './story_highlight.twig';
import storyHighlightData from './story_highlight.yml';
import { useEffect } from '@storybook/client-api';

/**
 * Storybook Definition.
 */
export default {
  title: 'Components/[ML 04] Story Highlight',
  parameters: {
    componentSubtitle: `A module meant for telling a single,
      contained story which supports images, video, and text.
      It can be displayed in the following pages - Homepage,
      Landing page, About page, Product detail, Recipe detail,
      Article page and Campaign page.`,
  },
  argTypes:{
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
      name: 'Title text',
      description: 'Title of the story. <b>Maximum character limit is 55.</b>',
      defaultValue: { summary: 'Dog Foods' },
      table: { category: 'Text' },
      control: { type: 'text' },
    },
    ParagraphContent: {
      name: 'Paragraph text',
      description: 'Paragraph of the story.<b> Maximum character limit is 255.</b>',
      defaultValue: { summary: 'lorem ipsum...' },
      table: { category: 'Text' },
      control: { type: 'text' },
    },
    ButtonCTA: {
      name: 'Button CTA',
      description: 'Button CTA of the button.<b> Maximum character limit is 15.</b>',
      defaultValue: { summary: 'EXPLORE' },
      table: { category: 'Text' },
      control: { type: 'text' },
    },
    ImageAsset1: {
      name:'Image asset 1',
      description: 'Change the first image of the story. Dimensions <ul><b> <li> Mobile : 375px X 435px </li>  <li> Tablet : 768px X 601px </li> <li>Desktop : 2880px X 1196px </li></b></ul><b>Story item title maximum character limit is 300.</b> ',
      table: { category: 'Images' },
      control: { type: 'object' },
    },
    ImageAsset2: {
      name:'Image asset 2',
      description: 'Change the second image of the story. Dimensions <ul><b> <li> Mobile : 375px X 435px </li>  <li> Tablet : 768px X 601px </li> <li>Desktop : 2880px X 1196px </li></b></ul><b>Story item title maximum character limit is 300.</b> ',
      table: { category: 'Images' },
      control: { type: 'object' },
    },
    ImageAsset3: {
      name:'Image asset 3',
      description: 'Change the third image of the story. Dimensions <ul><b> <li> Mobile : 375px X 435px </li>  <li> Tablet : 768px X 601px </li> <li>Desktop : 2880px X 1196px </li></b></ul><b>Story item title maximum character limit is 300.</b> ',
      table: { category: 'Images' },
      control: { type: 'object' },
    },
    items: {
      name: 'Stories List',
      description: 'Change layout of story1.Dimensions <ul><b> <li> Mobile : 375px X 435px </li>  <li> Tablet : 768px X 601px </li> <li>Desktop : 2880px X 1196px </li></b></ul><b>Story item title maximum character limit is 300.</b> ',
      table: { category: 'Stories' },
      control: { type: 'object' },
    },
  }
};

export const storyHighlightModule = ({
  theme,
  Title,
  ParagraphContent,
  ButtonCTA,
  ImageAsset1,
  ImageAsset2,
  ImageAsset3,
  items,
}) => {
  useEffect(() => Drupal.attachBehaviors(), []);
  return (
    <div
      dangerouslySetInnerHTML={{
        __html:
          "<div style='height: 300px; background-color: grey'></div>" +
          storyHighlight({
            ...storyHighlightData,
            theme_styles:theme,
            heading: Title,
            story_highlight_paragraph_content: ParagraphContent,
            story_highlight_button_text: ButtonCTA,
            asset_1: ImageAsset1,
            asset_2: ImageAsset2,
            asset_3:ImageAsset3,
            storybook_story_highlight_items: items,
          }),
      }}
    />
  );
};
storyHighlightModule.args = {
  theme:storyHighlightData.theme_styles,
  Title: storyHighlightData.heading,
  ParagraphContent: storyHighlightData.story_highlight_paragraph_content,
  ButtonCTA: storyHighlightData.story_highlight_button_text,
  ImageAsset1: storyHighlightData.asset_1,
  ImageAsset2: storyHighlightData.asset_2,
  ImageAsset3: storyHighlightData.asset_3,
  items: storyHighlightData.storybook_story_highlight_items,
};