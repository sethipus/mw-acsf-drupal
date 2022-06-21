import React from 'react';
import parentPageHeader from './parent-page-header.twig';
import parentPageHeaderData from './parent-page-header.yml';
import { useEffect } from '@storybook/client-api';

export default {
  title: 'Components/[ML 10] Parent Page Header',
  parameters: {
    componentSubtitle: `A large, flexible header that leads parent
      category pages and introduces the name of the
      page with a description to provide additional
      context about the page. It can be added to the
      following pages - Landing page, About page, Product
      hub, Content hub, Contact & help, Campaign page, where
      to buy and newsletter.`,
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
      name: 'Eyebrow text',
      description: 'Eyebrow of the page.<b> Maximum character limit is 30.</b>',
      defaultValue: { summary: 'LOREM' },
      table: {
        category: 'Text',
      },
      control: { type: 'text' },
    },
    Title: {
      name: 'Title',
      description: 'Title of the page.<b> Maximum character limit is 55.</b>',
      defaultValue: { summary: 'Title' },
      table: {
        category: 'Text',
      },
      control: { type: 'text' },
    },

    Description: {
      name: 'Description text',
      description:
        'Description of the page. <b>Maximum character limit is 255.</b>',
      defaultValue: { summary: 'lorem ipsum..' },
      table: {
        category: 'Text',
      },
      control: { type: 'text' },
    },

    BackgroundTheme: {
      name: 'Background Theme',
      description: 'Background - Color/Image/Video',
      table: {
        category: 'Theme',
      },
      control: { type: 'radio', options: ['color', 'image', 'video'] },
    },
    parent_page_media_entities: {
      name: 'Background Image/Video',
      description:
        'Background Image and Video URL. Dimensions <ul><b> <li> Mobile : 375px X 435px </li>  <li> Tablet : 768px X 601px </li> <li>Desktop : 2880px X 1196px </li></b></ul>',
      defaultValue: {
        summary:
          'VIDEO - https://lhcdn.mars.com/adaptivemedia/rendition/id_f76bfd3c55ff05adc19f33a69e3bc665045e6a4f/name_f76bfd3c55ff05adc19f33a69e3bc665045e6a4f.jpg ,IMAGE - http://dove.mars.acsitefactory.com/sites/g/files/fnmzdf186/files/2020-12/Dove%20Home%20Banner%2021-9.PNG',
      },
      table: {
        category: 'Theme',
      },
      control: { type: 'object' },
    },
  },
};

export const ParentPageHeaderLayout = ({
  theme,
  Eyebrow,
  Title,
  Description,
  BackgroundTheme,
  parent_page_media_entities,
}) => (
  <div
    dangerouslySetInnerHTML={{
      __html: parentPageHeader({
        ...parentPageHeaderData,
        theme_styles: theme,
        pp_header_eyebrow_text: Eyebrow,
        pp_header_heading: Title,
        pp_header_paragraph_content: Description,
        parent_page_media_type: BackgroundTheme,
        parent_page_media_entities:parent_page_media_entities,
      }),
    }}
  />
);
ParentPageHeaderLayout.args = {
  theme: parentPageHeaderData.theme_styles,
  Eyebrow: parentPageHeaderData.pp_header_eyebrow_text,
  Title: parentPageHeaderData.pp_header_heading,
  Description: parentPageHeaderData.pp_header_paragraph_content,
  BackgroundTheme: parentPageHeaderData.parent_page_media_type,
  parent_page_media_entities: parentPageHeaderData.parent_page_media_entities,
};