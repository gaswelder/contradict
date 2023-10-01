import React from "react";
import styled from "styled-components";
import { urlTitle } from "./lib/url-title";

const Ul = styled.ul`
  padding: 0;
  & li {
    font-size: 10pt;
    display: inline-block;
  }
  & li + li {
    margin-left: 0.5em;
  }
`;

export const CardSources = ({ card }) => {
  return (
    <Ul>
      {card.urls.map((url) => (
        <li key={url}>
          <a target="_blank" rel="noreferrer" href={url}>
            {urlTitle(url)}
          </a>
        </li>
      ))}
    </Ul>
  );
};
