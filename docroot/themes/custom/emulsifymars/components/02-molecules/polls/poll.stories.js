
import React from 'react';
import poll from './poll-vote.twig';
import pollData from './poll.yml';
import defaultLink from './../../01-atoms/links/defaultLink/defaultLink.twig'

export default { 
  title: 'Components/[ML 14] Poll',
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
    Image: {
      name: 'Image Asset',
      description: 'Changing the image for the layout',
      table: {
        category: 'Image',
      },
      control: { type: 'object' },
    },
    Heading: {
      name: 'Heading text',
      description: 'Changing the Heading for the layout.<b> Maximum character limti is 55.</b>',
      defaultValue: { summary: 'Lorem..' },
      table: {
        category: 'Text',
      },
      control: { type: 'text' },
    },
    Content: {
      name: 'Content text',
      description: 'Changing the Content for the layout.Dimensions <ul><b> <li> Mobile : 375px X 435px </li>  <li> Tablet : 768px X 601px </li> <li>Desktop : 2880px X 1196px </li></b></ul> <b>Maximum character limti is 255.</b>',
      defaultValue: { summary: 'Lorem Ipsum..' },
      table: {
        category: 'Text',
      },
      control: { type: 'text' },
    },
    Options: {
      name: 'Poll options',
      description: 'Change the poll options in the layout',
      defaultValue: { summary: '3 choices' },
      table: {
        category: 'Choices',
      },
      control: { type: 'radio', options: ['3', '4', '5'] },
    },
  },
  parameters:{
    componentSubtitle:'Polls will be pulled from a 3rd party vendor, so all functionality will be replicated from the integrated 3rd party. It can be added to the following pages - Homepage, Landing page, About page, Product Hub, Content hub, Product detail, Recipe Detail, Article and Campaign page.'
  }
};

export const PollingVoteLayout = ({  theme,Image, Heading, Content, Options }) => {
  pollData.vote_button = defaultLink({default_link_content: 'Submit'});
  return (
    <div
      style={{ padding: '2rem' }}
      dangerouslySetInnerHTML={{
        __html: poll({
          ...pollData,
          theme_styles: theme,
          polling_png_asset: Image,
          polling_heading: Heading,
          polling_paragraph_content: Content,
          storybook_poll_options:Options,
        }),
      }}
    />
  );
};

PollingVoteLayout.args = {
  theme: pollData.theme_styles,
  Image: pollData.polling_png_asset,
  Heading: pollData.polling_heading,
  Content: pollData.polling_paragraph_content,
  Options: pollData.storybook_poll_options
};