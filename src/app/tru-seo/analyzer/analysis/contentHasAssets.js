import { __ } from '@wordpress/i18n'

const td = import.meta.env.VITE_TEXTDOMAIN

function getImages (content) {
	const images = [].concat(
		match(content, '<img(?:[^>]+)?>'),
		match(content, '\\[gallery( [^\\]]+?)?\\]')
	)

	return images.length
}

function getVideos (content) {
	const videos = [].concat(
		match(content, '<iframe(?:[^>]+)?>'),
		match(content, '\\[video( [^\\]]+?)?\\]'),
		match(content, '<video(?:[^>]+)?>'),
		match(content, /(http:\/\/|https:\/\/|)(player.|www.)?(vimeo\.com|youtu(be\.com|\.be|be\.googleapis\.com))\/(video\/|embed\/|watch\?v=|v\/)?([A-Za-z0-9._%-]*)(\&\S+)?/) // eslint-disable-line
	)

	return videos.length
}

function match (text, regexString) {
	const regex = new RegExp(regexString, 'ig')
	const matches = text.match(regex)

	return null === matches ? [] : matches
}

function contentHasAssets (text) {
	if (!text) {
		return {
			error       : 1,
			title       : __('No content yet', td),
			description : __('Please add some content first.', td),
			score       : 1,
			maxScore    : 5
		}
	}

	const images = getImages(text)
	const videos = getVideos(text)

	if (0 < images || 0 < videos) {
		return {
			error       : 0,
			title       : __('Images/videos in content', td),
			description : __('Your content contains images and/or video(s).', td),
			score       : 5,
			maxScore    : 5
		}
	}
	return {
		error       : 1,
		title       : __('Images/videos in content', td),
		description : __('You are not using rich media like images or videos.', td),
		score       : 1,
		maxScore    : 5
	}
}

export default contentHasAssets