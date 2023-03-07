export const urlTitle = (url) => {
  return new URL(url).hostname
    .split(".")
    .filter((x) => x != "www" && x != "com" && x != "org")
    .join(".");
};
