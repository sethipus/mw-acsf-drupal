import React from 'react';

import newsLetterForm from './newsletter-form/newsletter-form.twig';
import newsLetterFormData from './newsletter-form/newsletter-form.yml';

import newsLetterEmailForm from './newsletter-email-form/newsletter-email-form.twig';
import newsLetterEmailFormData from './newsletter-email-form/newsletter-email-form.yml';
import './newsletter-form/newsletter-form';
/**
 * Storybook Definition.
 */
export default {
  title: 'Components/[ML 33] Newsletter form',
  parameters: {
    componentSubtitle: `This module gives users a chance to sign up for the brand's Newsletter (if available) when user on the Newsletter Sign Up page. Newsletter Signup is connected with CDP
  This also serves as a lead generation element. It can be only added to campaign page.`,
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
    backgroundColor:{
      name:'Background Color',
      description:'Background Color for the form',
      table:{
        category:'Theme'
      },
      control:{
        type:'color'
      }
    },
    title:{
      name:'Title',
      description:'Title of the form. <b> Maximum character limit is 55 </b>',
      table:{
        category:'Text'
      },
      control:{
        type:'text'
      }
    },
    formInput:{
      name:'Input Types',
      description:'Input types for the form. <ul> <li> For text input feild - the type will be <b>text</b> </li><li>For email address input feild - the type will be <b>email</b></li><li>For phone number inut feild - the type will be <b>number</b></li> For more such input type feilds - please check https://www.w3schools.com/html/html_form_input_types.asp',
      table:{
        category:'Text'
      },
      control:{
        type:'object'
      }
    },
    privacyterms:{
      name:'Privacy Qoutes',
      description:'Privacy guidelines of the form',
      table:{
        category:'Text'
      },
      control:{
        type:'object'
      }
    },

  }
};

export const newsletterSignupFormModule = ({
  theme,
  backgroundColor,
  title,
  formInput,
  privacyterms
}) => (
  <div
    dangerouslySetInnerHTML={{ __html: newsLetterForm({
      ...newsLetterFormData,
      theme_styles:theme,
      background_color:backgroundColor,
      webform_block_label:title,
      forms:formInput,
      privacyfeilds:privacyterms
    }) }}
  />
);
newsletterSignupFormModule.args = {
  theme: newsLetterFormData.theme_styles,
  backgroundColor:newsLetterFormData.background_color,
  title:newsLetterFormData.webform_block_label,
  formInput:newsLetterFormData.forms,
  privacyterms:newsLetterFormData.privacyfeilds
}