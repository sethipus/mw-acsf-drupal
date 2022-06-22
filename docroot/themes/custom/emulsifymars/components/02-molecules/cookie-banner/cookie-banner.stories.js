import React from 'react';
import cookieBanner from './cookie-banner.twig';
import cookieBannerYml from './cookie-banner.yml';

export default {
  title: 'Components/[GE 05]Cookie Banner',
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
    cookieMessage: {
      name: 'Cookie Message',
      description: 'Cookie description message',
      defaultValue: {
        summary: 'Lorem Ipsum...',
      },
      table: { category: 'Text' },
      control: { type: 'text' },
    },
    cookieSettingBtn: {
      name: 'Setting Button CTA',
      description: 'Cookie Setting Button CTA',
      defaultValue: {
        summary: 'Lorem',
      },
      table: { category: 'Text' },
      control: { type: 'text' },
    },
    acceptCookieBtn: {
      name: 'Accept Button CTA',
      description: 'Cookie Accept Button CTA',
      defaultValue: {
        summary: 'Lorem',
      },
      table: { category: 'Text' },
      control: { type: 'text' },
    },
  },
  parameters: {
    componentSubtitle:
      'The Cookie Banner pop-up will automatically appear when a visitor comes to any brand site. It lets users know that their data is being collected and will be used for certain purposes and allows them to give their consent for Mars to use the data.',
  },
};

export const CookieBannerLayout = ({
  cookieMessage,
  theme,
  cookieSettingBtn,
  acceptCookieBtn,
}) => {
  return (
    <div
      dangerouslySetInnerHTML={{
        __html: cookieBanner({
          ...cookieBannerYml,
          theme_styles:theme,
          accept_cookie: acceptCookieBtn,
          cookie_message: cookieMessage,
          cookie_settings: cookieSettingBtn,
        }),
      }}
    />
  );
};
CookieBannerLayout.args = {
  theme: cookieBannerYml.theme_styles,
  cookieMessage: cookieBannerYml.cookie_message,
  acceptCookieBtn: cookieBannerYml.accept_cookie,
  cookieSettingBtn: cookieBannerYml.cookie_settings,
};
