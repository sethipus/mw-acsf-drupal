
import React from 'react';
import poll from './poll-vote.twig';
import pollSubmitted from './poll-results.twig';
import pollData from './poll.yml';

export default { title: 'Molecules/Polling' };

export const pollingInitialState = () => (
  <div style={{padding: '2rem'}} dangerouslySetInnerHTML={{ __html: poll(pollData) }} />
);

export const pollingVoteSubmitted = () => (
  <div style={{padding: '2rem'}} dangerouslySetInnerHTML={{ __html: pollSubmitted(pollData) }} />
);
