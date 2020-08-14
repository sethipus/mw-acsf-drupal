import React from 'react';
import backgroundVideo from './background-video.twig';
import backgroundVideoData from './background-video.yml';
export default { title: 'Atoms/Background Video' };

export const backgroundVideoExample = () => {
  return <div dangerouslySetInnerHTML={{ __html: backgroundVideo({ ...backgroundVideoData }) }} />;
};
