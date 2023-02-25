import React from "react";
import styled from "styled-components";
import { Editable } from "./Editable";

const CardDiv = styled.div`
  padding: 20px;
  box-shadow: 2px 2px 4px rgba(0, 0, 0, 0.2);
  border-radius: 4px;
  max-width: 20em;
  border: 1px solid #eef;
  margin: 2em auto;
  background-color: ${(props) => (props.reverse ? "#ffd9e0" : "white")};
  position: relative;
  min-height: 6em;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  & .corner {
    position: absolute;
    opacity: 0.7;
    right: 10px;
    top: 10px;
    font-size: 90%;
  }
  & li {
    font-size: 10pt;
    display: inline-block;
  }
  & li + li {
    margin-left: 0.5em;
  }
`;

const urlTitle = (url) => {
  return new URL(url).hostname
    .split(".")
    .filter((x) => x != "www" && x != "com" && x != "org")
    .join(".");
};

export const Card = ({ card, show, onShow, onChange }) => {
  return (
    <CardDiv reverse={card.reverse} onClick={onShow}>
      <div className="corner">{card.times}</div>
      <p>{card.q}</p>
      {show && (
        <>
          <Editable content={card.a} onChange={onChange} />
          <ul>
            {card.urls.map((url) => (
              <li key={url}>
                <a target="_blank" rel="noreferrer" href={url}>
                  {urlTitle(url)}
                </a>
              </li>
            ))}
          </ul>
        </>
      )}
    </CardDiv>
  );
};
