import React from 'react';

import articleTemplateTwig from './article-template.twig';
import articleTemplateData from './article-template.yml';
import articleHeaderImage from '../../02-molecules/article-header/article-header-image.twig';
import articleHeaderImageData from '../../02-molecules/article-header/article-header-image.yml';
import articleArticleWysiwyg from '../../02-molecules/article-media/wysiwyg/article-wysiwyg.twig';
import articleArticleWysiwygData from '../../02-molecules/article-media/wysiwyg/article-wysiwyg.yml';
import articleMedia from '../../02-molecules/article-media/full-width/full-width-media.twig';
import articleMediaData from '../../02-molecules/article-media/full-width/full-width-media.yml';
import articleInlineMedia from '../../02-molecules/article-media/inline/inline-media.twig';
import articleInlineMediaData from '../../02-molecules/article-media/inline/inline-media.yml';
import articleMediaCarousel from '../../02-molecules/media-carousel/media-carousel.twig';
import articleMediaCarouselData from '../../02-molecules/media-carousel/media-carousel.yml';
import articleFlexibleDriver from '../../02-molecules/flexible-driver/flexible-driver.twig';
import articleFlexibleDriverData from '../../02-molecules/flexible-driver/flexible-driver.yml';
import articleArticleList from '../../02-molecules/article-media/list/article-list.twig';
import articleArticleListData from '../../02-molecules/article-media/list/article-list.yml';
import articleiFrame from '../../01-atoms/iframe/iframe.twig';
import articleiFrameData from '../../01-atoms/iframe/iframe.yml';
import articleSocialFeed from '../../02-molecules/social-feed/social-feed.twig';
import articleSocialFeedData from '../../02-molecules/social-feed/social-feed.yml';
import articleRecommendation from '../../02-molecules/recommendations-module/recommendations-module.twig';
import articleRecommendationData from '../../02-molecules/recommendations-module/recommendations-module.yml';
import articleStoryHighlight from '../../02-molecules/story-highlight/story_highlight.twig';
import articleStoryHighlightData from '../../02-molecules/story-highlight/story_highlight.yml';
import articleFlexibleFramer from '../../02-molecules/flexible-framer/flexible-framer.twig';
import articleFlexibleFramerData from '../../02-molecules/flexible-framer/flexible-framer.yml';
import articlePolls from '../../02-molecules/polls/poll-vote.twig';
import articlePollsData from '../../02-molecules/polls/poll.yml';
import articleContact from '../../02-molecules/contact-module/contact-module.twig';
import articleContactData from '../../02-molecules/contact-module/contact-module.yml';
import articleFeedback from '../../02-molecules/feedback-module/feedback.twig';
import articleFeedbackData from '../../02-molecules/feedback-module/feedback.yml';


import { useEffect } from '@storybook/client-api';

// export default { title: 'Templates/Article Template'};

export const articleTemplate = () => {
    useEffect(() => Drupal.attachBehaviors(), []);
    const components = [
      articleHeaderImage(articleHeaderImageData),
      articleArticleWysiwyg(articleArticleWysiwygData),
      articleMedia(articleMediaData),
      articleInlineMedia(articleInlineMediaData),
      articleMediaCarousel(articleMediaCarouselData),
      articleFlexibleDriver(articleFlexibleDriverData),
      articleArticleList(articleArticleListData),
      articleiFrame(articleiFrameData),
      articleSocialFeed(articleSocialFeedData),
      articleRecommendation(articleRecommendationData),
      articleStoryHighlight(articleStoryHighlightData),
      articleFlexibleFramer(articleFlexibleFramerData),
      articlePolls(articlePollsData),
      articleContact(articleContactData),
      articleFeedback(articleFeedbackData)
    ];
    return <div dangerouslySetInnerHTML={{
        __html: articleTemplateTwig({
          'components': components
        })
      }}/>
}
