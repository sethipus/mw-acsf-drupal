import React from 'react';
import contactModule from './contact-module.twig';
import contactModuleData from './contact-module.yml';

export default {
  title: 'Components/[ML 26] Contact Help Banner',
  parameters: {
    componentSubtitle: `Provides a quick and easy way to contact the brand
                     and corporate without having to visit the Contact t& Help page.
                    Contact information on banner includes phone number,
                    email, and social channels with a connection to Mars corporate. It
                    can be added to the following page - About, Product detail, recipe detail, article, 
                    contact & help, campaign page.`,
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
      description: 'Title for the contact module',
      defaultValue: {
        summary: 'Lorem ipsum..',
      },
      table: {
        category: 'Text',
      },
      control: {
        type: 'text',
      },
    },
    Description: {
      name: 'Description',
      description: 'Description for the contact module',
      defaultValue: {
        summary: 'Lorem ipsum..',
      },
      table: {
        category: 'Text',
      },
      control: {
        type: 'text',
      },
    },
    callCTA: {
      name: 'Call CTA',
      description: 'Call number for the contact module',
      defaultValue: {
        summary: 'Lorem ipsum..',
      },
      table: {
        category: 'Text',
      },
      control: {
        type: 'text',
      },
    },
    emailCTA: {
      name: 'Email CTA',
      description: 'Email for the contact module',
      defaultValue: {
        summary: 'Lorem ipsum..',
      },
      table: {
        category: 'Text',
      },
      control: {
        type: 'text',
      },
    },
  },
};

export const contactModuleLayout = ({
  theme,
  Title,
  Description,
  callCTA,
  emailCTA,
}) => {
  return (
    <div
      dangerouslySetInnerHTML={{
        __html: contactModule({
          ...contactModuleData,
          theme_styles: theme,
          contact_module_heading: Title,
          contact_module_paragraph_content: Description,
          contact_module_contact_phone: callCTA,
          contact_module_contact_email_text: emailCTA,
        }),
      }}
    />
  );
};
contactModuleLayout.args = {
  theme: contactModuleData.theme_styles,
  Title: contactModuleData.contact_module_heading,
  Description: contactModuleData.contact_module_paragraph_content,
  callCTA: contactModuleData.contact_module_contact_phone,
  emailCTA: contactModuleData.contact_module_contact_email_text,
};
