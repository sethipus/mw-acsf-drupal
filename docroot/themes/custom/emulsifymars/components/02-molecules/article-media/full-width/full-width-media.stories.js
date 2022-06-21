import React from 'react';

import fullWidthMediaTwig from './full-width-media.twig';
import fullWidthMediaData from './full-width-media.yml';

export default {
  title: 'Components/[ML 11][ML 12] Inline & Breakout Media',
  parameters: {
    componentSubtitle: `Static Inline images (landscape, square, portrait)
      Large images that breakout of the body copy width (landscape, square)
      Full width parallaxing images that open when scrolled into the viewport.
      It can be added to the following pages - homepage landing page, about page,
      produce detail page, recipe detail page, article and campaign page.`,
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
    heading: {
      name: ' Title',
      description: 'Title for the media. <b> Maximum character limit is 55</b>',
      table: {
        category: 'Text',
      },
      control: {
        type: 'text',
      },
    },
    media: {
      name: ' Media',
      description: `<ul><li> For video media, the video flag needs to be set as ,<i>true</i>
         with the src flag as the source path of the video.</li> <li>For parallax image media,
         the parallax_image flag needs to be set as ,<i>true</i>
         with the src flag as the image link.(When parallax image is set as false the heading
         will be visible)</li><li>For image media, the image flag needs to be set as ,<i>true</i>
         with the src flag as the source path of the image.</li></ul>`,
      table: {
        category: 'Text',
      },
      control: {
        type: 'object',
      },
    },
    content: {
      name: ' Content',
      description: `<b>Content description maximum character limit is 300</b>`,
      table: {
        category: 'Text',
      },
      control: {
        type: 'text',
      },
    },
  },
};

export const InlineBreakoutMedia = ({ theme, heading, media, content }) => {
  return (
    <div
      dangerouslySetInnerHTML={{
        __html: fullWidthMediaTwig({
          ...fullWidthMediaData,
          theme_styles: theme,
          heading: heading,
          media: media,
          content: content,
        }),
      }}
    />
  );
};
InlineBreakoutMedia.args = {
  theme: fullWidthMediaData.theme_styles,
  heading: fullWidthMediaData.heading,
  media: fullWidthMediaData.media,
  content: fullWidthMediaData.content,
};
