
import React from 'react';
import poll from './poll.twig';
import pollData from './poll.yml';

export default { title: 'Molecules/Polling' };

export const pollingExample = () => (
  <div style={{padding: '2rem'}} dangerouslySetInnerHTML={{ __html: poll(pollData) }} />
);
