import React from "react";
import { Link } from "react-router-dom";
import styled from "styled-components";
import { ROOT_PATH } from "../api";

const CardDiv = styled.div`
  padding: 40px;
  box-shadow: 2px 2px 4px rgba(0, 0, 0, 0.2);
  border-radius: 4px;
  max-width: 20em;
  border: 1px solid #eef;
  margin: 2em auto;
  background-color: ${(props) => (props.reverse ? "#ffd9e0" : "white")};
  position: relative;
  min-height: 6em;
  display: flex;
  align-items: center;
  justify-content: center;
  & .corner {
    position: absolute;
    opacity: 0.7;
    right: 10px;
    top: 10px;
    font-size: 90%;
  }
  & .left-corner {
    position: absolute;
    opacity: 0.7;
    left: 10px;
    top: 10px;
    font-size: 90%;
  }
`;

const urlTitle = (url) => {
  return new URL(url).hostname
    .split(".")
    .filter((x) => x != "www" && x != "com" && x != "org")
    .join(".");
};

export const Card = ({ card, show, onShow }) => {
  return (
    <CardDiv reverse={card.reverse} onClick={onShow}>
      <div className="corner">{card.times}</div>
      {show && (
        <div className="left-corner">
          <Link to={`${ROOT_PATH}entries/${card.id}`}>Edit</Link>
        </div>
      )}
      <div>
        {card.q}
        {card.hint && ` (${card.hint})`}
        {show && (
          <>
            <p>{card.a}</p>
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
      </div>
    </CardDiv>
  );
};
