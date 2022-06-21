import React from 'react';
import { useEffect } from '@storybook/client-api';

import '../../01-atoms/video/ambient-video/video';

import homepageHero from './standard/homepage-hero-standard.twig';
import homepageHeroData from './standard/homepage-hero-standard.yml';

import homepageHeroVideo from './video/homepage-hero-video.twig';
import homepageHeroVideoData from './video/homepage-hero-video.yml';

import homepageHero3UP from './3up/homepage-hero-3up.twig';
import homepageHero3UPData from './3up/homepage-hero-3up.yml';

import homepageHeroBasic from './basic/homepage-hero-basic.twig';
import homepageHeroBasicData from './basic/homepage-hero-basic.yml';
import './homepage-hero';

/**
 * Storybook Definition.
 */
export default {
  title: 'Components/[ML 01] Homepage Hero',
  parameters: {
    componentSubtitle: `HP will only live at the top of the homepage.
                     This high impact hero allows editors to promote
                     a single or combination of 3 products or content
                     within the hero. The Homepage hero include 5 variations:
                     standard (single piece of content or product)
                     Video (single video player)
                     Image+text
                     3-Up 
                     Looping Video.`,
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
    eyebrow: {
      name: 'Eyebrow',
      description: 'Eyebrow text for the homepage hero block.<b>CC: 15 Max</b>',
      defaultValue: {
        summary: 'Lorem..',
      },
      table: {
        category: 'Text',
      },
      control: {
        type: 'text',
      },
    },
    title: {
      name: 'Title',
      description: 'Title text for the homepage hero block.<b>CC: 55 Max</b>',
      defaultValue: {
        summary: 'Lorem..',
      },
      table: {
        category: 'Text',
      },
      control: {
        type: 'text',
      },
    },
    hero_images: {
      name: 'Background Media',
      description: 'Background media for the homepage block.',
      defaultValue: {
        summary:
          'For video - "https://lhcdn.mars.com/adaptivemedia/rendition/id_88cf808674c1c085869db727c492e1cba40b0dc8/name_88cf808674c1c085869db727c492e1cba40b0dc8.jpg"',
      },
      table: {
        category: 'Media',
      },
      control: {
        type: 'object',
      },
    },
    blocks: {
      name: 'Blocks(only applicable to block layout)',
      description: 'Edit block layout for 3up block homepage hero layout.',
      table: {
        category: 'Text',
      },
      control: {
        type: 'object',
      },
    },
  },
};

export const homepageStandardLayout = ({
  theme,
  eyebrow,
  title,
  hero_images,
}) => {
  useEffect(() => Drupal.attachBehaviors(), []);
  return (
    <div
      dangerouslySetInnerHTML={{
        __html: homepageHero({
          ...homepageHeroData,
          theme_styles: theme,
          eyebrow: eyebrow,
          title_label: title,
          hero_images,
        }),
      }}
    />
  );
};
homepageStandardLayout.args = {
  theme: homepageHeroData.theme_styles,
  eyebrow: homepageHeroData.eyebrow,
  title: homepageHeroData.title_label,
  hero_images: homepageHeroData.hero_images,
};

export const homepageHeroVideoBlock = ({
  theme,
  eyebrow,
  title,
  hero_images,
}) => {
  useEffect(() => Drupal.attachBehaviors(), []);
  return (
    <div
      dangerouslySetInnerHTML={{
        __html: homepageHeroVideo({
          ...homepageHeroVideoData,
          theme_styles: theme,
          eyebrow: eyebrow,
          title_label: title,
          video__src__url: hero_images,
        }),
      }}
    />
  );
};
homepageHeroVideoBlock.args = {
  theme: homepageHeroVideoData.theme_styles,
  eyebrow: homepageHeroVideoData.eyebrow,
  title: homepageHeroVideoData.title_label,
  hero_images: homepageHeroVideoData.video__src__url,
};

export const homepageHero3UPBlock = ({
  theme,
  eyebrow,
  title,
  hero_images,
  blocks,
}) => {
  return (
    <div
      dangerouslySetInnerHTML={{
        __html: homepageHero3UP({
          ...homepageHero3UPData,
          theme_styles: theme,
          eyebrow: eyebrow,
          title_label: title,
          hero_images: hero_images,
          blocks: blocks,
        }),
      }}
    />
  );
};
homepageHero3UPBlock.args = {
  theme: homepageHero3UPData.theme_styles,
  eyebrow: homepageHero3UPData.eyebrow,
  title: homepageHero3UPData.title_label,
  hero_images: homepageHero3UPData.hero_images,
  blocks: homepageHero3UPData.blocks,
};

export const homepageHeroBasicBlock = ({
  theme,
  eyebrow,
  title,
  hero_images,
}) => {
  return (
    <div
      dangerouslySetInnerHTML={{
        __html: homepageHeroBasic({
          ...homepageHeroBasicData,
          theme_styles: theme,
          homepage_hero_basic_content: eyebrow,
          hero_images: hero_images,
          title,
        }),
      }}
    />
  );
};
homepageHeroBasicBlock.args = {
  theme: homepageHeroBasicData.theme_styles,
  eyebrow: homepageHeroBasicData.homepage_hero_basic_content,
  hero_images: homepageHeroBasicData.hero_images,
  title:'Not applicable to this layout!',
};
