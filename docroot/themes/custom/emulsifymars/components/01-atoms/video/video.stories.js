import React from 'react';
import { useEffect } from '@storybook/client-api';

import ambientVideo from './ambient-video/video.twig';
import ambientVideoData from './ambient-video/video.yml';
import fullscreenVideo from './fullscreen-video/video.twig';
import fullscreenVideoData from './fullscreen-video/video.yml';
import inlineVideo from './inline-video/video.twig';
import inlineVideoData from './inline-video/video.yml';
import video from './video-frame/video.twig';
import videoData from './video-frame/video.yml';
import videoFullData from './video-frame/video-full.yml';
import backgroundVideo from './background-video/background-video.twig';
import backgroundVideoData from './background-video/background-video.yml';

import './ambient-video/video';
import './fullscreen-video/video';
import './inline-video/video';

/**
 * Storybook Definition.
 */
export default { title: 'Atoms/Video' };

export const ambientVideoComponent = () => {
  useEffect(() => Drupal.attachBehaviors(), []);
  return <div dangerouslySetInnerHTML={{ __html: ambientVideo({ ...ambientVideoData }) }} />;
};

export const backgroundVideoExample = () => {
  return <div dangerouslySetInnerHTML={{ __html: backgroundVideo({ ...backgroundVideoData }) }} />;
};

export const inlineVideoComponent = () => {
  useEffect(() => Drupal.attachBehaviors(), []);
  return <div dangerouslySetInnerHTML={{ __html: inlineVideo({ ...inlineVideoData }) }} />;
};

export const fullscreenVideoComponent = () => {
  useEffect(() => Drupal.attachBehaviors(), []);
  return <div dangerouslySetInnerHTML={{ __html: fullscreenVideo({ ...fullscreenVideoData }) }} />;
};

export const wide = () => (
  <div dangerouslySetInnerHTML={{ __html: video(videoData) }} />
);

export const full = () => (
  <div dangerouslySetInnerHTML={{ __html: video(videoFullData) }} />
);
