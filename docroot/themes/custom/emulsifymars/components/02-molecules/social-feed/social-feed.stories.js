import React from 'react';
import socialFeed from './social-feed.twig';
import socialFeedData from './social-feed.yml';
import { useEffect } from '@storybook/client-api';
import './social-feed';

/**
 * Storybook Definition.
 */
export default {
  title: 'Components/[ML 18] Social feed',
  parameters: {
    componentSubtitle: `This module will dynamically pull the 
                    12 most recent content pieces in from Instagram and 
                    must support the following: 
                    Able to pull from a specific tag
                    Able to pull from your full account.`,
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
    title: {
      name: 'Title',
      description: 'Title text for the social feed.<b> maximum CC : 55</b>',
      table: {
        category: 'Text',
      },
      control: {
        type: 'text',
      },
    },
    items: {
      name: 'Items',
      description: 'Item content for the social feed.',
      table: {
        category: 'Text',
      },
      control: {
        type: 'object',
      },
    },
  },
};

export const socialFeedModuleLayout = ({ theme, title, items }) => {
  useEffect(() => Drupal.attachBehaviors(), []);
  // componentDidUpdate(() => {
  //   console.log("Page reloaded");
  //   Drupal.attachBehaviors(),[]
  // })  
  return (
      <div
        dangerouslySetInnerHTML={{
          __html:
            socialFeed({
              ...socialFeedData,
              theme_styles: theme,
              storybook_social_feed_title: title,
              storybook_social_feed_items: items,
            }),
        }}
      />
  );
};
socialFeedModuleLayout.args = {
  theme: socialFeedData.theme_styles,
  title: socialFeedData.storybook_social_feed_title,
  items: socialFeedData.storybook_social_feed_items,
};
