import React from 'react';
import video from './video.twig';
import backgroundVideo from './background-video/background-video.twig';
import backgroundVideoData from './background-video/background-video.yml';
import videoData from './video.yml';
import videoFullData from './video-full.yml';

/**
 * Storybook Definition.
 */
export default { title: 'Atoms/Videos' };

export const wide = () => (
  <div dangerouslySetInnerHTML={{ __html: video(videoData) }} />
);
export const full = () => (
  <div dangerouslySetInnerHTML={{ __html: video(videoFullData) }} />
);
export const background = () => {
  return <div dangerouslySetInnerHTML={{ __html: backgroundVideo(backgroundVideoData) }} />;
};
