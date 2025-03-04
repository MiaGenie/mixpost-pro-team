import CountTextCharacters from "../Util/CountTextCharacters";

const getPostLength = (content) => {
    // Remove "https://" and "http://" from URLs
    const sanitizedContent = content.replace(/https?:\/\//g, '');

    return CountTextCharacters.getLength(sanitizedContent, {
        urlWeight: null,
        emojiWeight: 1,
    });
};

export default {
    getPostLength
}
