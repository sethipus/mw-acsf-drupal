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
export default { title: 'Molecules/Homepage Hero' };

export const homepageHeroBlock = () => {
  useEffect(() => Drupal.attachBehaviors(), []);
  return <div dangerouslySetInnerHTML={{ __html: homepageHero(homepageHeroData) }} />;
};

export const homepageHeroVideoBlock = () => {
  useEffect(() => Drupal.attachBehaviors(), []);
  return <div dangerouslySetInnerHTML={{ __html: homepageHeroVideo(homepageHeroVideoData) }} />;
};

export const homepageHero3UPBlock = () => {
  return <div dangerouslySetInnerHTML={{ __html: homepageHero3UP(homepageHero3UPData) }} />;
};

export const homepageHeroBasicBlock = () => {
  return <div dangerouslySetInnerHTML={{ __html: homepageHeroBasic(homepageHeroBasicData) }} />;
};
