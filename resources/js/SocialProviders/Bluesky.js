import CountTextCharacters from "../Util/CountTextCharacters";

const getPostLength = (content) => {
    return CountTextCharacters.getLength(content, {
        urlWeight: null,
        emojiWeight: 1,
    });
};

export default {
    getPostLength
}
