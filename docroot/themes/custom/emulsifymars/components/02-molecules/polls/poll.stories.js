
import React from 'react';
import poll from './poll-vote.twig';
import pollSubmitted from './poll-results.twig';
import pollData from './poll.yml';
import defaultLink from './../../01-atoms/links/defaultLink/defaultLink.twig'

export default { title: 'Molecules/Polling' };

export const pollingInitialState = () => {
  pollData.vote_button = defaultLink({default_link_content: 'Submit'});
  return <div style={{padding: '2rem'}} dangerouslySetInnerHTML={{ __html: poll(pollData) }} />;
};

export const pollingVoteSubmitted = () => (
  <div style={{padding: '2rem'}} dangerouslySetInnerHTML={{ __html: pollSubmitted(pollData) }} />
);
