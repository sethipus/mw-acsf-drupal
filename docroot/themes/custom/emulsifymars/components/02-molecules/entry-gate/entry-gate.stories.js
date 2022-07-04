import React from 'react';
import entryGate from './entry-gate.twig';
import entryGateData from './entry-gate.yml';
import { useEffect } from '@storybook/client-api';
import './entry-gate';

export default {
  title: 'Components/[GE 06] Entry Gate',
  parameters: {
    componentSubtitle: `Required for select markets, this overlay will appear
     as soon as the consumer comes to the site, requesting they enter their
      date of birth to continue, is configurable per page (e.g. within the
      website there can be pages without entry gate)`,
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
      name: 'Title',
      description: 'Title of the entry gate. <b>Max CC: 55</b>',
      table: {
        category: 'Text',
      },
      control: {
        type: 'text',
      },
    },
    paragraph: {
      name: 'Description',
      description: 'Description of the entry gate. <b>Max CC: 150</b>',
      table: {
        category: 'Text',
      },
      control: {
        type: 'text',
      },
    },
    eyebrowtext: {
      name: 'Heading',
      description: 'Heading of the entry gate. <b>Max CC: 45</b>',
      table: {
        category: 'Text',
      },
      control: {
        type: 'text',
      },
    },
    marketing_message: {
      name: 'Marketing Messaging',
      description: 'Marketing Messaging of the entry gate. <b>Max CC: 150</b>',
      table: {
        category: 'Text',
      },
      control: {
        type: 'text',
      },
    },
    backgroundColor: {
      name: 'Background Color',
      table: {
        category: 'Theme',
      },
      control: {
        type: 'color',
      },
    },
  },
};

export const entryGateExample = ({
  theme,
  heading,
  paragraph,
  backgroundColor,
  eyebrowtext,
  marketing_message
}) => {
  useEffect(() => Drupal.attachBehaviors(), []);
  return (
    <div
      dangerouslySetInnerHTML={{
        __html: entryGate({
          ...entryGateData,
          theme_styles:theme,
          entry_gate_heading: heading,
          entry_gate_paragraph_content: paragraph,
          storybook_entry_gate_background_color: backgroundColor,
          entry_gate_eyebrow_text: eyebrowtext,
          entry_gate_bottom_paragraph_content:marketing_message
        }),
      }}
    />
  );
};
entryGateExample.args = {
  theme:entryGateData.theme_styles,
  heading: entryGateData.entry_gate_heading,
  paragraph: entryGateData.entry_gate_paragraph_content,
  backgroundColor: entryGateData.storybook_entry_gate_background_color,
  eyebrowtext:entryGateData.entry_gate_eyebrow_text,
  marketing_message: entryGateData.entry_gate_bottom_paragraph_content
};
