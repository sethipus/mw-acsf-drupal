import React from 'react';
import { useEffect } from '@storybook/client-api';
import iframe from './iframe.twig';
import iframeData from './iframe.yml';
import './iframe';

export default {
  title: 'Components/[ML 17] iFrame',
  parameters: {
    componentSubtitle: `This component loads external elements from another
   source onto a page. The component slotted in the iFrame is fitted to
   the width of the breakpoint's container. It can be added to the following page - 
   Homepage, Landing page, About page, Product detail, recipe detail and article page.`,
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
    iframe_description: {
      name: 'Source Link',
      description: 'Link for the iframe',
      table: {
        category: 'Text',
      },
      control: {
        type: 'text',
      },
    },
  },
};

export const iFrameLayout = ({ theme, iframe_description }) => {
  useEffect(() => Drupal.attachBehaviors(), []);
  return (
    <div
      dangerouslySetInnerHTML={{
        __html: iframe({
          ...iframeData,
          theme_styles: theme,
          iframe_src: iframe_description,
        }),
      }}
    />
  );
};
iFrameLayout.args = {
  theme: iframeData.theme_styles,
  iframe_description: iframeData.iframe_src,
};
