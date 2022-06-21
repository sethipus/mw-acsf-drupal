import React from 'react';
import { useEffect } from '@storybook/client-api';

import ambientVideo from './ambient-video/video.twig';
import ambientVideoData from './ambient-video/video.yml';
import fullscreenVideo from './fullscreen-video/video.twig';
import fullscreenVideoData from './fullscreen-video/video.yml';
import inlineVideo from './inline-video/video.twig';
import inlineVideoData from './inline-video/video.yml';
import overlayVideo from './overlay-video/video.twig';
import overlayVideoData from './overlay-video/video.yml';

import './ambient-video/video';
import './fullscreen-video/video';
import './inline-video/video';
import './overlay-video/video';

/**
 * Storybook Definition.
 */
// export default { title: 'Atoms/Video' };

export const ambientVideoComponent = () => {
  useEffect(() => Drupal.attachBehaviors(), []);
  return <div dangerouslySetInnerHTML={{ __html: ambientVideo({ ...ambientVideoData }) }} />;
};

export const inlineVideoComponent = () => {
  useEffect(() => Drupal.attachBehaviors(), []);
  return <div dangerouslySetInnerHTML={{ __html: inlineVideo({ ...inlineVideoData }) }} />;
};

export const overlayVideoComponent = () => {
  useEffect(() => Drupal.attachBehaviors(), []);
  return <div dangerouslySetInnerHTML={{ __html: overlayVideo({ ...overlayVideoData }) }} />;
};

export const fullscreenVideoComponent = () => {
  useEffect(() => Drupal.attachBehaviors(), []);
  return <div dangerouslySetInnerHTML={{ __html: fullscreenVideo({ ...fullscreenVideoData }) }} />;
};
